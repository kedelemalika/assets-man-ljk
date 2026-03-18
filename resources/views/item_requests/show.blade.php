@extends('layouts/default')

@section('title')
    Detail Pengajuan Barang
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">

        @php
            $currentApproval = $itemRequest->approvals->where('status', 'waiting')->sortBy('approval_order')->first();
            $isActiveApprover = $currentApproval && (int) $currentApproval->assigned_approver_id === (int) auth()->id();
            $approvedCount = $itemRequest->approvals->where('status', 'approved')->count();
            $totalApproval = $itemRequest->approvals->count();
        @endphp

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Detail Pengajuan</h3>
            </div>

            <div class="box-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">Nomor Request</th>
                        <td>{{ $itemRequest->request_number }}</td>
                    </tr>
                    <tr>
                        <th>Tipe Request</th>
                        <td>{{ ucfirst($itemRequest->request_type) }}</td>
                    </tr>
                    <tr>
                        <th>Tipe Pengadaan</th>
                        <td>{{ strtoupper($itemRequest->procurement_type ?? '-') }}</td>
                    </tr>
                    <tr>
                        <th>Pemohon</th>
                        <td>{{ optional($itemRequest->requester)->display_name ?? optional($itemRequest->requester)->first_name }}</td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td>{{ optional($itemRequest->department)->name }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><span class="label label-info">{{ $itemRequest->status }}</span></td>
                    </tr>
                    <tr>
                        <th>Progress Approval</th>
                        <td>{{ $approvedCount }} / {{ $totalApproval }} step approved</td>
                    </tr>
                    <tr>
                        <th>Approver Aktif</th>
                        <td>
                            @if($currentApproval)
                                {{ optional($currentApproval->assignedApprover)->display_name ?? 'Belum ditentukan' }}
                                @if($currentApproval->assigned_role)
                                    <br><small class="text-muted">{{ $currentApproval->assigned_role }}</small>
                                @endif
                            @else
                                <span class="label label-success">Tidak ada approval yang menunggu</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Keperluan</th>
                        <td>{{ $itemRequest->purpose }}</td>
                    </tr>
                    <tr>
                        <th>Estimasi Total</th>
                        <td>{{ number_format($itemRequest->estimated_total ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>BAST</th>
                        <td>{{ optional($itemRequest->bast)->bast_number ?? '-' }}</td>
                    </tr>
                    @if(!empty($itemRequest->rejection_reason))
                    <tr>
                        <th>Catatan Revisi / Penolakan</th>
                        <td>{{ $itemRequest->rejection_reason }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Item</h3>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Spec</th>
                            <th>Qty</th>
                            <th>Estimasi Harga</th>
                            <th>Tipe</th>
                            <th>Sumber Pemenuhan</th>
                            <th>Referensi</th>
                            <th>Registered</th>
                            <th>Fulfilled</th>
                            <th width="280">Aksi Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemRequest->items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->spec }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->estimated_price ?? 0, 2, ',', '.') }}</td>
                                <td>{{ ucfirst($item->item_type) }}</td>
                                <td>
                                    @if($item->fulfillment_type === 'existing_stock')
                                        <span class="label label-success">Stok</span>
                                    @else
                                        <span class="label label-warning">Procurement</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->asset)
                                        Asset: {{ $item->asset->asset_tag }}
                                    @elseif($item->consumable)
                                        Consumable: {{ $item->consumable->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_registered)
                                        <span class="label label-success">Ya</span>
                                    @else
                                        <span class="label label-default">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_fulfilled)
                                        <span class="label label-success">Ya</span>
                                    @else
                                        <span class="label label-default">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    @if($itemRequest->status === 'delivered' && $item->fulfillment_type === 'procurement' && !$item->is_registered)
                                        <form action="{{ route('item-requests.items.link', [$itemRequest->id, $item->id]) }}" method="POST" class="form-inline">
                                            @csrf

                                            @if($item->item_type === 'asset')
                                                <div class="form-group" style="width:100%; margin-bottom:5px;">
                                                    <select name="asset_id" class="form-control" style="width:100%;">
                                                        <option value="">-- Pilih Asset Terdaftar --</option>
                                                        @foreach($assets as $asset)
                                                            <option value="{{ $asset->id }}">
                                                                {{ $asset->asset_tag }} - {{ $asset->name ?? optional($asset->model)->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            @if($item->item_type === 'consumable')
                                                <div class="form-group" style="width:100%; margin-bottom:5px;">
                                                    <select name="consumable_id" class="form-control" style="width:100%;">
                                                        <option value="">-- Pilih Consumable Terdaftar --</option>
                                                        @foreach($consumables as $consumable)
                                                            <option value="{{ $consumable->id }}">
                                                                {{ $consumable->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <button type="submit" class="btn btn-xs btn-primary">
                                                Link Item
                                            </button>
                                        </form>
                                    @elseif($item->fulfillment_type === 'procurement' && $item->is_registered)
                                        <span class="label label-success">Sudah terhubung</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Riwayat Approval</h3>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="80">Step</th>
                            <th>Assigned To</th>
                            <th>Assigned Role</th>
                            <th>Approver</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th width="180">Acted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemRequest->approvals as $approval)
                            <tr>
                                <td>
                                    {{ $approval->approval_order }}
                                    @if($approval->status === 'waiting')
                                        <span class="label label-primary">current</span>
                                    @endif
                                </td>
                                <td>{{ optional($approval->assignedApprover)->display_name ?? '-' }}</td>
                                <td>{{ $approval->assigned_role ?? '-' }}</td>
                                <td>{{ $approval->approver_name ?? '-' }}</td>
                                <td>
                                    @if($approval->status === 'approved')
                                        <span class="label label-success">approved</span>
                                    @elseif($approval->status === 'rejected')
                                        <span class="label label-danger">rejected</span>
                                    @elseif($approval->status === 'revision_needed')
                                        <span class="label label-warning">revision_needed</span>
                                    @else
                                        <span class="label label-default">waiting</span>
                                    @endif
                                </td>
                                <td>{{ $approval->remarks ?? '-' }}</td>
                                <td>{{ optional($approval->acted_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada riwayat approval.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="box-footer">
                @if(in_array($itemRequest->status, ['submitted']) && $currentApproval && $isActiveApprover)
                    <form action="{{ route('item-requests.approve', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            Approve Step {{ $currentApproval->approval_order }}
                        </button>
                    </form>

                    <form action="{{ route('item-requests.revision-needed', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <input type="hidden" name="revision_reason" value="Perlu revisi dari halaman detail">
                        <button type="submit" class="btn btn-warning">
                            Minta Revisi
                        </button>
                    </form>

                    <form action="{{ route('item-requests.reject', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <input type="hidden" name="rejection_reason" value="Ditolak dari halaman detail">
                        <button type="submit" class="btn btn-danger">
                            Reject Step {{ $currentApproval->approval_order }}
                        </button>
                    </form>
                @endif

                @if(in_array($itemRequest->status, ['submitted']) && $currentApproval && !$isActiveApprover)
                    <div class="alert alert-info" style="display:inline-block; margin:0 10px 0 0; padding:8px 12px;">
                        Menunggu approval dari:
                        <strong>{{ optional($currentApproval->assignedApprover)->display_name ?? 'Approver' }}</strong>
                        @if($currentApproval->assigned_role)
                            ({{ $currentApproval->assigned_role }})
                        @endif
                    </div>
                @endif

                @if(in_array($itemRequest->status, ['draft', 'revision_needed']) && (int)$itemRequest->requester_id === (int)auth()->id())
                    <a href="{{ route('item-requests.edit', $itemRequest->id) }}" class="btn btn-primary">
                        Edit Draft
                    </a>

                    <form action="{{ route('item-requests.submit', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            Ajukan Sekarang
                        </button>
                    </form>
                @endif

                @if($itemRequest->status === 'procurement_process')
                    <form action="{{ route('item-requests.mark-delivered', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-primary">Mark as Delivered</button>
                    </form>
                @endif

                @if(in_array($itemRequest->status, ['approved', 'delivered']))
                    <form action="{{ route('item-requests.ready-for-handover', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-primary">Siap Serah Terima</button>
                    </form>
                @endif

                @if($itemRequest->status === 'ready_for_handover' && $itemRequest->request_type === 'asset')
                    <a href="{{ route('basts.create.from-request', $itemRequest->id) }}" class="btn btn-warning">
                        Generate BAST
                    </a>
                @endif

                @if($itemRequest->status === 'ready_for_handover' && $itemRequest->request_type === 'consumable')
                    <a href="{{ route('consumable-handovers.create.from-request', $itemRequest->id) }}" class="btn btn-warning">
                        Generate Serah Terima Consumable
                    </a>
                @endif

                @if($itemRequest->status === 'handed_over')
                    <form action="{{ route('item-requests.close', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-success">Close Request</button>
                    </form>
                @endif

                @if($itemRequest->bast_id)
                    <a href="{{ route('basts.show', $itemRequest->bast_id) }}" class="btn btn-info">
                        Lihat BAST
                    </a>
                @endif

                <a href="{{ route('item-requests.index') }}" class="btn btn-default">
                    Kembali
                </a>
            </div>
        </div>

    </div>
</div>
@stop