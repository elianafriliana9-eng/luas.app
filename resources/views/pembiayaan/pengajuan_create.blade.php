<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Input Pengajuan Pembiayaan</h2><a href="{{ route('pembiayaan.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div>
    </x-slot>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"><ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('pembiayaan.pengajuan.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Anggota <span class="text-red-500">*</span></label>
                        <select name="anggota_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih Anggota --</option>
                            @foreach($anggotaList as $a)<option value="{{ $a->id }}">{{ $a->nama_lengkap }} ({{ $a->no_anggota }})</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produk Pembiayaan <span class="text-red-500">*</span></label>
                        <select name="produk_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produkList as $p)<option value="{{ $p->id }}">{{ $p->nama }} (Bunga {{ $p->bunga_pa }}%)</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Diajukan <span class="text-red-500">*</span></label>
                        <input type="number" name="nominal_diajukan" required min="0" step="100000" class="w-full border rounded-lg px-3 py-2 text-sm font-mono" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jangka Waktu (Bulan) <span class="text-red-500">*</span></label>
                        <input type="number" name="jangka_bulan" required min="1" max="60" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="12">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan <span class="text-red-500">*</span></label>
                        <select name="tujuan" required class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih Tujuan --</option>
                            <option value="modal_kerja">Modal Kerja</option>
                            <option value="konsumtif">Konsumtif</option>
                            <option value="investasi">Investasi</option>
                            <option value="pendidikan">Pendidikan</option>
                            <option value="kesehatan">Kesehatan</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="auto_potong_gaji" id="autoPotong" value="1" class="rounded">
                        <label for="autoPotong" class="text-sm text-gray-700">Auto Potong Gaji</label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="catatan" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('pembiayaan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Ajukan Pembiayaan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
