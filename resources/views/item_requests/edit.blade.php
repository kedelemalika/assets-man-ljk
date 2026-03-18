@extends('layouts/default')

@section('title')
    Edit Draft Pengajuan Barang
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

        <form method="POST" action="{{ route('item-requests.update', $item_request->id) }}">
            @csrf
            @method('PUT')

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Draft / Revisi Pengajuan</h3>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipe Request</label>
                                <select name="request_type" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="asset" {{ old('request_type', $item_request->request_type) == 'asset' ? 'selected' : '' }}>Asset</option>
                                    <option value="consumable" {{ old('request_type', $item_request->request_type) == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipe Pengadaan</label>
                                <select name="procurement_type" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    <option value="cash" {{ old('procurement_type', $item_request->procurement_type) == 'cash' ? 'selected' : '' }}>Kas Kecil</option>
                                    <option value="po" {{ old('procurement_type', $item_request->procurement_type) == 'po' ? 'selected' : '' }}>PO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" class="form-control">
                                    <option value="">-- Pilih Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $item_request->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tujuan / Keperluan</label>
                        <textarea name="purpose" class="form-control" rows="3">{{ old('purpose', $item_request->purpose) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Item Pengajuan</h3>
                </div>

                <div class="box-body table-responsive">
                    <div class="alert alert-info" style="margin-bottom:15px;">
                        <strong>Panduan:</strong><br>
                        - Pilih <strong>Ambil dari Stok Existing</strong> jika barang sudah tersedia di Assets.<br>
                        - Pilih <strong>Pengadaan Baru</strong> jika barang belum tersedia dan harus dibeli terlebih dahulu.<br>
                        - Untuk <strong>Pengadaan Baru</strong>, field Asset Existing / Consumable Existing tidak perlu dipilih.
                    </div>

                    <table class="table table-bordered" id="items-table">
                        <thead>
                            <tr>
                                <th>Nama Item</th>
                                <th>Spesifikasi</th>
                                <th>Qty</th>
                                <th>Estimasi Harga / Unit</th>
                                <th>Tipe Item</th>
                                <th>Sumber Barang</th>
                                <th>Asset Existing</th>
                                <th>Consumable Existing</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item_request->items as $index => $item)
                                <tr>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][item_name]" class="form-control" value="{{ old("items.$index.item_name", $item->item_name) }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][spec]" class="form-control" value="{{ old("items.$index.spec", $item->spec) }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][qty]" class="form-control" min="1" value="{{ old("items.$index.qty", $item->qty) }}" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[{{ $index }}][estimated_price]" class="form-control" value="{{ old("items.$index.estimated_price", $item->estimated_price) }}">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][item_type]" class="form-control item-type-select" required>
                                            <option value="asset" {{ old("items.$index.item_type", $item->item_type) == 'asset' ? 'selected' : '' }}>Asset</option>
                                            <option value="consumable" {{ old("items.$index.item_type", $item->item_type) == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][fulfillment_type]" class="form-control fulfillment-type-select" required>
                                            <option value="existing_stock" {{ old("items.$index.fulfillment_type", $item->fulfillment_type) == 'existing_stock' ? 'selected' : '' }}>Ambil dari Stok Existing</option>
                                            <option value="procurement" {{ old("items.$index.fulfillment_type", $item->fulfillment_type) == 'procurement' ? 'selected' : '' }}>Pengadaan Baru</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][asset_id]" class="form-control asset-select">
                                            <option value="">-- Pilih Asset --</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}" {{ old("items.$index.asset_id", $item->asset_id) == $asset->id ? 'selected' : '' }}>
                                                    {{ $asset->asset_tag }} - {{ $asset->name ?? optional($asset->model)->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][consumable_id]" class="form-control consumable-select">
                                            <option value="">-- Pilih Consumable --</option>
                                            @foreach($consumables as $consumable)
                                                <option value="{{ $consumable->id }}" {{ old("items.$index.consumable_id", $item->consumable_id) == $consumable->id ? 'selected' : '' }}>
                                                    {{ $consumable->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-default" id="add-row">Tambah Item</button>
                </div>

                <div class="box-footer">
                    <button type="submit" name="action" value="draft" class="btn btn-default">Simpan Draft</button>
                    <button type="submit" name="action" value="submit" class="btn btn-primary">Simpan & Ajukan</button>
                    <a href="{{ route('item-requests.show', $item_request->id) }}" class="btn btn-default">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('moar_scripts')
<script>
    let rowIndex = {{ $item_request->items->count() }};

    function toggleRowFields(row) {
        const itemType = row.querySelector('.item-type-select').value;
        const fulfillmentType = row.querySelector('.fulfillment-type-select').value;

        const assetSelect = row.querySelector('.asset-select');
        const consumableSelect = row.querySelector('.consumable-select');

        assetSelect.disabled = false;
        consumableSelect.disabled = false;

        if (fulfillmentType === 'existing_stock') {
            if (itemType === 'asset') {
                assetSelect.disabled = false;
                consumableSelect.disabled = true;
                consumableSelect.value = '';
            } else {
                consumableSelect.disabled = false;
                assetSelect.disabled = true;
                assetSelect.value = '';
            }
        } else {
            assetSelect.disabled = true;
            consumableSelect.disabled = true;
            assetSelect.value = '';
            consumableSelect.value = '';
        }
    }

    function bindRowEvents(row) {
        const itemTypeSelect = row.querySelector('.item-type-select');
        const fulfillmentTypeSelect = row.querySelector('.fulfillment-type-select');

        itemTypeSelect.addEventListener('change', function () {
            toggleRowFields(row);
        });

        fulfillmentTypeSelect.addEventListener('change', function () {
            toggleRowFields(row);
        });

        toggleRowFields(row);
    }

    document.querySelectorAll('#items-table tbody tr').forEach(function (row) {
        bindRowEvents(row);
    });

    document.getElementById('add-row').addEventListener('click', function () {
        const tableBody = document.querySelector('#items-table tbody');
        const row = document.createElement('tr');

        row.innerHTML = `
            <td><input type="text" name="items[${rowIndex}][item_name]" class="form-control" required></td>
            <td><input type="text" name="items[${rowIndex}][spec]" class="form-control"></td>
            <td><input type="number" name="items[${rowIndex}][qty]" class="form-control" min="1" value="1" required></td>
            <td><input type="number" step="0.01" name="items[${rowIndex}][estimated_price]" class="form-control"></td>
            <td>
                <select name="items[${rowIndex}][item_type]" class="form-control item-type-select" required>
                    <option value="asset">Asset</option>
                    <option value="consumable">Consumable</option>
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][fulfillment_type]" class="form-control fulfillment-type-select" required>
                    <option value="existing_stock">Ambil dari Stok Existing</option>
                    <option value="procurement">Pengadaan Baru</option>
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][asset_id]" class="form-control asset-select">
                    <option value="">-- Pilih Asset --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->asset_tag }} - {{ $asset->name ?? optional($asset->model)->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][consumable_id]" class="form-control consumable-select">
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
        bindRowEvents(row);
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