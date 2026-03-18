@extends('layouts/default')

@section('title')
    Daftar Pengajuan Barang
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Filter Pengajuan</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('item-requests.create') }}" class="btn btn-primary btn-sm">
                        Buat Pengajuan
                    </a>
                </div>
            </div>

            <div class="box-body">
                <div style="margin-bottom: 10px;">
                    <a href="{{ route('item-requests.index') }}"
                       class="btn btn-sm {{ empty($filter) ? 'btn-primary' : 'btn-default' }}">
                        Semua ({{ $summary['all'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'my_draft']) }}"
                       class="btn btn-sm {{ $filter === 'my_draft' ? 'btn-primary' : 'btn-default' }}">
                        Draft Saya ({{ $summary['my_draft'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'my_revision']) }}"
                       class="btn btn-sm {{ $filter === 'my_revision' ? 'btn-primary' : 'btn-default' }}">
                        Revisi Saya ({{ $summary['my_revision'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'waiting_my_approval']) }}"
                       class="btn btn-sm {{ $filter === 'waiting_my_approval' ? 'btn-primary' : 'btn-default' }}">
                        Menunggu Approval Saya ({{ $summary['waiting_my_approval'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'submitted']) }}"
                       class="btn btn-sm {{ $filter === 'submitted' ? 'btn-primary' : 'btn-default' }}">
                        Submitted ({{ $summary['submitted'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'approved']) }}"
                       class="btn btn-sm {{ $filter === 'approved' ? 'btn-primary' : 'btn-default' }}">
                        Approved ({{ $summary['approved'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'procurement_process']) }}"
                       class="btn btn-sm {{ $filter === 'procurement_process' ? 'btn-primary' : 'btn-default' }}">
                        Procurement ({{ $summary['procurement_process'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'delivered']) }}"
                       class="btn btn-sm {{ $filter === 'delivered' ? 'btn-primary' : 'btn-default' }}">
                        Delivered ({{ $summary['delivered'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'ready_for_handover']) }}"
                       class="btn btn-sm {{ $filter === 'ready_for_handover' ? 'btn-primary' : 'btn-default' }}">
                        Siap Serah Terima ({{ $summary['ready_for_handover'] }})
                    </a>

                    <a href="{{ route('item-requests.index', ['filter' => 'closed']) }}"
                       class="btn btn-sm {{ $filter === 'closed' ? 'btn-primary' : 'btn-default' }}">
                        Closed ({{ $summary['closed'] }})
                    </a>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Pengajuan</h3>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No Request</th>
                            <th>Tipe</th>
                            <th>Pemohon</th>
                            <th>Department</th>
                            <th>Estimasi Total</th>
                            <th>Status</th>
                            <th>Approval Aktif</th>
                            <th>Tanggal</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemRequests as $request)
                            @php
                                $currentApproval = $request->approvals->where('status', 'waiting')->sortBy('approval_order')->first();
                            @endphp
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>{{ ucfirst($request->request_type) }}</td>
                                <td>{{ optional($request->requester)->display_name ?? optional($request->requester)->first_name }}</td>
                                <td>{{ optional($request->department)->name ?? '-' }}</td>
                                <td>{{ number_format($request->estimated_total ?? 0, 2, ',', '.') }}</td>
                                <td>
                                    <span class="label label-info">{{ $request->status }}</span>
                                </td>
                                <td>
                                    @if($currentApproval)
                                        {{ optional($currentApproval->assignedApprover)->display_name ?? '-' }}
                                        <br>
                                        <small class="text-muted">{{ $currentApproval->assigned_role ?? '-' }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ optional($request->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('item-requests.show', $request->id) }}" class="btn btn-xs btn-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data pengajuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="box-footer clearfix">
                {{ $itemRequests->links() }}
            </div>
        </div>

    </div>
</div>
@stop