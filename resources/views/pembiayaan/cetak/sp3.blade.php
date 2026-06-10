<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>SP3 - {{ $pembiayaan->no_pembiayaan }}</title>
<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:40px 20px;font-size:12px;line-height:1.6}h1{text-align:center;font-size:18px;margin-bottom:5px}h2{text-align:center;font-size:14px;color:#666;margin-top:0}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f5f5f5}.text-right{text-align:right}.signature{text-align:right;margin-top:60px}</style></head><body>
<h1>SURAT PERJANJIAN PINJAMAN (SP3)</h1>
<h2>Koperasi Lumbung Artha Sejahtera</h2>
<hr>
<p><strong>No. SP3:</strong> {{ $pembiayaan->no_pembiayaan }}</p>
<p><strong>Tanggal:</strong> {{ now()->format('d F Y') }}</p>

<table>
<tr><th style="width:30%">Nama Lengkap</th><td>{{ $pembiayaan->anggota->nama_lengkap }}</td></tr>
<tr><th>No. Anggota</th><td>{{ $pembiayaan->anggota->no_anggota }}</td></tr>
<tr><th>No. Pembiayaan</th><td>{{ $pembiayaan->no_pembiayaan }}</td></tr>
<tr><th>Nominal</th><td class="text-right">Rp {{ number_format($pembiayaan->nominal_disetujui, 0, ',', '.') }}</td></tr>
<tr><th>Bunga</th><td>{{ $pembiayaan->bunga_pa }}% per tahun ({{ ucfirst($pembiayaan->metode_hitung) }})</td></tr>
<tr><th>Jangka Waktu</th><td>{{ $pembiayaan->jangka_bulan }} bulan</td></tr>
<tr><th>Angsuran/Bulan</th><td class="text-right">Rp {{ number_format($pembiayaan->angsuran_pokok + $pembiayaan->angsuran_bunga, 0, ',', '.') }}</td></tr>
</table>

<h3>Jadwal Angsuran</h3>
<table>
<thead><tr><th>Ke</th><th>Jatuh Tempo</th><th class="text-right">Pokok</th><th class="text-right">Bunga</th><th class="text-right">Total</th></tr></thead>
<tbody>
@foreach($pembiayaan->jadwalAngsuran as $j)
<tr><td>{{ $j->ke }}</td><td>{{ $j->tanggal_jatuh_tempo->format('d M Y') }}</td><td class="text-right">Rp {{ number_format($j->pokok, 0, ',', '.') }}</td><td class="text-right">Rp {{ number_format($j->bunga, 0, ',', '.') }}</td><td class="text-right">Rp {{ number_format($j->total, 0, ',', '.') }}</td></tr>
@endforeach
</tbody>
</table>

<p>Dengan ini saya yang bertanda tangan di bawah ini menyetujui seluruh ketentuan pembiayaan di atas.</p>
<div class="signature">
<p>{{ now()->format('d F Y') }}</p>
<br><br><br>
<p>_______________________</p>
<p>{{ $pembiayaan->anggota->nama_lengkap }}</p>
</div>
<script>window.print()</script>
</body></html>
