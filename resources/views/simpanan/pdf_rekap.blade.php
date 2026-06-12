<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Rekap Simpanan</title>
<style>
    body { font-family: sans-serif; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
    th { background: #e5e7eb; font-size: 9px; text-transform: uppercase; }
    .text-right { text-align: right; }
    .header { margin-bottom: 16px; }
    .header h1 { font-size: 14px; margin: 0 0 4px; }
    .total-row { font-weight: bold; background: #f3f4f6; }
</style>
</head>
<body>
    <div class="header">
        <h1>Rekapitulasi Simpanan</h1>
        <p>Per {{ now()->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Rekening</th>
                <th>Anggota</th>
                <th>Produk</th>
                <th>Cabang</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekenings as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->no_rekening }}</td>
                    <td>{{ $r->anggota->nama_lengkap ?? '-' }}</td>
                    <td>{{ $r->produk->nama ?? '-' }}</td>
                    <td>{{ $r->anggota->cabang->nama ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($r->saldo, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top:12px;font-size:9px;color:#888;">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
