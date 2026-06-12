<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Import Master Data</h2>
                <p class="text-slate-500 text-sm mt-1">Import data dari Template.xlsx (4 sheet: OST, Simpanan, Saldo)</p>
            </div>
            <a href="{{ route('anggota.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        <div class="max-w-3xl space-y-6">
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-secondary/10 border border-secondary/20 text-secondary-dark rounded-xl">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-start gap-3 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl">
                    <span class="material-symbols-outlined mt-0.5">error</span>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="flex items-start gap-3 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl">
                    <span class="material-symbols-outlined mt-0.5">error</span>
                    <ul class="text-sm font-medium space-y-1">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                </div>
            @endif

            <!-- Format Info -->
            <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-amber-500">info</span>
                    Format File Template.xlsx
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 font-data text-xs text-slate-600 space-y-2">
                    <p><strong>Sheet 1: OST</strong> — Data pembiayaan & anggota (header baris 2)</p>
                    <p><strong>Sheet 2: SIMPANAN POKOK DAN WAJIB</strong> — Simpanan pokok & wajib (header baris 3)</p>
                    <p><strong>Sheet 3: SEMUA SIMPANAN</strong> — Semua jenis simpanan (header baris 2)</p>
                    <p><strong>Sheet 4: LIST SALDO ANGGOTA</strong> — Rekap saldo (referensi, tidak diimport)</p>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-full border border-amber-200">Data lama akan DIHAPUS sebelum import</span>
                </div>
            </section>

            <!-- Upload Form -->
            <section class="bg-white rounded-xl shadow-sm p-6">
                <form action="{{ route('anggota.import.master.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">File Template.xlsx <span class="text-danger">*</span></label>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:border-amber-500/50 transition-colors">
                            <span class="material-symbols-outlined text-[40px] text-slate-300 mb-3 block">cloud_upload</span>
                            <input type="file" name="file" accept=".xlsx,.xls" required
                                   class="text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 file:transition-all file:cursor-pointer">
                            <p class="text-xs text-slate-400 mt-2">Format .xlsx — maksimal 10MB</p>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="reset_data" value="1" checked
                                   class="mt-0.5 w-4 h-4 text-red-600 border-red-300 rounded focus:ring-red-500">
                            <div>
                                <span class="text-sm font-semibold text-red-700">Hapus semua data lama sebelum import</span>
                                <p class="text-xs text-red-500 mt-0.5">Data anggota, simpanan, pembiayaan, dan jurnal akan dihapus</p>
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                        <a href="{{ route('anggota.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                        <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-amber-600 text-white text-sm font-semibold rounded-xl shadow-md shadow-amber-600/20 hover:bg-amber-700 transition-all active:scale-95"
                                onclick="return confirm('PERINGATAN: Semua data lama akan dihapus! Lanjutkan?')">
                            <span class="material-symbols-outlined text-[18px]">upload</span>
                            Import & Proses
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
