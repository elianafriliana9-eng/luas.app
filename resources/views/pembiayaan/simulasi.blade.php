<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Simulasi Pembiayaan</h2>
            <a href="{{ route('pembiayaan.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a>
        </div>
    </x-slot>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Kalkulator Simulasi</h3>
            <form id="simulasiForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Pinjaman</label>
                        <input type="text" id="nominal" value="{{ $nominal }}" inputmode="numeric" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jangka Waktu (Bulan)</label>
                        <input type="number" id="jangka" value="{{ $jangka }}" min="1" max="60" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bunga (% per tahun)</label>
                        <input type="number" id="bunga" value="{{ $bunga }}" min="0" max="100" step="0.5" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metode Perhitungan</label>
                        <select id="metode" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="flat" {{ $metode == 'flat' ? 'selected' : '' }}>Flat</option>
                            <option value="anuitas" {{ $metode == 'anuitas' ? 'selected' : '' }}>Anuitas</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Hitung Simulasi</button>
            </form>
        </div>

        <!-- Hasil -->
        <div id="hasil" class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Hasil Simulasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex justify-between"><span class="text-gray-500 text-sm">Angsuran Pokok</span><span id="hasilPokok" class="font-mono font-semibold">Rp {{ number_format($angsuranPokok, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-sm">Angsuran Bunga</span><span id="hasilBunga" class="font-mono font-semibold">Rp {{ number_format($angsuranBunga, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span class="text-gray-700 text-sm font-bold">Total Angsuran/Bulan</span><span id="hasilTotal" class="font-mono font-bold text-indigo-600 text-lg">Rp {{ number_format($totalAngsuran, 0, ',', '.') }}</span></div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between"><span class="text-gray-500 text-sm">Total Bunga</span><span id="hasilTotalBunga" class="font-mono font-semibold text-red-600">Rp {{ number_format($totalBunga, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span class="text-gray-700 text-sm font-bold">Total Bayar</span><span id="hasilTotalBayar" class="font-mono font-bold text-green-600 text-lg">Rp {{ number_format($nominal + $totalBunga, 0, ',', '.') }}</span></div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t flex justify-end">
                <a id="btnAjukan" href="{{ route('pembiayaan.pengajuan.create') }}" class="px-6 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">Ajukan Pembiayaan →</a>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('simulasiForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const nominal = unformatRupiah(document.getElementById('nominal').value);
        const jangka = document.getElementById('jangka').value;
        const bunga = document.getElementById('bunga').value;
        const metode = document.getElementById('metode').value;

        const res = await fetch('{{ route("pembiayaan.simulasi.hitung") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ nominal, jangka, bunga, metode })
        });
        const data = await res.json();

        document.getElementById('hasilPokok').textContent = 'Rp ' + Number(data.angsuran_pokok).toLocaleString('id-ID', {maximumFractionDigits: 0});
        document.getElementById('hasilBunga').textContent = 'Rp ' + Number(data.angsuran_bunga).toLocaleString('id-ID', {maximumFractionDigits: 0});
        document.getElementById('hasilTotal').textContent = 'Rp ' + Number(data.total_angsuran).toLocaleString('id-ID', {maximumFractionDigits: 0});
        document.getElementById('hasilTotalBunga').textContent = 'Rp ' + Number(data.total_bunga).toLocaleString('id-ID', {maximumFractionDigits: 0});
        document.getElementById('hasilTotalBayar').textContent = 'Rp ' + Number(data.total_bayar).toLocaleString('id-ID', {maximumFractionDigits: 0});
        document.getElementById('btnAjukan').href = '{{ route("pembiayaan.pengajuan.create") }}?nominal=' + nominal + '&jangka=' + jangka;
    });
    </script>
</x-app-layout>
