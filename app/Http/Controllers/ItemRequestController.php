<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\ItemRequest;
use App\Models\ItemRequestItem;
use App\Models\ItemRequestApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemRequestController extends Controller
{
    public function index()
    {
        $itemRequests = ItemRequest::with(['requester', 'department', 'bast'])
            ->latest()
            ->paginate(20);

        return view('item_requests.index', compact('itemRequests'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        $assets = Asset::with(['model'])
            ->whereNull('deleted_at')
            ->where('archived', 0)
            ->orderBy('asset_tag')
            ->get();

        $consumables = Consumable::orderBy('name')->get();

        return view('item_requests.create', compact('departments', 'assets', 'consumables'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_type' => 'required|in:asset,consumable',
            'procurement_type' => 'nullable|in:cash,po',
            'department_id' => 'nullable|exists:departments,id',
            'purpose' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.spec' => 'nullable|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.item_type' => 'required|in:asset,consumable',
            'items.*.fulfillment_type' => 'required|in:existing_stock,procurement',
            'items.*.asset_id' => 'nullable|exists:assets,id',
            'items.*.consumable_id' => 'nullable|exists:consumables,id',
        ]);

        foreach ($request->items as $index => $item) {
            // Validasi kecocokan item_type dengan request_type
            if (($item['item_type'] ?? null) !== $request->request_type) {
                return back()->withInput()->withErrors([
                    "items.$index.item_type" => 'Tipe item harus sama dengan tipe request utama.',
                ]);
            }

            // Jika ambil dari stok, wajib pilih existing item sesuai tipenya
            if (($item['fulfillment_type'] ?? null) === 'existing_stock') {
                if (($item['item_type'] ?? null) === 'asset' && empty($item['asset_id'])) {
                    return back()->withInput()->withErrors([
                        "items.$index.asset_id" => 'Karena sumber barang dipilih "Ambil dari Stok Existing", Anda wajib memilih Asset Existing.',
                    ]);
                }

                if (($item['item_type'] ?? null) === 'consumable' && empty($item['consumable_id'])) {
                    return back()->withInput()->withErrors([
                        "items.$index.consumable_id" => 'Karena sumber barang dipilih "Ambil dari Stok Existing", Anda wajib memilih Consumable Existing.',
                    ]);
                }
            }

            // Jika procurement, pastikan referensi existing dikosongkan
            if (($item['fulfillment_type'] ?? null) === 'procurement') {
                if (!empty($item['asset_id']) || !empty($item['consumable_id'])) {
                    return back()->withInput()->withErrors([
                        "items.$index.fulfillment_type" => 'Jika memilih "Pengadaan Baru", kolom Asset Existing / Consumable Existing harus dikosongkan.',
                    ]);
                }
            }
        }

        $itemRequest = DB::transaction(function () use ($request) {
            $nextId = (ItemRequest::max('id') ?? 0) + 1;
            $requestNumber = 'REQ/' . date('Y') . '/' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

            $estimatedTotal = collect($request->items)->sum(function ($item) {
                $qty = (int) ($item['qty'] ?? 0);
                $price = (float) ($item['estimated_price'] ?? 0);
                return $qty * $price;
            });

            $itemRequest = ItemRequest::create([
                'request_number' => $requestNumber,
                'request_type' => $request->request_type,
                'procurement_type' => $request->procurement_type,
                'requester_id' => Auth::id(),
                'department_id' => $request->department_id,
                'purpose' => $request->purpose,
                'estimated_total' => $estimatedTotal,
                'status' => 'submitted',
            ]);
            ItemRequestApproval::create([
                'item_request_id' => $itemRequest->id,
                'approval_order' => 1,
                'assigned_approver_id' => $this->resolveStepOneApproverId(),
                'assigned_role' => 'Manager Approval',
                'status' => 'waiting',
            ]);

            ItemRequestApproval::create([
                'item_request_id' => $itemRequest->id,
                'approval_order' => 2,
                'assigned_approver_id' => $this->resolveStepTwoApproverId(),
                'assigned_role' => 'Procurement Approval',
                'status' => 'waiting',
            ]);

            foreach ($request->items as $item) {
                ItemRequestItem::create([
                    'item_request_id' => $itemRequest->id,
                    'item_name' => $item['item_name'],
                    'spec' => $item['spec'] ?? null,
                    'qty' => $item['qty'],
                    'estimated_price' => $item['estimated_price'] ?? null,
                    'item_type' => $item['item_type'],
                    'fulfillment_type' => $item['fulfillment_type'],
                    'asset_id' => $item['asset_id'] ?? null,
                    'consumable_id' => $item['consumable_id'] ?? null,
                    'is_registered' => false,
                    'is_fulfilled' => false,
                ]);
            }

            return $itemRequest;
        });

        return redirect()
            ->route('item-requests.show', $itemRequest->id)
            ->with('success', 'Pengajuan berhasil dibuat.');
    }

    public function show(ItemRequest $item_request)
    {
        $item_request->load([
            'items.asset',
            'items.consumable',
            'requester',
            'department',
            'bast',
            'approvals.approver',
            'approvals.assignedApprover',
        ]);

        $assets = Asset::with(['model'])
            ->whereNull('deleted_at')
            ->where('archived', 0)
            ->orderBy('asset_tag')
            ->get();

        $consumables = Consumable::orderBy('name')->get();

        return view('item_requests.show', [
            'itemRequest' => $item_request,
            'assets' => $assets,
            'consumables' => $consumables,
        ]);
    }

    public function approve(ItemRequest $item_request)
    {
        if (!in_array($item_request->status, ['draft', 'submitted'])) {
            return redirect()->back()->with('error', 'Status pengajuan tidak bisa di-approve.');
        }

        $item_request->load('items', 'approvals');

        $currentApproval = $item_request->approvals()
            ->where('status', 'waiting')
            ->orderBy('approval_order')
            ->first();

        if (!$currentApproval) {
            return redirect()->back()->with('error', 'Tidak ada approval yang menunggu.');
        }

        if (!empty($currentApproval->assigned_approver_id) && (int)$currentApproval->assigned_approver_id !== (int)Auth::id()) {
            return redirect()->back()->with('error', 'Anda bukan approver untuk step ini.');
        }

        $currentApproval->update([
            'approver_id' => Auth::id(),
            'approver_name' => Auth::user()->display_name ?? Auth::user()->first_name ?? 'Unknown',
            'approver_role' => Auth::user()->jobtitle ?? ($currentApproval->assigned_role ?? 'Approver'),
            'status' => 'approved',
            'remarks' => 'Approved from request detail page',
            'acted_at' => now(),
        ]);

        $remainingWaiting = $item_request->approvals()
            ->where('status', 'waiting')
            ->count();

        if ($remainingWaiting > 0) {
            return redirect()->back()->with(
                'success',
                'Approval step ' . $currentApproval->approval_order . ' berhasil. Menunggu approval step berikutnya.'
            );
        }

        $hasProcurement = $item_request->items->contains(function ($item) {
            return $item->fulfillment_type === 'procurement';
        });

        $item_request->update([
            'status' => $hasProcurement ? 'procurement_process' : 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with(
            'success',
            $hasProcurement
                ? 'Semua approval selesai. Pengajuan masuk ke proses procurement.'
                : 'Semua approval selesai. Pengajuan berhasil di-approve.'
        );
    }

    public function reject(Request $request, ItemRequest $item_request)
    {
        if (!in_array($item_request->status, ['draft', 'submitted', 'approved', 'procurement_process'])) {
            return redirect()->back()->with('error', 'Status pengajuan tidak bisa di-reject.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string'
        ]);

        $currentApproval = $item_request->approvals()
            ->where('status', 'waiting')
            ->orderBy('approval_order')
            ->first();

        if ($currentApproval) {
            if (!empty($currentApproval->assigned_approver_id) && (int)$currentApproval->assigned_approver_id !== (int)Auth::id()) {
                return redirect()->back()->with('error', 'Anda bukan approver untuk step ini.');
            }

            $currentApproval->update([
                'approver_id' => Auth::id(),
                'approver_name' => Auth::user()->display_name ?? Auth::user()->first_name ?? 'Unknown',
                'approver_role' => Auth::user()->jobtitle ?? ($currentApproval->assigned_role ?? 'Approver'),
                'status' => 'rejected',
                'remarks' => $request->rejection_reason,
                'acted_at' => now(),
            ]);
        }

        $item_request->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    public function markReadyForHandover(ItemRequest $item_request)
    {
        if (!in_array($item_request->status, ['approved', 'delivered'])) {
            return redirect()->back()->with('error', 'Pengajuan harus berstatus approved atau delivered terlebih dahulu.');
        }

        $item_request->load('items');

        if ($item_request->status === 'delivered') {
            $unregisteredProcurementItems = $item_request->items->filter(function ($item) {
                return $item->fulfillment_type === 'procurement' && !$item->is_registered;
            });

            if ($unregisteredProcurementItems->count() > 0) {
                return redirect()->back()->with('error', 'Masih ada item procurement yang belum dihubungkan ke item terdaftar di Snipe-IT.');
            }
        }

        $item_request->update([
            'status' => 'ready_for_handover',
        ]);

        return redirect()->back()->with('success', 'Status pengajuan diubah menjadi siap serah terima.');
    }

    public function markDelivered(ItemRequest $item_request)
    {
        if ($item_request->status !== 'procurement_process') {
            return redirect()->back()->with('error', 'Status pengajuan harus procurement_process.');
        }

        $item_request->update([
            'status' => 'delivered',
        ]);

        return redirect()->back()->with('success', 'Status pengajuan diubah menjadi delivered.');
    }

    public function close(ItemRequest $item_request)
    {
        if ($item_request->status !== 'handed_over') {
            return redirect()->back()->with('error', 'Pengajuan hanya bisa ditutup jika sudah handed_over.');
        }

        $item_request->update([
            'status' => 'closed',
        ]);

        return redirect()->back()->with('success', 'Pengajuan berhasil ditutup.');
    }

    public function linkRegisteredItem(Request $request, ItemRequest $item_request, ItemRequestItem $item)
    {
        if ((int) $item->item_request_id !== (int) $item_request->id) {
            return redirect()->back()->with('error', 'Item tidak sesuai dengan request.');
        }

        if ($item_request->status !== 'delivered') {
            return redirect()->back()->with('error', 'Item hanya bisa di-link saat request berstatus delivered.');
        }

        if ($item->fulfillment_type !== 'procurement') {
            return redirect()->back()->with('error', 'Hanya item procurement yang perlu di-link ke item terdaftar.');
        }

        $request->validate([
            'asset_id' => 'nullable|exists:assets,id',
            'consumable_id' => 'nullable|exists:consumables,id',
        ]);

        if ($item->item_type === 'asset') {
            if (!$request->filled('asset_id')) {
                return redirect()->back()->with('error', 'Asset wajib dipilih untuk item asset.');
            }

            $item->update([
                'asset_id' => $request->asset_id,
                'consumable_id' => null,
                'is_registered' => true,
            ]);
        }

        if ($item->item_type === 'consumable') {
            if (!$request->filled('consumable_id')) {
                return redirect()->back()->with('error', 'Consumable wajib dipilih untuk item consumable.');
            }

            $item->update([
                'consumable_id' => $request->consumable_id,
                'asset_id' => null,
                'is_registered' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Item procurement berhasil dihubungkan ke data Snipe-IT.');
    }

    private function resolveStepOneApproverId(): ?int
    {
        // sementara: ambil super admin / admin pertama
        $user = \App\Models\User::where('permissions', 'like', '%"superuser":"1"%')->first();

        return $user ? $user->id : null;
    }

    private function resolveStepTwoApproverId(): ?int
    {
        // sementara: fallback ke super admin pertama juga
        $user = \App\Models\User::where('permissions', 'like', '%"superuser":"1"%')->first();

        return $user ? $user->id : null;
    }
}
