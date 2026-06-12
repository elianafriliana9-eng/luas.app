<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Pemindahbukuan (Pinbuk)</h2>
                <p class="text-slate-500 text-sm mt-1">Transfer saldo antar rekening simpanan</p>
            </div>
            <a href="{{ route('simpanan.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
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

            <!-- Info -->
            <div class="flex items-start gap-3 p-4 bg-primary/5 border border-primary/10 rounded-xl">
                <span class="material-symbols-outlined text-primary mt-0.5">info</span>
                <p class="text-sm text-slate-600"><strong class="text-primary">Pinbuk</strong> = Pemindahbukuan. Memindahkan saldo dari satu rekening ke rekening lain (bisa beda anggota). Nominal &gt; Rp 1.000.000 memerlukan approval.</p>
            </div>

            <!-- Form -->
            <section class="bg-white rounded-xl shadow-sm p-6">
                <form action="{{ route('simpanan.pinbuk.store') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    <div class="space-y-5">
                        <div x-data="{ search: '' }">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Rekening Sumber (Kurang) <span class="text-danger">*</span></label>
                            <input type="text" x-model="search" placeholder="Cari anggota atau no. rekening..." class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm mb-2 focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <select name="rekening_sumber_id" required class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary @error('rekening_sumber_id') border-red-500 @enderror" size="8">
                                <option value="">-- Pilih Rekening Sumber --</option>
                                @foreach($anggota as $a)
                                    @foreach($a->rekeningSimpanan->where('status', 'aktif') as $rek)
                                        @php $searchText = strtolower($a->nama_lengkap . ' ' . $rek->no_rekening . ' ' . $rek->produk?->nama); @endphp
                                        <option value="{{ $rek->id }}" data-search="{{ $searchText }}" x-show="!search || '{{ $searchText }}'.includes(search.toLowerCase())" x-transition>{{ $a->nama_lengkap }} -- {{ $rek->produk?->nama }} ({{ $rek->no_rekening }}, Saldo: Rp {{ number_format($rek->saldo, 0, ',', '.') }})</option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('rekening_sumber_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-center py-2">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">arrow_downward</span>
                            </div>
                        </div>

                        <div x-data="{ search: '' }">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Rekening Tujuan (Tambah) <span class="text-danger">*</span></label>
                            <input type="text" x-model="search" placeholder="Cari anggota atau no. rekening..." class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm mb-2 focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <select name="rekening_tujuan_id" required class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary @error('rekening_tujuan_id') border-red-500 @enderror" size="8">
                                <option value="">-- Pilih Rekening Tujuan --</option>
                                @foreach($anggota as $a)
                                    @foreach($a->rekeningSimpanan->where('status', 'aktif') as $rek)
                                        @php $searchText = strtolower($a->nama_lengkap . ' ' . $rek->no_rekening . ' ' . $rek->produk?->nama); @endphp
                                        <option value="{{ $rek->id }}" data-search="{{ $searchText }}" x-show="!search || '{{ $searchText }}'.includes(search.toLowerCase())" x-transition>{{ $a->nama_lengkap }} -- {{ $rek->produk?->nama }} ({{ $rek->no_rekening }})</option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('rekening_tujuan_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nominal <span class="text-danger">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                <input type="text" name="nominal" required inputmode="numeric" class="input-rupiah w-full border border-slate-200 rounded-xl pl-12 pr-4 py-3 text-lg font-data font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary @error('nominal') border-red-500 @enderror" placeholder="0" oninput="formatRupiah(this)">
                            </div>
                            @error('nominal') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Keterangan</label>
                            <input type="text" name="keterangan" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Pemindahbukuan">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                        <a href="{{ route('simpanan.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                        <button type="submit" :disabled="loading" onclick="return confirm('Yakin ingin memproses pemindahbukuan ini?')" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 hover:bg-primary-dark transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="loading" class="material-symbols-outlined text-[18px] animate-spin">sync</span>
                            <span x-show="!loading" class="material-symbols-outlined text-[18px]">compare_arrows</span>
                            <span x-text="loading ? 'Memproses...' : 'Proses Pinbuk'"></span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
