@extends('layouts/default')

@section('title')
    Buat Serah Terima Consumable
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <form method="POST" action="{{ route('consumable-handovers.store') }}">
            @csrf

            @if(isset($item_request))
                <input type="hidden" name="item_request_id" value="{{ $item_request->id }}">
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin-bottom:0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($item_request))
                <div class="alert alert-info">
                    Dokumen ini dibuat dari pengajuan: <strong>{{ $item_request->request_number }}</strong>
                </div>
            @endif

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Serah Terima Consumable</h3>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No Dokumen</label>
                                <input type="text" class="form-control" value="Akan dibuat saat finalisasi" readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="handover_date" class="form-control"
                                       value="{{ old('handover_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" name="department" class="form-control"
                                       value="{{ old('department', isset($item_request) ? optional($item_request->department)->name : '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Penyerah</label>
                        <input type="text" name="handover_by" class="form-control" value="{{ old('handover_by') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Penerima</label>
                        <input type="text" name="received_by" class="form-control"
                               value="{{ old('received_by', isset($item_request) ? optional($item_request->requester)->display_name : '') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Item Consumable</h3>
                </div>

                <div class="box-body table-responsive">
                    @php
                        $prefillItems = isset($item_request) ? $item_request->items : collect([null]);
                    @endphp

                    <table class="table table-bordered" id="consumable-items-table">
                        <thead>
                            <tr>
                                <th>Nama Item</th>
                                <th>Consumable Existing</th>
                                <th>Qty</th>
                                <th>Keterangan</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prefillItems as $index => $prefillItem)
                                <tr>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][item_name]" class="form-control"
                                               value="{{ old('items.'.$index.'.item_name', $prefillItem->item_name ?? '') }}" required>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][consumable_id]" class="form-control">
                                            <option value="">-- Pilih Consumable --</option>
                                            @foreach($consumables as $consumable)
                                                <option value="{{ $consumable->id }}"
                                                    {{ (string)old('items.'.$index.'.consumable_id', $prefillItem->consumable_id ?? '') === (string)$consumable->id ? 'selected' : '' }}>
                                                    {{ $consumable->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][qty]" class="form-control" min="1"
                                               value="{{ old('items.'.$index.'.qty', $prefillItem->qty ?? 1) }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][remarks]" class="form-control"
                                               value="{{ old('items.'.$index.'.remarks') }}">
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
                    <button type="submit" name="action" value="final" class="btn btn-primary">Finalisasi</button>
                    <a href="{{ route('consumable-handovers.index') }}" class="btn btn-default">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('moar_scripts')
<script>
let rowIndex = {{ isset($item_request) ? $item_request->items->count() : 1 }};

document.getElementById('add-row').addEventListener('click', function () {
    const tbody = document.querySelector('#consumable-items-table tbody');
    const row = document.createElement('tr');

    row.innerHTML = `
        <td>
            <input type="text" name="items[${rowIndex}][item_name]" class="form-control" required>
        </td>
        <td>
            <select name="items[${rowIndex}][consumable_id]" class="form-control">
                <option value="">-- Pilih Consumable --</option>
                @foreach($consumables as $consumable)
                    <option value="{{ $consumable->id }}">{{ $consumable->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[${rowIndex}][qty]" class="form-control" min="1" value="1" required>
        </td>
        <td>
            <input type="text" name="items[${rowIndex}][remarks]" class="form-control">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
        </td>
    `;

    tbody.appendChild(row);
    rowIndex++;
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        const rows = document.querySelectorAll('#consumable-items-table tbody tr');
        if (rows.length > 1) {
            e.target.closest('tr').remove();
        }
    }
});
</script>
@stop
