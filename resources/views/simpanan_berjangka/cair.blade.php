<x-app-layout>
    <div class="p-8 max-w-[1200px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Pencairan Deposito</h2>
                <p class="text-slate-500 text-sm mt-1">{{ $deposito->no_deposito }} — {{ $deposito->anggota?->nama_lengkap }}</p>
            </div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <section class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-primary">
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Nominal Deposito</p>
                <p class="text-xl font-bold text-blue-900 mt-1">Rp {{ number_format($deposito->nominal, 0, ',', '.') }}</p>
            </section>
            <section class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-secondary">
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Estimasi Bunga</p>
                <p class="text-xl font-bold text-secondary mt-1">
                    Rp {{ number_format($deposito->nominal * ($deposito->bunga_pa / 100) * ($deposito->jangka_bulan / 12), 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-400">{{ $deposito->bunga_pa }}% p.a. / {{ $deposito->jangka_bulan }} bln</p>
            </section>
            <section class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-danger">
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Total Dicairkan</p>
                <p class="text-xl font-bold text-danger mt-1">
                    Rp {{ number_format($deposito->nominal + ($deposito->nominal * ($deposito->bunga_pa / 100) * ($deposito->jangka_bulan / 12)), 0, ',', '.') }}
                </p>
            </section>
        </div>

        <section class="bg-white p-8 rounded-xl shadow-sm">
            <form method="POST" class="space-y-6 max-w-xl">
                @csrf

                <div class="bg-tertiary/5 rounded-xl p-4 border border-tertiary/20">
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold">Perhatian:</span> Pencairan akan mentransfer dana
                        (deposito + bunga) ke rekening simpanan sukarela anggota.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="transfer_ke_rekening" id="transfer_ke_rekening" value="1" checked
                        class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                    <label for="transfer_ke_rekening" class="text-sm text-slate-600">Transfer ke rekening simpanan sukarela</label>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">{{ old('keterangan') }}</textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-tertiary text-white text-sm font-semibold rounded-xl hover:bg-tertiary-dark transition-all shadow-sm"
                        onclick="return confirm('Yakin akan mencairkan deposito ini?')">
                        <span class="material-symbols-outlined text-[18px] inline-block mr-1">check</span>
                        Cairkan Sekarang
                    </button>
                    <a href="{{ route('simpanan-berjangka.show', $deposito->id) }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">Batal</a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
