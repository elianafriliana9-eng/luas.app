<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penutupan Keanggotaan - {{ $anggota->no_anggota }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        h1, h2, h3 { margin-bottom: 5px; color: #1f2937; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; background-color: #f3f4f6; padding: 5px; margin-top: 15px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background-color: #f9fafb; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mb-2 { margin-bottom: 10px; }
        .flex-row { display: table; width: 100%; margin-bottom: 15px; }
        .col-half { display: table-cell; width: 50%; }
        .label { font-weight: bold; display: inline-block; width: 120px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>KOPERASI SAKU (KOPSAKU)</h1>
        <p>Jl. Contoh No. 123, Jakarta Raya - Telp: (021) 1234567</p>
        <h2>BUKTI PENUTUPAN KEANGGOTAAN</h2>
    </div>

    <div class="flex-row">
        <div class="col-half">
            <div><span class="label">No. Anggota</span>: {{ $anggota->no_anggota }}</div>
            <div><span class="label">Nama Lengkap</span>: {{ $anggota->nama_lengkap }}</div>
            <div><span class="label">NIK</span>: {{ $anggota->nik }}</div>
            
        </div>
        <div class="col-half">
            <div><span class="label">Tgl. Masuk</span>: {{ $anggota->tanggal_masuk?->format('d M Y') ?? '-' }}</div>
            <div><span class="label">Tgl. Keluar</span>: {{ $anggota->tanggal_keluar?->format('d M Y') ?? '-' }}</div>
            <div><span class="label">Alasan Keluar</span>: {{ $anggota->alasan_keluar ?? '-' }}</div>
        </div>
    </div>

    <p class="mb-2">
        Dengan dicetaknya surat ini, menyatakan bahwa anggota tersebut di atas telah <strong>resmi keluar</strong> dari keanggotaan Kopsaku. Seluruh hak dan kewajiban telah diselesaikan, dan seluruh simpanan telah ditarik sejumlah rincian berikut:
    </p>

    <div class="section-title">Riwayat Penarikan Saldo Penutupan (Otomatis)</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:25%">Rekening (Produk)</th>
                <th style="width:20%">Tgl Transaksi</th>
                <th style="width:30%">Keterangan</th>
                <th style="width:20%" class="text-right">Nominal Ditarik</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPenarikanKeluar = 0; $no = 1; @endphp
            @foreach($historyTransaksi as $trx)
                {{-- Hanya tampilkan transaksi penarikan otomatis saat keluar --}}
                @if($trx->jenis === 'penarikan' && str_contains(strtolower($trx->keterangan), 'keluar'))
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td>{{ $trx->rekening?->no_rekening }} <br><small>({{ $trx->rekening?->produk?->nama }})</small></td>
                        <td>{{ $trx->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $trx->keterangan }}</td>
                        <td class="text-right">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @php $totalPenarikanKeluar += $trx->nominal; @endphp
                @endif
            @endforeach
            @if($no == 1)
                <tr><td colspan="5" class="text-center">Tidak ada penarikan akhir (saldo sudah 0 sebelum penutupan).</td></tr>
            @endif
        </tbody>
        @if($no > 1)
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total Dana Dikembalikan:</th>
                <th class="text-right">Rp {{ number_format($totalPenarikanKeluar, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="section-title">Rangkuman Riwayat Simpanan & Transaksi Lainnya</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:15%">Tanggal</th>
                <th style="width:20%">Rekening</th>
                <th style="width:15%">Jenis</th>
                <th style="width:20%" class="text-right">Nominal</th>
                <th style="width:25%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no2 = 1; @endphp
            @forelse($historyTransaksi as $trx)
                <tr>
                    <td class="text-center">{{ $no2++ }}</td>
                    <td>{{ $trx->created_at->format('d M Y') }}</td>
                    <td>{{ $trx->rekening?->no_rekening }} <small>({{ $trx->rekening?->produk?->kode }})</small></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $trx->jenis)) }}</td>
                    <td class="text-right">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</td>
                    <td>{{ $trx->keterangan }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Tidak ada riwayat transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Riwayat Pembiayaan (Pinjaman)</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:20%">No. Pembiayaan</th>
                <th style="width:25%">Produk</th>
                <th style="width:20%" class="text-right">Plafon</th>
                <th style="width:15%">Tgl Cair</th>
                <th style="width:15%">Status Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($anggota->pembiayaan as $idx => $pem)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $pem->no_pembiayaan }}</td>
                    <td>{{ $pem->produk?->nama ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($pem->plafon, 0, ',', '.') }}</td>
                    <td>{{ $pem->tanggal_cair ? $pem->tanggal_cair->format('d/m/Y') : '-' }}</td>
                    <td>{{ ucfirst($pem->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Anggota tidak memiliki riwayat pembiayaan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <br><br><br>
    <div class="flex-row" style="margin-top: 30px;">
        <div class="col-half text-center">
            <p>Mengetahui,</p>
            <p><strong>Koperasi Saku</strong></p>
            <br><br><br><br>
            <p>(............................................)</p>
            <p>Petugas / Super Admin</p>
        </div>
        <div class="col-half text-center">
            <p>Jakarta, {{ now()->format('d F Y') }}</p>
            <p><strong>Mantan Anggota</strong></p>
            <br><br><br><br>
            <p>( <strong>{{ $anggota->nama_lengkap }}</strong> )</p>
        </div>
    </div>

</body>
</html>
