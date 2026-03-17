<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\ItemRequest;
use App\Models\ItemRequestItem;
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
            'items.*.asset_id' => 'nullable|exists:assets,id',
            'items.*.consumable_id' => 'nullable|exists:consumables,id',
        ]);

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

            foreach ($request->items as $item) {
                ItemRequestItem::create([
                    'item_request_id' => $itemRequest->id,
                    'item_name' => $item['item_name'],
                    'spec' => $item['spec'] ?? null,
                    'qty' => $item['qty'],
                    'estimated_price' => $item['estimated_price'] ?? null,
                    'item_type' => $item['item_type'],
                    'asset_id' => $item['asset_id'] ?? null,
                    'consumable_id' => $item['consumable_id'] ?? null,
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
            'bast'
        ]);

        return view('item_requests.show', [
            'itemRequest' => $item_request
        ]);
    }

    public function approve(ItemRequest $item_request)
    {
        if (! in_array($item_request->status, ['draft', 'submitted'])) {
            return redirect()->back()->with('error', 'Status pengajuan tidak bisa di-approve.');
        }

        $item_request->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan berhasil di-approve.');
    }

    public function reject(Request $request, ItemRequest $item_request)
    {
        if (! in_array($item_request->status, ['draft', 'submitted'])) {
            return redirect()->back()->with('error', 'Status pengajuan tidak bisa di-reject.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string'
        ]);

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
        if ($item_request->status !== 'approved') {
            return redirect()->back()->with('error', 'Pengajuan harus approved terlebih dahulu.');
        }

        $item_request->update([
            'status' => 'ready_for_handover',
        ]);

        return redirect()->back()->with('success', 'Status pengajuan diubah menjadi siap serah terima.');
    }
}