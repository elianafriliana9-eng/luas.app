<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">
                    {{ $jenis === 'setoran' ? 'Setoran Simpanan' : 'Penarikan Simpanan' }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">
                    {{ $jenis === 'setoran' ? 'Input setoran tunai ke rekening anggota' : 'Proses penarikan dana dari rekening anggota' }}
                </p>
            </div>
            <a href="{{ route('simpanan.transaksi') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        <!-- Jenis Toggle -->
        <section class="bg-white rounded-xl shadow-sm p-1.5 inline-flex gap-1">
            <a href="{{ route('simpanan.create', ['jenis' => 'setoran', 'anggota' => request('anggota')]) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all {{ $jenis === 'setoran' ? 'bg-secondary text-white shadow-md shadow-secondary/20' : 'text-slate-500 hover:bg-slate-50' }}">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Setoran
            </a>
            <a href="{{ route('simpanan.create', ['jenis' => 'penarikan', 'anggota' => request('anggota')]) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all {{ $jenis === 'penarikan' ? 'bg-danger text-white shadow-md shadow-danger/20' : 'text-slate-500 hover:bg-slate-50' }}">
                <span class="material-symbols-outlined text-[18px]">remove_circle</span>
                Penarikan
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

            <!-- Search Anggota -->
            <section class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">person_search</span>
                    Cari Anggota
                </h3>
                <form action="{{ route('simpanan.create') }}" method="GET" class="flex gap-2">
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <div class="flex-1 relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        <input type="text" name="anggota" value="{{ request('anggota') }}" placeholder="Ketik nama, no. anggota, atau NIK..." class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Cari</button>
                    <a href="{{ route('simpanan.create', ['jenis' => $jenis]) }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Reset</a>
                </form>
            </section>

            {{-- Search Results: multiple matches --}}
            @if(isset($anggotaResults) && $anggotaResults->count() > 0)
                <section class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">group</span>
                        Pilih Anggota ({{ $anggotaResults->count() }} hasil)
                    </h3>
                    <div class="space-y-2">
                        @foreach($anggotaResults as $result)
                            <a href="{{ route('simpanan.create', ['jenis' => $jenis, 'anggota' => $result->id]) }}"
                               class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-primary/30 hover:bg-primary/5 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-sm group-hover:bg-primary group-hover:text-white transition-colors">
                                    {{ strtoupper(substr($result->nama_lengkap, 0, 2)) }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-blue-900 text-sm">{{ $result->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-400 font-data">{{ $result->no_anggota }} &middot; {{ $result->nik }} &middot; {{ $result->departemen ?? '-' }}</p>
                                </div>
                                <span class="material-symbols-outlined text-slate-300 group-hover:text-primary transition-colors">arrow_forward</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($anggota)
                <!-- Anggota Info Card -->
                <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-primary">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
                            {{ strtoupper(substr($anggota->nama_lengkap, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-blue-900 font-headline">{{ $anggota->nama_lengkap }}</p>
                            <p class="text-sm text-slate-500 font-data">{{ $anggota->no_anggota }} &middot; {{ $anggota->departemen ?? '-' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Transaction Form -->
                <section class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-700 mb-5 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">edit_note</span>
                        Detail Transaksi
                    </h3>
                    <form action="{{ route('simpanan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="anggota_id" value="{{ $anggota->id }}">
                        <input type="hidden" name="jenis" value="{{ $jenis }}">

                        <div class="space-y-5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pilih Rekening <span class="text-danger">*</span></label>
                                <select name="rekening_id" required class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    <option value="">-- Pilih Rekening --</option>
                                    @foreach($rekeningList as $rek)
                                        <option value="{{ $rek->id }}" {{ old('rekening_id') == $rek->id ? 'selected' : '' }}>
                                            {{ $rek->produk?->nama }} -- {{ $rek->no_rekening }} (Saldo: Rp {{ number_format($rek->saldo, 0, ',', '.') }})
                                            @if($rek->status !== 'aktif') -- [{{ strtoupper($rek->status) }}]@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nominal <span class="text-danger">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                    <input type="number" name="nominal" value="{{ old('nominal') }}" required min="1000" step="1000" class="w-full border border-slate-200 rounded-xl pl-12 pr-4 py-3 text-lg font-data font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="0">
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1.5">Minimal Rp 1.000</p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Keterangan</label>
                                <input type="text" name="keterangan" value="{{ old('keterangan') }}" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="{{ $jenis === 'setoran' ? 'Setoran tunai' : 'Penarikan tunai' }}">
                            </div>
                        </div>

                        @if($jenis === 'penarikan')
                            <div class="mt-5 flex items-start gap-3 p-4 bg-tertiary/10 border border-tertiary/20 rounded-xl">
                                <span class="material-symbols-outlined text-tertiary-dark mt-0.5">info</span>
                                <p class="text-sm text-tertiary-dark">Penarikan di atas <strong>Rp 1.000.000</strong> membutuhkan approval dari atasan.</p>
                            </div>
                        @endif

                        <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                            <a href="{{ route('simpanan.transaksi') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                            <button type="submit" class="flex items-center gap-2 px-6 py-2.5 {{ $jenis === 'setoran' ? 'bg-secondary shadow-secondary/20' : 'bg-danger shadow-danger/20' }} text-white text-sm font-semibold rounded-xl shadow-md hover:opacity-90 transition-all active:scale-95">
                                <span class="material-symbols-outlined text-[18px]">{{ $jenis === 'setoran' ? 'savings' : 'money_off' }}</span>
                                {{ $jenis === 'setoran' ? 'Proses Setoran' : 'Proses Penarikan' }}
                            </button>
                        </div>
                    </form>
                </section>
            @else
                <!-- Empty State -->
                <section class="bg-white rounded-xl shadow-sm p-16 text-center">
                    <span class="material-symbols-outlined text-[64px] text-slate-200 mb-4 block">person_search</span>
                    <p class="text-slate-400 font-medium">Cari anggota terlebih dahulu untuk memulai transaksi</p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
