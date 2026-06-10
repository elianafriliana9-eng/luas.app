<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Pengajuan Keluar — {{ $anggota->nama_lengkap }}</h2>
            <a href="{{ route('anggota.show', $anggota->id) }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <p class="text-sm text-orange-800"><strong>⚠️ Perhatian:</strong> Anggota yang keluar akan melalui proses approval. Pastikan tidak ada tunggakan pembiayaan aktif.</p>
                </div>
                <form action="{{ route('anggota.keluar.submit', $anggota->id) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Keluar <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_keluar" required min="{{ now()->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Keluar <span class="text-red-500">*</span></label>
                            <textarea name="alasan_keluar" rows="4" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Jelaskan alasan anggota keluar..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                        <a href="{{ route('anggota.show', $anggota->id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition">Ajukan Keluar</button>
                    </div>
                </form>
            </div>
        </div>
    </div></div>
</x-app-layout>
