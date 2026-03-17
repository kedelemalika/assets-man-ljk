@extends('layouts/default')

@section('title')
    Buat Pengajuan Barang
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan:</strong>
                <ul style="margin-bottom:0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('item-requests.store') }}">
            @csrf

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Pengajuan</h3>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipe Request</label>
                                <select name="request_type" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="asset" {{ old('request_type') == 'asset' ? 'selected' : '' }}>Asset</option>
                                    <option value="consumable" {{ old('request_type') == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipe Pengadaan</label>
                                <select name="procurement_type" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    <option value="cash" {{ old('procurement_type') == 'cash' ? 'selected' : '' }}>Kas Kecil</option>
                                    <option value="po" {{ old('procurement_type') == 'po' ? 'selected' : '' }}>PO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" class="form-control">
                                    <option value="">-- Pilih Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tujuan / Keperluan</label>
                        <textarea name="purpose" class="form-control" rows="3">{{ old('purpose') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Item Pengajuan</h3>
                </div>

                <div class="box-body table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead>
                            <tr>
                                <th>Nama Item</th>
                                <th>Spesifikasi</th>
                                <th>Qty</th>
                                <th>Estimasi Harga</th>
                                <th>Tipe</th>
                                <th>Asset Existing</th>
                                <th>Consumable Existing</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" name="items[0][item_name]" class="form-control" required>
                                </td>
                                <td>
                                    <input type="text" name="items[0][spec]" class="form-control">
                                </td>
                                <td>
                                    <input type="number" name="items[0][qty]" class="form-control" min="1" value="1" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][estimated_price]" class="form-control">
                                </td>
                                <td>
                                    <select name="items[0][item_type]" class="form-control" required>
                                        <option value="asset">Asset</option>
                                        <option value="consumable">Consumable</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][asset_id]" class="form-control">
                                        <option value="">-- Pilih Asset --</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}">
                                                {{ $asset->asset_tag }} - {{ $asset->name ?? optional($asset->model)->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][consumable_id]" class="form-control">
                                        <option value="">-- Pilih Consumable --</option>
                                        @foreach($consumables as $consumable)
                                            <option value="{{ $consumable->id }}">
                                                {{ $consumable->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-default" id="add-row">Tambah Item</button>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Simpan Pengajuan</button>
                    <a href="{{ route('item-requests.index') }}" class="btn btn-default">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('moar_scripts')
<script>
    let rowIndex = 1;

    document.getElementById('add-row').addEventListener('click', function () {
        const tableBody = document.querySelector('#items-table tbody');
        const row = document.createElement('tr');

        row.innerHTML = `
            <td><input type="text" name="items[${rowIndex}][item_name]" class="form-control" required></td>
            <td><input type="text" name="items[${rowIndex}][spec]" class="form-control"></td>
            <td><input type="number" name="items[${rowIndex}][qty]" class="form-control" min="1" value="1" required></td>
            <td><input type="number" step="0.01" name="items[${rowIndex}][estimated_price]" class="form-control"></td>
            <td>
                <select name="items[${rowIndex}][item_type]" class="form-control" required>
                    <option value="asset">Asset</option>
                    <option value="consumable">Consumable</option>
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][asset_id]" class="form-control">
                    <option value="">-- Pilih Asset --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->asset_tag }} - {{ $asset->name ?? optional($asset->model)->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][consumable_id]" class="form-control">
                    <option value="">-- Pilih Consumable --</option>
                    @foreach($consumables as $consumable)
                        <option value="{{ $consumable->id }}">
                            {{ $consumable->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
            </td>
        `;

        tableBody.appendChild(row);
        rowIndex++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('#items-table tbody tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });
</script>
@stop