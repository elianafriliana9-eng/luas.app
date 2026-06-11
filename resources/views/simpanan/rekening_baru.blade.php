<x-app-layout>
    <div class="p-8 max-w-[900px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Buka Rekening Baru</h2>
                <p class="text-slate-500 text-sm mt-1">Membuka rekening simpanan untuk anggota yang sudah terdaftar</p>
            </div>
            <a href="{{ route('simpanan.rekening') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        @if($errors->any())
            <div class="flex items-start gap-3 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl">
                <span class="material-symbols-outlined mt-0.5">error</span>
                <ul class="text-sm font-medium space-y-1">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <section class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('simpanan.rekening_baru.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Anggota <span class="text-danger">*</span></label>
                    <select name="anggota_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">-- Pilih Anggota --</option>
                        @foreach($anggota as $a)
                            <option value="{{ $a->id }}" {{ old('anggota_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->no_anggota }} — {{ $a->nama_lengkap }} {{ $a->departemen ? '(' . $a->departemen . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jenis Simpanan <span class="text-danger">*</span></label>
                    <select name="produk_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">-- Pilih Jenis Simpanan --</option>
                        @foreach($produk as $p)
                            <option value="{{ $p->id }}" {{ old('produk_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} ({{ ucfirst($p->jenis) }}){{ $p->minimal_saldo ? ' — Min. Saldo: Rp ' . number_format($p->minimal_saldo, 0, ',', '.') : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
                    <a href="{{ route('simpanan.rekening') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                    <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 hover:bg-primary-dark transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        Buka Rekening
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
