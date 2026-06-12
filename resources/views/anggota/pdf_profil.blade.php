<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Profil Anggota</title>
<style>
    body { font-family: sans-serif; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
    th { background: #e5e7eb; font-size: 9px; text-transform: uppercase; }
    .header { margin-bottom: 16px; }
    .header h1 { font-size: 14px; margin: 0 0 4px; }
    .header p { margin: 2px 0; color: #555; }
</style>
</head>
<body>
    <div class="header">
        <h1>Profil Anggota</h1>
        <p>Per {{ now()->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Anggota</th>
                <th>NIK</th>
                <th>Nama Lengkap</th>
                <th>Cabang</th>
                <th>No. HP</th>
                <th>Status</th>
                <th>Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($anggota as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $a->no_anggota }}</td>
                    <td>{{ $a->nik }}</td>
                    <td>{{ $a->nama_lengkap }}</td>
                    <td>{{ $a->cabang->nama ?? '-' }}</td>
                    <td>{{ $a->no_hp ?? '-' }}</td>
                    <td>{{ $a->status }}</td>
                    <td>{{ $a->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:12px;font-size:9px;color:#888;">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
