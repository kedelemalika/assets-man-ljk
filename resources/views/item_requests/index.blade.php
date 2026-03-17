@extends('layouts/default')

@section('title')
    Pengajuan Barang
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Pengajuan Barang</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('item-requests.create') }}" class="btn btn-primary btn-sm">
                        Buat Pengajuan
                    </a>
                </div>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No Request</th>
                            <th>Tipe</th>
                            <th>Pengadaan</th>
                            <th>Pemohon</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>BAST</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemRequests as $row)
                            <tr>
                                <td>{{ $row->request_number }}</td>
                                <td>{{ ucfirst($row->request_type) }}</td>
                                <td>{{ strtoupper($row->procurement_type ?? '-') }}</td>
                                <td>{{ optional($row->requester)->display_name ?? optional($row->requester)->first_name }}</td>
                                <td>{{ optional($row->department)->name }}</td>
                                <td>
                                    <span class="label label-info">{{ $row->status }}</span>
                                </td>
                                <td>{{ optional($row->bast)->bast_number ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('item-requests.show', $row->id) }}" class="btn btn-xs btn-info">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data pengajuan.</td>
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