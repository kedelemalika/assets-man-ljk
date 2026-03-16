<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>BAST {{ $bast->bast_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 18mm 14mm 18mm 14mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            line-height: 1.45;
        }

        .page {
            width: 100%;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .logo-wrap {
            float: left;
            width: 70px;
        }

        .logo-wrap img {
            max-width: 62px;
            max-height: 62px;
            display: block;
        }

        .company-wrap {
            overflow: hidden;
        }

        .company-name {
            font-size: 18px;
            font-weight: 700;
            margin: 2px 0 8px 0;
        }

        .company-line {
            font-size: 11px;
            margin: 2px 0;
        }

        .title {
            text-align: center;
            margin: 6px 0 14px 0;
        }

        .title h1 {
            font-size: 18px;
            margin: 0;
            text-decoration: underline;
            font-weight: 700;
        }

        .title .doc-no {
            font-size: 14px;
            margin-top: 2px;
        }

        .spacer {
            height: 8px;
        }

        .intro {
            margin: 8px 0 14px 0;
            text-align: justify;
        }

        .party-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .party-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .party-label {
            width: 26px;
        }

        .field-name {
            width: 150px;
        }

        .items-intro {
            margin: 10px 0 8px 0;
            text-align: justify;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 16px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }

        .items-table th {
            text-align: center;
            font-weight: 700;
        }

        .items-table td.center {
            text-align: center;
        }

        .closing {
            margin-top: 8px;
            text-align: justify;
        }

        .city-date {
            margin-top: 14px;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .sign-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding-top: 8px;
        }

        .sign-space {
            height: 72px;
        }

        .sign-name {
            display: inline-block;
            min-width: 180px;
        }

        .doc-code {
            margin-top: 10px;
            font-size: 11px;
        }

        .muted {
            font-size: 11px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
@php
    \Carbon\Carbon::setLocale('id');

    $companyName = $company->name ?? $snipeSettings->site_name ?? config('app.name');
    $companyLogo = !empty($snipeSettings->logo) ? Storage::disk('public')->url($snipeSettings->logo) : null;

    $companyAddress1 = $company->address ?? null;
    $companyAddress2 = null;
    $companyAddress3 = null;

    if (!empty($printAddressLines) && is_array($printAddressLines)) {
        $companyAddress1 = $printAddressLines[0] ?? $companyAddress1;
        $companyAddress2 = $printAddressLines[1] ?? null;
        $companyAddress3 = $printAddressLines[2] ?? null;
    }

    $tanggalFull = $bast->bast_date->translatedFormat('d F Y');
@endphp

<div class="page">

    <div class="header">
        <div class="logo-wrap">
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="Logo">
            @endif
        </div>

        <div class="company-wrap">
            <div class="company-name">{{ $companyName }}</div>

            @if($companyAddress1)
                <div class="company-line">{{ $companyAddress1 }}</div>
            @endif

            @if($companyAddress2)
                <div class="company-line">{{ $companyAddress2 }}</div>
            @endif

            @if($companyAddress3)
                <div class="company-line">{{ $companyAddress3 }}</div>
            @endif
        </div>
    </div>

    <div class="title">
        <h1>BERITA ACARA SERAH TERIMA BARANG</h1>
        <div class="doc-no">No. {{ $bast->bast_number ?? 'DRAFT' }}</div>
    </div>

    <div class="intro">
        Pada hari ini, tanggal <strong>{{ $tanggalFull }}</strong>, telah dilakukan serah terima barang
        antara pihak-pihak sebagai berikut:
    </div>

    <table class="party-table">
        <tr>
            <td class="party-label">1.</td>
            <td class="field-name">Nama</td>
            <td>: {{ $bast->handover_by }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan / Departemen</td>
            <td>: {{ $bast->handover_position ?: '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Alamat / Instansi</td>
            <td>: {{ $bast->handover_location ?: ($companyName ?: '-') }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">Selanjutnya disebut <strong>PIHAK PERTAMA</strong></td>
        </tr>

        <tr><td colspan="3" class="spacer"></td></tr>

        <tr>
            <td class="party-label">2.</td>
            <td class="field-name">Nama</td>
            <td>: {{ $bast->received_by }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan / Departemen</td>
            <td>: {{ $bast->received_position ?: '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Alamat / Instansi</td>
            <td>: {{ $bast->receiver_location ?: '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">Selanjutnya disebut <strong>PIHAK KEDUA</strong></td>
        </tr>
    </table>

    <div class="items-intro">
        PIHAK PERTAMA menyerahkan barang kepada PIHAK KEDUA dan PIHAK KEDUA menyatakan telah menerima
        barang dari PIHAK PERTAMA berupa daftar terlampir:
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="42">No.</th>
                <th>Nama Barang</th>
                <th width="90">Jumlah</th>
                <th width="260">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bast->items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->asset->name ?? '-' }}
                        @if(!empty($item->asset->asset_tag))
                            <div class="muted">Asset Tag: {{ $item->asset->asset_tag }}</div>
                        @endif
                        @if(!empty($item->asset->serial))
                            <div class="muted">Serial: {{ $item->asset->serial }}</div>
                        @endif
                    </td>
                    <td class="center">1</td>
                    <td>
                        {{ $item->condition_notes ?: '-' }}
                        @if(!empty($item->remarks))
                            <div>{{ $item->remarks }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">Tidak ada item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="closing">
        Demikianlah berita acara serah terima barang ini dibuat oleh kedua belah pihak.
        Sejak penandatanganan berita acara ini, maka barang tersebut menjadi tanggung jawab PIHAK KEDUA.
    </div>

    <div class="city-date">
    {{ $bast->handover_city ?: 'Jakarta' }}, {{ $bast->bast_date->translatedFormat('d F Y') }}
    </div>

    <table class="sign-table">
        <tr>
            <td>Yang menyerahkan</td>
            <td>Yang menerima</td>
        </tr>
        <tr>
            <td><div class="sign-space"></div></td>
            <td><div class="sign-space"></div></td>
        </tr>
        <tr>
            <td>
                <span class="sign-name">( {{ $bast->handover_by }} )</span><br>
                <strong>PIHAK PERTAMA</strong>
            </td>
            <td>
                <span class="sign-name">( {{ $bast->received_by }} )</span><br>
                <strong>PIHAK KEDUA</strong>
            </td>
        </tr>
    </table>

    <div class="doc-code">FR-HR-30/Rev.00</div>
</div>

</body>
</html>