<x-app-layout>
    <div class="p-8 max-w-[1200px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Detail Deposito</h2>
                <p class="text-slate-500 text-sm mt-1">{{ $deposito->no_deposito }}</p>
            </div>
            <a href="{{ route('simpanan-berjangka.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <section class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-primary">
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Anggota</p>
                <p class="text-lg font-bold text-blue-900 mt-1">{{ $deposito->anggota?->nama_lengkap }}</p>
                <p class="text-sm text-slate-500">{{ $deposito->anggota?->no_anggota }}</p>
            </section>
            <section class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-secondary">
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Nominal</p>
                <p class="text-lg font-bold text-secondary mt-1">Rp {{ number_format($deposito->nominal, 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">{{ $deposito->jangka_bulan }} bulan &middot; {{ $deposito->bunga_pa }}% p.a.</p>
            </section>
            <section class="bg-white rounded-xl p-6 shadow-sm border-l-4 {{ $deposito->status === 'aktif' ? 'border-secondary' : ($deposito->status === 'jatuh_tempo' ? 'border-tertiary' : 'border-danger') }}">
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Status</p>
                <p class="text-lg font-bold text-blue-900 mt-1 capitalize">{{ $deposito->status }}</p>
                <p class="text-sm text-slate-500">Jatuh tempo: {{ $deposito->tanggal_jatuh_tempo?->format('d M Y') }}</p>
            </section>
        </div>

        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-bold text-blue-900">Informasi Lengkap</h3>
            </div>
            <div class="p-6 grid grid-cols-2 gap-6 text-sm">
                <div><span class="text-slate-400">No. Deposito</span><p class="font-semibold text-blue-900 font-data">{{ $deposito->no_deposito }}</p></div>
                <div><span class="text-slate-400">Tanggal Mulai</span><p class="font-semibold text-blue-900">{{ $deposito->tanggal_mulai?->format('d M Y') }}</p></div>
                <div><span class="text-slate-400">Tanggal Jatuh Tempo</span><p class="font-semibold text-blue-900">{{ $deposito->tanggal_jatuh_tempo?->format('d M Y') }}</p></div>
                <div><span class="text-slate-400">Auto Perpanjang</span><p class="font-semibold text-blue-900">{{ $deposito->auto_perpanjang ? 'Ya' : 'Tidak' }}</p></div>
                <div><span class="text-slate-400">Bunga Akrual</span><p class="font-semibold text-blue-900">Rp {{ number_format($deposito->bunga_akrual, 0, ',', '.') }}</p></div>
                <div><span class="text-slate-400">Estimasi Bunga Jatuh Tempo</span>
                    <p class="font-semibold text-blue-900">
                        Rp {{ number_format($deposito->nominal * ($deposito->bunga_pa / 100) * ($deposito->jangka_bulan / 12), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </section>

        @if(in_array($deposito->status, ['jatuh_tempo', 'aktif']))
            <div class="flex gap-3">
                <a href="{{ route('simpanan-berjangka.cair', $deposito->id) }}" class="px-6 py-2.5 bg-tertiary text-white text-sm font-semibold rounded-xl hover:bg-tertiary-dark transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px] inline-block mr-1">payments</span>
                    Cairkan Deposito
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
