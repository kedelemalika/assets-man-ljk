<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Bast;
use App\Models\BastItem;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BastController extends Controller
{
    public function index()
    {
        $basts = Bast::withCount('items')
            ->latest()
            ->paginate(20);

        return view('basts.index', compact('basts'));
    }

    public function create()
    {
        $assets = Asset::with(['model', 'company'])
            ->whereNull('deleted_at')
            ->where('archived', 0)
            ->orderBy('asset_tag')
            ->get();

        $users = User::with('department')->orderBy('display_name')->get();
        $departments = Department::orderBy('name')->get();

        return view('basts.create', compact('assets', 'users', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bast_date' => 'required|date',
            'department_id' => 'required|exists:departments,id',

            'handover_by' => 'required|string|max:255',
            'handover_position' => 'nullable|string|max:255',
            'received_by' => 'required|string|max:255',
            'received_position' => 'nullable|string|max:255',

            'handover_location' => 'nullable|string|max:500',
            'receiver_location' => 'nullable|string|max:500',
            'handover_city' => 'nullable|string|max:255',
            'receiver_city' => 'nullable|string|max:255',

            'notes' => 'nullable|string',
            'asset_id' => 'required|array|min:1',
            'asset_id.*' => 'required|exists:assets,id',
            'condition_notes' => 'nullable|array',
            'remarks' => 'nullable|array',
            'action' => 'required|in:draft,final',
        ]);

        if (count($request->asset_id) !== count(array_unique($request->asset_id))) {
            return back()->withInput()->withErrors([
                'asset_id' => 'Asset yang sama dipilih lebih dari satu kali.',
            ]);
        }

        $department = Department::find($request->department_id);
        $departmentName = $department ? $department->name : null;

        $bast = DB::transaction(function () use ($request, $departmentName) {
            $status = $request->action === 'final' ? 'final' : 'draft';
            $bastNumber = null;
            $finalizedAt = null;

            if ($status === 'final') {
                $bastNumber = $this->generateFinalBastNumber($request->department_id);
                $finalizedAt = now();
            }

            $bast = Bast::create([
                'bast_number' => $bastNumber,
                'bast_date' => $request->bast_date,

                'handover_by' => $request->handover_by,
                'handover_position' => $request->handover_position,
                'received_by' => $request->received_by,
                'received_position' => $request->received_position,

                'handover_location' => $request->handover_location,
                'receiver_location' => $request->receiver_location,
                'handover_city' => $request->handover_city,
                'receiver_city' => $request->receiver_city,

                'department' => $departmentName,
                'notes' => $request->notes,
                'status' => $status,
                'finalized_at' => $finalizedAt,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->asset_id as $index => $assetId) {
                BastItem::create([
                    'bast_id' => $bast->id,
                    'asset_id' => $assetId,
                    'condition_notes' => $request->condition_notes[$index] ?? null,
                    'remarks' => $request->remarks[$index] ?? null,
                ]);
            }

            return $bast;
        });

        return redirect()->route('basts.show', $bast->id)
            ->with('success', $request->action === 'final'
                ? 'BAST berhasil difinalisasi.'
                : 'Draft BAST berhasil disimpan.');
    }

    public function show(Bast $bast)
    {
        $bast->load('items.asset', 'creator');

        return view('basts.show', compact('bast'));
    }

    public function edit(Bast $bast)
    {
        if ($bast->status === 'final') {
            abort(403, 'BAST final tidak dapat diedit.');
        }

        $bast->load('items.asset');

        $assets = Asset::with(['model', 'company'])
            ->whereNull('deleted_at')
            ->where('archived', 0)
            ->orderBy('asset_tag')
            ->get();

        $users = User::with('department')->orderBy('display_name')->get();
        $departments = Department::orderBy('name')->get();

        return view('basts.edit', compact('bast', 'assets', 'users', 'departments'));
    }

    public function update(Request $request, Bast $bast)
    {
        if ($bast->status === 'final') {
            abort(403, 'BAST final tidak dapat diedit.');
        }

        $request->validate([
            'bast_date' => 'required|date',
            'department_id' => 'required|exists:departments,id',

            'handover_by' => 'required|string|max:255',
            'handover_position' => 'nullable|string|max:255',
            'received_by' => 'required|string|max:255',
            'received_position' => 'nullable|string|max:255',

            'handover_location' => 'nullable|string|max:500',
            'receiver_location' => 'nullable|string|max:500',
            'handover_city' => 'nullable|string|max:255',
            'receiver_city' => 'nullable|string|max:255',

            'notes' => 'nullable|string',
            'asset_id' => 'required|array|min:1',
            'asset_id.*' => 'required|exists:assets,id',
            'condition_notes' => 'nullable|array',
            'remarks' => 'nullable|array',
            'action' => 'required|in:draft,final',
        ]);

        if (count($request->asset_id) !== count(array_unique($request->asset_id))) {
            return back()->withInput()->withErrors([
                'asset_id' => 'Asset yang sama dipilih lebih dari satu kali.',
            ]);
        }

        $department = Department::find($request->department_id);
        $departmentName = $department ? $department->name : null;

        DB::transaction(function () use ($request, $bast, $departmentName) {
            $status = $request->action === 'final' ? 'final' : 'draft';

            $bast->update([
                'bast_date' => $request->bast_date,

                'handover_by' => $request->handover_by,
                'handover_position' => $request->handover_position,
                'received_by' => $request->received_by,
                'received_position' => $request->received_position,

                'handover_location' => $request->handover_location,
                'receiver_location' => $request->receiver_location,
                'handover_city' => $request->handover_city,
                'receiver_city' => $request->receiver_city,

                'department' => $departmentName,
                'notes' => $request->notes,
            ]);

            if ($status === 'final' && $bast->status !== 'final') {
                $bast->update([
                    'status' => 'final',
                    'bast_number' => $this->generateFinalBastNumber($request->department_id),
                    'finalized_at' => now(),
                ]);
            } else {
                $bast->update([
                    'status' => 'draft',
                ]);
            }

            $bast->items()->delete();

            foreach ($request->asset_id as $index => $assetId) {
                BastItem::create([
                    'bast_id' => $bast->id,
                    'asset_id' => $assetId,
                    'condition_notes' => $request->condition_notes[$index] ?? null,
                    'remarks' => $request->remarks[$index] ?? null,
                ]);
            }
        });

        return redirect()->route('basts.show', $bast->id)
            ->with('success', $request->action === 'final'
                ? 'BAST berhasil difinalisasi.'
                : 'Draft BAST berhasil diperbarui.');
    }

    public function finalize(Bast $bast)
    {
        if ($bast->status === 'final') {
            return redirect()->route('basts.show', $bast->id)
                ->with('success', 'BAST ini sudah final.');
        }

        $departmentId = $this->resolveDepartmentIdByName($bast->department);

        DB::transaction(function () use ($bast, $departmentId) {
            $bast->update([
                'status' => 'final',
                'bast_number' => $this->generateFinalBastNumber($departmentId),
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('basts.show', $bast->id)
            ->with('success', 'BAST berhasil difinalisasi.');
    }

    public function print(Bast $bast)
    {
        $bast->load('items.asset.company', 'creator');

        $firstAsset = optional($bast->items->first())->asset;
        $company = null;

        if ($firstAsset && !empty($firstAsset->company_id)) {
            $company = Company::find($firstAsset->company_id);
        }

        $printAddressLines = [
            $company->address ?? null,
            null,
            null,
        ];

        return view('basts.print', compact('bast', 'company', 'printAddressLines'));
    }

    private function generateFinalBastNumber($departmentId): string
    {
        $year = date('Y');

        $department = Department::find($departmentId);
        $deptName = trim($department->name ?? 'UMUM');
        $deptCode = $this->mapDepartmentCode($deptName);

        $lastBast = Bast::where('status', 'final')
            ->where('department', $deptName)
            ->whereYear('finalized_at', $year)
            ->orderByDesc('id')
            ->first();

        $lastSequence = 0;

        if ($lastBast && preg_match('/(\d{4})$/', $lastBast->bast_number, $matches)) {
            $lastSequence = (int) $matches[1];
        }

        $nextSequence = $lastSequence + 1;

        return 'BAST/' . $deptCode . '/' . $year . '/' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    private function mapDepartmentCode(string $departmentName): string
    {
        $map = [
            'IT Department' => 'IT',
            'Information Technology' => 'IT',
            'Human Resource' => 'HR',
            'Human Resources' => 'HR',
            'HR Department' => 'HR',
            'Finance' => 'FIN',
            'Finance Accounting' => 'FA',
            'Accounting' => 'ACC',
            'General Affair' => 'GA',
            'General Affairs' => 'GA',
            'Warehouse' => 'WH',
            'Logistic' => 'LOG',
            'Logistics' => 'LOG',
            'Procurement' => 'PROC',
            'Purchasing' => 'PUR',
            'Operation' => 'OPS',
            'Operations' => 'OPS',
            'Marketing' => 'MKT',
            'Sales' => 'SLS',
        ];

        if (isset($map[$departmentName])) {
            return $map[$departmentName];
        }

        $words = preg_split('/\s+/', strtoupper($departmentName));
        $code = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $code .= substr($word, 0, 1);
            }
        }

        return substr($code, 0, 4) ?: 'UMUM';
    }

    private function resolveDepartmentIdByName(?string $departmentName): int
    {
        if (!$departmentName) {
            $dept = Department::first();
            return $dept ? $dept->id : 1;
        }

        $dept = Department::where('name', $departmentName)->first();

        if ($dept) {
            return $dept->id;
        }

        $fallback = Department::first();

        return $fallback ? $fallback->id : 1;
    }
}