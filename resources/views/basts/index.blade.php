@extends('layouts/default')

@section('title')
    Daftar BAST
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar BAST</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('basts.create') }}" class="btn btn-primary btn-sm">
                        Buat BAST
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No. BAST</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Penyerah</th>
                                <th>Penerima</th>
                                <th>Jumlah Asset</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($basts as $bast)
                                <tr>
                                    <td>{{ $bast->bast_number ?? 'DRAFT' }}</td>
                                    <td>
                                        @if($bast->status === 'final')
                                            <span class="label label-success">FINAL</span>
                                        @else
                                            <span class="label label-default">DRAFT</span>
                                        @endif
                                    </td>
                                    <td>{{ $bast->bast_date ? $bast->bast_date->format('d-m-Y') : '-' }}</td>
                                    <td>{{ $bast->handover_by }}</td>
                                    <td>{{ $bast->received_by }}</td>
                                    <td>{{ $bast->items_count }}</td>
                                    <td>
                                        <a href="{{ route('basts.show', $bast->id) }}" class="btn btn-xs btn-info">Detail</a>

                                        @if($bast->status === 'draft')
                                            <a href="{{ route('basts.edit', $bast->id) }}" class="btn btn-xs btn-warning">Edit</a>
                                        @endif

                                        @if($bast->status === 'final')
                                            <a href="{{ route('basts.print', $bast->id) }}" class="btn btn-xs btn-default" target="_blank">Print</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data BAST.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $basts->links() }}
            </div>
        </div>
    </div>
</div>
@stop