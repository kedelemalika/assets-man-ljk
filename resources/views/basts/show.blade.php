@extends('layouts/default')

@section('title')
    Detail BAST
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Detail BAST</h3>
                <div class="box-tools pull-right">
                    @if($bast->status === 'draft')
                        <a href="{{ route('basts.edit', $bast->id) }}" class="btn btn-warning btn-sm">Edit Draft</a>

                        <form action="{{ route('basts.finalize', $bast->id) }}" method="POST" style="display:inline-block; margin-left:5px;">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm"
                                onclick="return confirm('Finalisasi draft ini? Setelah final, dokumen tidak bisa diedit.')">
                                Finalisasi
                            </button>
                        </form>
                    @endif

                    @if($bast->status === 'final')
                        <a href="{{ route('basts.print', $bast->id) }}" target="_blank" class="btn btn-default btn-sm">Print</a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <p>
                    <strong>Status:</strong>
                    @if($bast->status === 'final')
                        <span class="label label-success">FINAL</span>
                    @else
                        <span class="label label-default">DRAFT</span>
                    @endif
                </p>

                <p><strong>No. BAST:</strong> {{ $bast->bast_number ?? 'Belum ada (Draft)' }}</p>
                <p><strong>Tanggal:</strong> {{ $bast->bast_date ? $bast->bast_date->format('d-m-Y') : '-' }}</p>
                <p><strong>Penyerah:</strong> {{ $bast->handover_by }}</p>
                <p><strong>Lokasi Penyerah:</strong> {{ $bast->handover_location ?? '-' }}</p>
                <p><strong>Penerima:</strong> {{ $bast->received_by }}</p>
                <p><strong>Lokasi Penerima:</strong> {{ $bast->receiver_location ?? '-' }}</p>
                <p><strong>Departemen:</strong> {{ $bast->department }}</p>
                <p><strong>Catatan:</strong> {{ $bast->notes }}</p>

                @if($bast->finalized_at)
                    <p><strong>Finalized At:</strong> {{ $bast->finalized_at->format('d-m-Y H:i') }}</p>
                @endif

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Asset Tag</th>
                                <th>Nama Asset</th>
                                <th>Serial</th>
                                <th>Kondisi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bast->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->asset->asset_tag ?? '-' }}</td>
                                    <td>{{ $item->asset->name ?? '-' }}</td>
                                    <td>{{ $item->asset->serial ?? '-' }}</td>
                                    <td>{{ $item->condition_notes ?? '-' }}</td>
                                    <td>{{ $item->remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop