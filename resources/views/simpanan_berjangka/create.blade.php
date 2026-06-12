<x-app-layout>
    <div class="p-8 max-w-[1200px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Deposito Baru</h2>
                <p class="text-slate-500 text-sm mt-1">Buka simpanan berjangka (deposito) untuk anggota</p>
            </div>
            <a href="{{ route('simpanan-berjangka.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        <section class="bg-white p-8 rounded-xl shadow-sm">
            <form method="POST" class="space-y-6 max-w-2xl">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Anggota</label>
                    <select name="anggota_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary @error('anggota_id') border-danger @enderror" required>
                        <option value="">-- Pilih Anggota --</option>
                        @foreach($anggota as $a)
                            <option value="{{ $a->id }}" {{ old('anggota_id')==$a->id?'selected':'' }}>{{ $a->nama_lengkap }} ({{ $a->no_anggota }})</option>
                        @endforeach
                    </select>
                    @error('anggota_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nominal Deposito</label>
                    <input type="number" name="nominal" value="{{ old('nominal') }}" min="1000000" step="100000"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary @error('nominal') border-danger @enderror" required>
                    <p class="text-xs text-slate-400 mt-1">Minimal Rp 1.000.000</p>
                    @error('nominal') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jangka Waktu</label>
                        <select name="jangka_bulan" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary @error('jangka_bulan') border-danger @enderror" required>
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ old('jangka_bulan')=='1'?'selected':'' }}>1 Bulan</option>
                            <option value="3" {{ old('jangka_bulan')=='3'?'selected':'' }}>3 Bulan</option>
                            <option value="6" {{ old('jangka_bulan')=='6'?'selected':'' }}>6 Bulan</option>
                            <option value="12" {{ old('jangka_bulan')=='12'?'selected':'' }}>12 Bulan</option>
                            <option value="24" {{ old('jangka_bulan')=='24'?'selected':'' }}>24 Bulan</option>
                        </select>
                        @error('jangka_bulan') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Bunga (% p.a.)</label>
                        <input type="number" name="bunga_pa" value="{{ old('bunga_pa', '4') }}" min="0.01" max="20" step="0.01"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary @error('bunga_pa') border-danger @enderror" required>
                        @error('bunga_pa') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="auto_perpanjang" id="auto_perpanjang" value="1" {{ old('auto_perpanjang')?'checked':'' }}
                        class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                    <label for="auto_perpanjang" class="text-sm text-slate-600">Perpanjang otomatis (ARO) saat jatuh tempo</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Simpan</button>
                    <a href="{{ route('simpanan-berjangka.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">Batal</a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
