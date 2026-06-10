<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Pelunasan Pembiayaan</h2><a href="{{ route('pembiayaan.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div>
    </x-slot>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800"><strong>Pembiayaan:</strong> {{ $pembiayaan->no_pembiayaan }}</p>
                <p class="text-sm text-blue-800"><strong>Anggota:</strong> {{ $pembiayaan->anggota?->nama_lengkap }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg text-center"><div class="text-xs text-gray-500 uppercase">Sisa Pokok</div><div class="text-lg font-bold font-mono text-red-600">Rp {{ number_format($sisaPokok, 0, ',', '.') }}</div></div>
                <div class="bg-gray-50 p-4 rounded-lg text-center"><div class="text-xs text-gray-500 uppercase">Sisa Bunga</div><div class="text-lg font-bold font-mono text-orange-600">Rp {{ number_format($sisaBunga, 0, ',', '.') }}</div></div>
                <div class="bg-indigo-50 p-4 rounded-lg text-center"><div class="text-xs text-indigo-500 uppercase">Total Pelunasan</div><div class="text-lg font-bold font-mono text-indigo-600">Rp {{ number_format($totalPelunasan, 0, ',', '.') }}</div></div>
            </div>
            @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"><ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('pembiayaan.pelunasan.submit', $pembiayaan->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Bayar <span class="text-red-500">*</span></label>
                    <input type="number" name="nominal_bayar" value="{{ $totalPelunasan }}" required min="0" step="1000" class="w-full border rounded-lg px-3 py-2 text-sm font-mono text-lg">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bayar</label><input type="date" name="tanggal_bayar" value="{{ now()->format('Y-m-d') }}" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metode Bayar</label>
                        <select name="metode_bayar" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="transfer">Transfer</option><option value="tunai">Tunai</option><option value="potong_gaji">Potong Gaji</option></select>
                    </div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label><textarea name="catatan" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea></div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('pembiayaan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                    <button type="submit" onclick="return confirm('Yakin melunasi pembiayaan ini?')" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">✅ Proses Pelunasan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
