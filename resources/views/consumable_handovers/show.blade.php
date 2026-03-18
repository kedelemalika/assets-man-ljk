@extends('layouts/default')

@section('title')
    Detail Serah Terima Consumable
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
                <h3 class="box-title">Detail Dokumen</h3>
            </div>

            <div class="box-body">
                <table class="table table-bordered">
                    <tr><th width="220">No Dokumen</th><td>{{ $handover->document_number ?? 'Draft' }}</td></tr>
                    <tr><th>Tanggal</th><td>{{ optional($handover->handover_date)->format('Y-m-d') }}</td></tr>
                    <tr><th>Penyerah</th><td>{{ $handover->handover_by }}</td></tr>
                    <tr><th>Penerima</th><td>{{ $handover->received_by }}</td></tr>
                    <tr><th>Department</th><td>{{ $handover->department }}</td></tr>
                    <tr><th>Status</th><td>{{ $handover->status }}</td></tr>
                    <tr><th>Request</th><td>{{ optional($handover->itemRequest)->request_number ?? '-' }}</td></tr>
                    <tr><th>Catatan</th><td>{{ $handover->notes }}</td></tr>
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
                            <th>Consumable</th>
                            <th>Qty</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($handover->items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ optional($item->consumable)->name ?? '-' }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ $item->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="box-footer">
                <a href="{{ route('consumable-handovers.index') }}" class="btn btn-default">Kembali</a>
            </div>
        </div>
    </div>
</div>
@stop
