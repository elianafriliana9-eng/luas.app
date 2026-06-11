<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Upload Data Simpanan</h2>
                <p class="text-slate-500 text-sm mt-1">Import transaksi simpanan dari file Excel</p>
            </div>
            <a href="{{ route('simpanan.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        <div class="max-w-2xl space-y-6">

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

            <!-- Excel Format Info -->
            <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-primary">
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">description</span>
                    Format File Excel
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 font-data text-xs text-slate-600 overflow-x-auto">
                    <pre>no_rekening,jenis,nominal,keterangan
REK-SP-ANG-0001,setoran,500000,Setoran bulan Januari
REK-SS-ANG-0002,penarikan,200000,Penarikan tunai</pre>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-secondary/10 text-secondary text-[10px] font-bold rounded-full">setoran</span>
                    <span class="px-2 py-1 bg-danger/10 text-danger text-[10px] font-bold rounded-full">penarikan</span>
                    <span class="px-2 py-1 bg-primary/10 text-primary text-[10px] font-bold rounded-full">pinbuk_masuk</span>
                    <span class="px-2 py-1 bg-tertiary/10 text-tertiary-dark text-[10px] font-bold rounded-full">pinbuk_keluar</span>
                </div>
            </section>

            <!-- Template Download -->
            <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-secondary">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-secondary">download</span>
                            Download Template Excel
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Gunakan template untuk import transaksi via Excel</p>
                    </div>
                    <a href="{{ route('simpanan.download_template') }}" class="flex items-center gap-2 px-4 py-2.5 bg-secondary/10 text-secondary-dark text-sm font-semibold rounded-xl hover:bg-secondary/20 transition-all">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Download Template
                    </a>
                </div>
            </section>

            <!-- Upload Form -->
            <section class="bg-white rounded-xl shadow-sm p-6">
                <form action="{{ route('simpanan.upload.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">File Excel <span class="text-danger">*</span></label>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:border-primary/50 transition-colors">
                            <span class="material-symbols-outlined text-[40px] text-slate-300 mb-3 block">cloud_upload</span>
                            <input type="file" name="file" accept=".xlsx,.xls" required class="text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:transition-all file:cursor-pointer">
                            <p class="text-xs text-slate-400 mt-2">Format .xlsx atau .xls — maksimal 10MB</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                        <a href="{{ route('simpanan.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                        <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 hover:bg-primary-dark transition-all active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">upload</span>
                            Upload & Proses
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
