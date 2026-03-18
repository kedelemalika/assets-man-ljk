@extends('layouts/default')

@section('title')
    Buat BAST
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <form id="create-form" method="POST" action="{{ route('basts.store') }}">
            @csrf

            @if(isset($item_request))
                <input type="hidden" name="item_request_id" value="{{ $item_request->id }}">
            @endif

            <input type="hidden" name="handover_by" id="handover_by" value="{{ old('handover_by', isset($item_request) ? optional($item_request->requester)->display_name : '') }}">
            <input type="hidden" name="received_by" id="received_by" value="{{ old('received_by', isset($item_request) ? optional($item_request->requester)->display_name : '') }}">

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
                    BAST ini dibuat dari pengajuan: <strong>{{ $item_request->request_number }}</strong>
                </div>
            @endif

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Form BAST</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No. BAST</label>
                                <input type="text"
                                       class="form-control"
                                       value="Akan dibuat saat finalisasi"
                                       readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal BAST</label>
                                <input type="date"
                                       name="bast_date"
                                       class="form-control"
                                       value="{{ old('bast_date', date('Y-m-d')) }}"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" class="form-control" required>
                                    <option value="">-- Pilih Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ old('department_id', isset($item_request) ? $item_request->department_id : null) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h4>Penyerah</h4>

                            <div class="form-group">
                                <label>Pilih dari People</label>
                                <select id="handover_user_select" class="form-control">
                                    <option value="">-- Pilih user --</option>
                                    @foreach($users as $user)
                                        @php
                                            $loc = $user->location;
                                            $fullAddress = '';
                                            $cityOnly = '';

                                            if ($loc) {
                                                $parts = array_filter([
                                                    $loc->address ?? null,
                                                    $loc->city ?? null,
                                                    $loc->zip ?? null,
                                                ]);
                                                $fullAddress = implode(', ', $parts);
                                                $cityOnly = $loc->city ?? '';
                                            }

                                            $jobTitle = $user->jobtitle ?? optional($user->department)->name ?? '';
                                        @endphp
                                        <option value="{{ $user->display_name }}"
                                                data-address="{{ $fullAddress }}"
                                                data-city="{{ $cityOnly }}"
                                                data-position="{{ $jobTitle }}"
                                                {{ old('handover_by') == $user->display_name ? 'selected' : '' }}>
                                            {{ $user->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group manual-name-group" id="handover_manual_group">
                                <label>Atau isi manual</label>
                                <input type="text"
                                       id="handover_manual"
                                       class="form-control"
                                       value="{{ old('handover_by') }}">
                            </div>

                            <div class="form-group">
                                <label>Jabatan / Departemen Penyerah</label>
                                <input type="text"
                                       name="handover_position"
                                       class="form-control"
                                       value="{{ old('handover_position') }}">
                            </div>

                            <div class="form-group">
                                <label>Alamat Penyerah</label>
                                <input type="text"
                                       name="handover_location"
                                       class="form-control"
                                       value="{{ old('handover_location') }}">
                            </div>

                            <div class="form-group">
                                <label>Kota Penyerah</label>
                                <input type="text"
                                       name="handover_city"
                                       class="form-control"
                                       value="{{ old('handover_city') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h4>Penerima</h4>

                            <div class="form-group">
                                <label>Pilih dari People</label>
                                <select id="receiver_user_select" class="form-control">
                                    <option value="">-- Pilih user --</option>
                                    @foreach($users as $user)
                                        @php
                                            $fullAddress = trim(implode(', ', array_filter([
                                                $user->address ?? null,
                                                $user->state ?? null,
                                                $user->country ?? null,
                                                $user->zip ?? null,
                                            ])));

                                            $cityOnly = $user->city ?? '';
                                            $jobTitle = $user->jobtitle ?? '';
                                        @endphp

                                        <option value="{{ $user->display_name }}"
                                                data-address="{{ $fullAddress }}"
                                                data-city="{{ $cityOnly }}"
                                                data-position="{{ $jobTitle }}"
                                                {{ old('received_by', isset($item_request) ? optional($item_request->requester)->display_name : '') == $user->display_name ? 'selected' : '' }}>
                                            {{ $user->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group manual-name-group" id="receiver_manual_group">
                                <label>Atau isi manual</label>
                                <input type="text"
                                       id="receiver_manual"
                                       class="form-control"
                                       value="{{ old('received_by', isset($item_request) ? optional($item_request->requester)->display_name : '') }}">
                            </div>

                            <div class="form-group">
                                <label>Jabatan / Departemen Penerima</label>
                                <input type="text"
                                       name="received_position"
                                       class="form-control"
                                       value="{{ old('received_position', isset($item_request) ? optional($item_request->department)->name : '') }}">
                            </div>

                            <div class="form-group">
                                <label>Alamat Penerima</label>
                                <input type="text"
                                       name="receiver_location"
                                       class="form-control"
                                       value="{{ old('receiver_location') }}">
                            </div>

                            <div class="form-group">
                                <label>Kota Penerima</label>
                                <input type="text"
                                       name="receiver_city"
                                       class="form-control"
                                       value="{{ old('receiver_city') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', isset($item_request) ? 'Generated from request: '.$item_request->request_number : '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Asset</h3>
                </div>
                <div class="box-body">
                    @php
                        $prefillAssets = isset($item_request)
                            ? $item_request->items->pluck('asset_id')->filter()->values()
                            : collect([null]);
                    @endphp

                    <table class="table table-bordered" id="asset-table">
                        <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Kondisi</th>
                                <th>Keterangan</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prefillAssets as $prefillAssetId)
                                <tr>
                                    <td>
                                        <select name="asset_id[]" class="form-control" required>
                                            <option value="">-- Pilih Asset --</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}" {{ (string)$prefillAssetId === (string)$asset->id ? 'selected' : '' }}>
                                                    {{ $asset->asset_tag }} - {{ $asset->name ?? ($asset->model->name ?? 'Tanpa Nama') }}{{ $asset->serial ? ' / '.$asset->serial : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="condition_notes[]" class="form-control">
                                    </td>
                                    <td>
                                        <input type="text" name="remarks[]" class="form-control">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-default" id="add-row">Tambah Asset</button>
                </div>
                <div class="box-footer">
                    <button type="submit" name="action" value="draft" class="btn btn-default">Simpan Draft</button>
                    <button type="submit" name="action" value="final" class="btn btn-primary">Finalisasi BAST</button>
                    <a href="{{ route('basts.index') }}" class="btn btn-default">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('moar_scripts')
<script>
function syncPersonFields() {
    const handoverSelect = document.getElementById('handover_user_select');
    const receiverSelect = document.getElementById('receiver_user_select');
    const handoverManual = document.getElementById('handover_manual');
    const receiverManual = document.getElementById('receiver_manual');

    document.getElementById('handover_by').value =
        handoverManual && handoverManual.value.trim() !== '' ? handoverManual.value.trim() : handoverSelect.value;

    document.getElementById('received_by').value =
        receiverManual && receiverManual.value.trim() !== '' ? receiverManual.value.trim() : receiverSelect.value;
}

function updatePersonDetails(selectId, addressField, cityField, positionField, manualGroupId) {
    const select = document.getElementById(selectId);
    const selectedOption = select.options[select.selectedIndex];

    const address = selectedOption ? (selectedOption.dataset.address || '') : '';
    const city = selectedOption ? (selectedOption.dataset.city || '') : '';
    const position = selectedOption ? (selectedOption.dataset.position || '') : '';

    if (address !== '') {
        document.querySelector(addressField).value = address;
    }

    if (city !== '') {
        document.querySelector(cityField).value = city;
    }

    if (position !== '') {
        document.querySelector(positionField).value = position;
    }

    const manualGroup = document.getElementById(manualGroupId);
    if (select.value !== '') {
        manualGroup.style.display = 'none';
    } else {
        manualGroup.style.display = 'block';
    }
}

document.getElementById('create-form').addEventListener('submit', function () {
    syncPersonFields();
});

document.getElementById('handover_user_select').addEventListener('change', function() {
    updatePersonDetails(
        'handover_user_select',
        'input[name="handover_location"]',
        'input[name="handover_city"]',
        'input[name="handover_position"]',
        'handover_manual_group'
    );
    syncPersonFields();
});

document.getElementById('receiver_user_select').addEventListener('change', function() {
    updatePersonDetails(
        'receiver_user_select',
        'input[name="receiver_location"]',
        'input[name="receiver_city"]',
        'input[name="received_position"]',
        'receiver_manual_group'
    );
    syncPersonFields();
});

document.getElementById('handover_manual').addEventListener('input', syncPersonFields);
document.getElementById('receiver_manual').addEventListener('input', syncPersonFields);

document.getElementById('add-row').addEventListener('click', function () {
    const tbody = document.querySelector('#asset-table tbody');
    const rows = tbody.querySelectorAll('tr');
    const firstRow = rows[0];
    const newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

    tbody.appendChild(newRow);
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        const rows = document.querySelectorAll('#asset-table tbody tr');
        if (rows.length > 1) {
            e.target.closest('tr').remove();
        }
    }
});

window.addEventListener('load', function() {
    updatePersonDetails(
        'handover_user_select',
        'input[name="handover_location"]',
        'input[name="handover_city"]',
        'input[name="handover_position"]',
        'handover_manual_group'
    );

    updatePersonDetails(
        'receiver_user_select',
        'input[name="receiver_location"]',
        'input[name="receiver_city"]',
        'input[name="received_position"]',
        'receiver_manual_group'
    );

    syncPersonFields();
});
</script>
@stop