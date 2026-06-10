<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Perjanjian Kredit - {{ $pembiayaan->no_pembiayaan }}</title>
<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:40px 20px;font-size:12px;line-height:1.8}h1{text-align:center;font-size:16px;margin-bottom:20px}h3{margin-top:20px;font-size:14px}table{width:100%;border-collapse:collapse;margin:15px 0}th,td{border:1px solid #ddd;padding:8px}th{background:#f5f5f5}.text-right{text-align:right}.signature{display:flex;justify-content:space-between;margin-top:80px}</style></head><body>
<h1>PERJANJIAN KREDIT PEMBIAYAAN</h1>
<p style="text-align:center">No: {{ $pembiayaan->no_pembiayaan }}</p>
<hr>

<p>Pada hari ini, {{ now()->isoFormat('dddd, D MMMM Y') }}, telah dibuat Perjanjian Kredit antara:</p>

<p><strong>PIHAK PERTAMA (Koperasi):</strong><br>
Koperasi Lumbung Artha Sejahtera<br>
Alamat: Jl. Raya Koperasi No. 1</p>

<p><strong>PIHAK KEDUA (Debitur):</strong><br>
Nama: {{ $pembiayaan->anggota->nama_lengkap }}<br>
No. KTP: {{ $pembiayaan->anggota->nik }}<br>
Alamat: {{ $pembiayaan->anggota->alamat }}</p>

<h3>Pasal 1 - Ketentuan Pembiayaan</h3>
<table>
<tr><th style="width:40%">Item</th><th>Keterangan</th></tr>
<tr><td>Nominal Pembiayaan</td><td class="text-right">Rp {{ number_format($pembiayaan->nominal_disetujui, 0, ',', '.') }}</td></tr>
<tr><td>Bunga per Tahun</td><td>{{ $pembiayaan->bunga_pa }}%</td></tr>
<tr><td>Metode Perhitungan</td><td>{{ ucfirst($pembiayaan->metode_hitung) }}</td></tr>
<tr><td>Jangka Waktu</td><td>{{ $pembiayaan->jangka_bulan }} bulan</td></tr>
<tr><td>Angsuran per Bulan</td><td class="text-right">Rp {{ number_format($pembiayaan->angsuran_pokok + $pembiayaan->angsuran_bunga, 0, ',', '.') }}</td></tr>
</table>

<h3>Pasal 2 - Kewajiban Debitur</h3>
<p>Debitur berkewajiban membayar angsuran setiap bulan sesuai jadwal yang telah ditentukan.</p>

<h3>Pasal 3 - Sanksi Keterlambatan</h3>
<p>Apabila Debitur terlambat membayar angsuran, maka akan dikenakan sanksi sesuai ketentuan koperasi.</p>

<div class="signature">
<div><p>PIHAK PERTAMA</p><br><br><br><p>Koperasi Lumbung Artha Sejahtera</p></div>
<div><p>PIHAK KEDUA</p><br><br><br><p>{{ $pembiayaan->anggota->nama_lengkap }}</p></div>
</div>
<script>window.print()</script>
</body></html>
