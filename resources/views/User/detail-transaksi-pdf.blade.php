<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Transaksi</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f3f4f6;
        padding: 40px;
        font-size: 14px;
    }

    .container {
        max-width: 420px;
        margin: auto;
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }

    h1 {
        text-align: center;
        font-size: 22px;
        margin-bottom: 4px;
    }

    .trx-id {
        text-align: center;
        color: #6b7280;
        margin-bottom: 16px;
    }

    .divider {
        border-top: 1px dashed #e5e7eb;
        margin: 16px 0;
    }

    /* GRID 2 KOLOM FIX */
    .grid {
        display: grid;
        grid-template-columns: 1fr auto;
        row-gap: 10px;
    }

    .left {
        color: #6b7280;
    }

    .right {
        font-weight: 600;
        color: #111827;
        text-align: right;
        white-space: nowrap;
    }

    .section-header {
        font-weight: bold;
        color: #111827;
    }

    .sub {
        grid-column: 1 / 3;
        margin-left: 12px;
        color: #111827;
    }

    .total {
        font-size: 18px;
        font-weight: bold;
    }

    .btn {
        margin-top: 24px;
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 24px;
        background: #5a3e2b;
        color: white;
        font-size: 16px;
        cursor: pointer;
    }
</style>
</head>

<body>

<div class="container">

    <h1>Detail Transaksi</h1>
    <div class="trx-id">#001</div>

    <div class="divider"></div>

    <!-- DATA PELANGGAN -->
    <div class="grid">
        <div class="left">Nama</div>
        <div class="right">{{ $data['nama'] }}</div>

        <div class="left">No Hp</div>
        <div class="right">{{ $data['noHp'] }}</div>

        <div class="left">Jumlah</div>
        <div class="right">{{ $data['jumlahTamu'] }}</div>
    </div>

    <div class="divider"></div>

    <!-- TIPE MEJA -->
    <div class="grid">
        <div class="section-header">Tipe Meja</div>
        <div class="right">Jumlah Meja : {{ count($meja) }}</div>

        @foreach ($meja as $m)
            <div class="sub">
                {{ strtoupper($m['ruangan']) }} - {{ $m['kode_meja'] }}
            </div>
        @endforeach
    </div>

    <div class="divider"></div>

    <!-- PESANAN -->
    <div class="grid">
        <div class="section-header">Pesanan</div>
        <div class="section-header right">Harga</div>

        @foreach ($pesanan as $p)
            <div>{{ $p['qty'] }}x {{ $p['nama'] }}</div>
            <div class="right">Rp. {{ number_format($p['harga'], 0, ',', '.') }}</div>
        @endforeach
    </div>

    <div class="divider"></div>

    <!-- TOTAL -->
    <div class="grid">
        {{-- <div>Total Harga</div>
        <div class="right">Rp. {{ number_format($totalHarga, 0, ',', '.') }}</div> --}}

        {{-- <div>Pajak</div>
        <div class="right">Rp. {{ number_format($pajak, 0, ',', '.') }}</div> --}}

        <div class="total">Total</div>
        <div class="right total">Rp. {{ number_format($totalBayar, 0, ',', '.') }}</div>
    </div>

</div>

</body>
</html>
