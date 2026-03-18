@extends('layouts/default')

@section('title')
    Serah Terima Consumable
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Serah Terima Consumable</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('consumable-handovers.create') }}" class="btn btn-primary btn-sm">
                        Buat Dokumen
                    </a>
                </div>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No Dokumen</th>
                            <th>Tanggal</th>
                            <th>Penerima</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($handovers as $row)
                            <tr>
                                <td>{{ $row->document_number ?? 'Draft' }}</td>
                                <td>{{ optional($row->handover_date)->format('Y-m-d') }}</td>
                                <td>{{ $row->received_by }}</td>
                                <td>{{ $row->department }}</td>
                                <td>{{ $row->status }}</td>
                                <td>
                                    <a href="{{ route('consumable-handovers.show', $row->id) }}" class="btn btn-xs btn-info">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="box-footer clearfix">
                {{ $handovers->links() }}
            </div>
        </div>
    </div>
</div>
@stop
