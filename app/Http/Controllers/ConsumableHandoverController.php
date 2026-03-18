<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\ConsumableHandover;
use App\Models\ConsumableHandoverItem;
use App\Models\Department;
use App\Models\ItemRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConsumableHandoverController extends Controller
{
    public function index()
    {
        $handovers = ConsumableHandover::withCount('items')
            ->latest()
            ->paginate(20);

        return view('consumable_handovers.index', compact('handovers'));
    }

    public function create()
    {
        $consumables = Consumable::orderBy('name')->get();
        $users = User::with('department')->orderBy('display_name')->get();
        $departments = Department::orderBy('name')->get();

        return view('consumable_handovers.create', compact('consumables', 'users', 'departments'));
    }

    public function createFromRequest(ItemRequest $item_request)
    {
        $item_request->load(['items.consumable', 'requester', 'department']);

        if ($item_request->request_type !== 'consumable') {
            return redirect()->back()->with('error', 'Hanya request consumable yang bisa dibuatkan serah terima consumable.');
        }

        if ($item_request->status !== 'ready_for_handover') {
            return redirect()->back()->with('error', 'Request harus berstatus ready_for_handover.');
        }

        $consumables = Consumable::orderBy('name')->get();
        $users = User::with('department')->orderBy('display_name')->get();
        $departments = Department::orderBy('name')->get();

        return view('consumable_handovers.create', compact('consumables', 'users', 'departments', 'item_request'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_request_id' => 'nullable|exists:item_requests,id',
            'handover_date' => 'required|date',
            'handover_by' => 'required|string|max:255',
            'received_by' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.consumable_id' => 'nullable|exists:consumables,id',
            'items.*.remarks' => 'nullable|string',
            'action' => 'required|in:draft,final',
        ]);

        $handover = DB::transaction(function () use ($request) {
            $status = $request->action === 'final' ? 'final' : 'draft';
            $documentNumber = null;
            $finalizedAt = null;

            if ($status === 'final') {
                $documentNumber = $this->generateDocumentNumber();
                $finalizedAt = now();
            }

            $handover = ConsumableHandover::create([
                'document_number' => $documentNumber,
                'handover_date' => $request->handover_date,
                'item_request_id' => $request->item_request_id,
                'handover_by' => $request->handover_by,
                'received_by' => $request->received_by,
                'department' => $request->department,
                'notes' => $request->notes,
                'status' => $status,
                'finalized_at' => $finalizedAt,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                ConsumableHandoverItem::create([
                    'consumable_handover_id' => $handover->id,
                    'consumable_id' => $item['consumable_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'qty' => $item['qty'],
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            if ($status === 'final' && $request->filled('item_request_id')) {
                $itemRequest = ItemRequest::find($request->item_request_id);

                if ($itemRequest) {
                    $itemRequest->update([
                        'status' => 'handed_over',
                    ]);
                }
            }

            return $handover;
        });

        return redirect()
            ->route('consumable-handovers.show', $handover->id)
            ->with('success', $request->action === 'final'
                ? 'Serah terima consumable berhasil difinalisasi.'
                : 'Draft serah terima consumable berhasil disimpan.');
    }

    public function show(ConsumableHandover $consumable_handover)
    {
        $consumable_handover->load('items.consumable', 'creator', 'itemRequest');

        return view('consumable_handovers.show', [
            'handover' => $consumable_handover
        ]);
    }

    private function generateDocumentNumber(): string
    {
        $year = date('Y');

        $last = ConsumableHandover::where('status', 'final')
            ->whereYear('finalized_at', $year)
            ->orderByDesc('id')
            ->first();

        $lastSequence = 0;

        if ($last && preg_match('/(\d{4})$/', $last->document_number, $matches)) {
            $lastSequence = (int) $matches[1];
        }

        $nextSequence = $lastSequence + 1;

        return 'STC/' . $year . '/' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
