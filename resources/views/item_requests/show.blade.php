@extends('layouts/default')

@section('title')
    Detail Pengajuan Barang
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
                        <td>{{ $itemRequest->status }}</td>
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
                            <th>Referensi</th>
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
                                    @if($item->asset)
                                        Asset: {{ $item->asset->asset_tag }}
                                    @elseif($item->consumable)
                                        Consumable: {{ $item->consumable->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="box-footer">
                @if(in_array($itemRequest->status, ['draft', 'submitted']))
                    <form action="{{ route('item-requests.approve', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-success">Approve</button>
                    </form>

                    <form action="{{ route('item-requests.reject', $itemRequest->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <input type="hidden" name="rejection_reason" value="Ditolak dari halaman detail">
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </form>
                @endif

                @if($itemRequest->status === 'approved')
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