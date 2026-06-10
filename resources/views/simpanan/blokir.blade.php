<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Blokir Rekening</h2>
                <p class="text-slate-500 text-sm mt-1">Blokir sementara rekening simpanan anggota</p>
            </div>
            <a href="{{ route('simpanan.rekening') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        <div class="max-w-2xl space-y-6">

            @if($errors->any())
                <div class="flex items-start gap-3 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl">
                    <span class="material-symbols-outlined mt-0.5">error</span>
                    <ul class="text-sm font-medium space-y-1">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                </div>
            @endif
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-secondary/10 border border-secondary/20 text-secondary-dark rounded-xl">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Rekening Info -->
            <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-tertiary">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-tertiary/10 rounded-xl">
                        <span class="material-symbols-outlined text-tertiary-dark text-[24px]">lock</span>
                    </div>
                    <div>
                        <p class="font-bold text-blue-900 font-headline">{{ $rekening->anggota?->nama_lengkap }}</p>
                        <p class="text-sm text-slate-500 font-data mt-1">{{ $rekening->no_rekening }} &middot; {{ $rekening->produk?->nama }}</p>
                        <p class="text-sm text-slate-600 mt-2">Saldo saat ini: <strong class="font-data text-blue-900">Rp {{ number_format($rekening->saldo, 0, ',', '.') }}</strong></p>
                    </div>
                </div>
            </section>

            <!-- Form -->
            <section class="bg-white rounded-xl shadow-sm p-6">
                <form action="{{ route('simpanan.blokir.submit', $rekening->id) }}" method="POST">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alasan Pemblokiran <span class="text-danger">*</span></label>
                        <textarea name="alasan" rows="3" required class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Jelaskan alasan pemblokiran..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                        <a href="{{ route('simpanan.rekening') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                        <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-tertiary-dark text-white text-sm font-semibold rounded-xl shadow-md shadow-tertiary/20 hover:opacity-90 transition-all active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">lock</span>
                            Blokir Rekening
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
