<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Statement {{ $rekening->no_rekening }}</title>
<style>
    body { font-family: sans-serif; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
    th { background: #e5e7eb; font-size: 9px; text-transform: uppercase; }
    .text-right { text-align: right; }
    .header { margin-bottom: 16px; }
    .header h1 { font-size: 14px; margin: 0 0 4px; }
    .header p { margin: 2px 0; color: #555; }
    .total { font-weight: bold; }
</style>
</head>
<body>
    <div class="header">
        <h1>Statement Simpanan</h1>
        <p><strong>Rekening:</strong> {{ $rekening->no_rekening }} — {{ $rekening->produk->nama ?? '-' }}</p>
        <p><strong>Anggota:</strong> {{ $rekening->anggota->nama_lengkap ?? '-' }} ({{ $rekening->anggota->nik ?? '-' }})</p>
        <p><strong>Saldo Saat Ini:</strong> Rp {{ number_format($rekening->saldo, 0, ',', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Transaksi</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th class="text-right">Debet</th>
                <th class="text-right">Kredit</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php $saldo = 0; @endphp
            @forelse($transaksis as $t)
                @php
                    $isPenambahan = in_array($t->jenis, ['setoran', 'pinbuk_masuk', 'bunga']);
                    $saldo = $isPenambahan ? $saldo + $t->nominal : $saldo - $t->nominal;
                @endphp
                <tr>
                    <td>{{ $t->created_at->format('d/m/Y') }}</td>
                    <td>{{ $t->no_transaksi }}</td>
                    <td>{{ $t->label_jenis }}</td>
                    <td>{{ $t->keterangan }}</td>
                    <td class="text-right">{{ $isPenambahan ? number_format($t->nominal, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ !$isPenambahan ? number_format($t->nominal, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($saldo, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center">Tidak ada transaksi</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:12px;font-size:9px;color:#888;">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
