<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Pencairan Pembiayaan</h2><a href="{{ route('pembiayaan.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div>
    </x-slot>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800"><strong>Pembiayaan:</strong> {{ $pembiayaan->no_pembiayaan }}</p>
                <p class="text-sm text-green-800"><strong>Anggota:</strong> {{ $pembiayaan->anggota?->nama_lengkap }} ({{ $pembiayaan->anggota?->no_anggota }})</p>
                <p class="text-sm text-green-800"><strong>Nominal Disetujui:</strong> Rp {{ number_format($pembiayaan->nominal_disetujui, 0, ',', '.') }}</p>
            </div>
            @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"><ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('pembiayaan.pencairan.submit', $pembiayaan->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Cair <span class="text-red-500">*</span></label>
                    <input type="number" name="nominal_cair" value="{{ $pembiayaan->nominal_disetujui }}" required min="0" step="100000" class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Cair <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_cair" value="{{ now()->format('Y-m-d') }}" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pencairan <span class="text-red-500">*</span></label>
                        <select name="metode_cair" required class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="transfer">Transfer</option>
                            <option value="tunai">Tunai</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Catatan pencairan..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('pembiayaan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">💰 Proses Pencairan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
