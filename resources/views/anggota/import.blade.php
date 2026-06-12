<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Import Anggota</h2>
                <p class="text-slate-500 text-sm mt-1">Import data anggota baru dari file Excel</p>
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

            @if(session('import_hasil'))
                @php
                    $berhasilCount = count(array_filter(session('import_hasil'), fn($h) => $h['status'] === 'berhasil'));
                    $gagalCount = count(array_filter(session('import_hasil'), fn($h) => $h['status'] === 'gagal'));
                @endphp

                <section class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold font-heading text-slate-700 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-primary">summarize</span>
                            Hasil Import
                        </h3>
                        <div class="flex items-center gap-3 text-xs font-semibold">
                            <span class="flex items-center gap-1 text-success"><span class="w-2 h-2 rounded-full bg-success"></span> Berhasil {{ $berhasilCount }}</span>
                            <span class="flex items-center gap-1 text-danger"><span class="w-2 h-2 rounded-full bg-danger"></span> Gagal {{ $gagalCount }}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
                                    <th class="text-left px-4 py-3">#</th>
                                    <th class="text-left px-4 py-3">NIK</th>
                                    <th class="text-left px-4 py-3">Nama</th>
                                    <th class="text-center px-4 py-3">Status</th>
                                    <th class="text-left px-4 py-3">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach(session('import_hasil') as $h)
                                <tr class="{{ $h['status'] === 'berhasil' ? 'text-slate-600' : 'text-danger' }}">
                                    <td class="px-4 py-2.5 font-mono text-slate-400">{{ $h['baris'] }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $h['nik'] }}</td>
                                    <td class="px-4 py-2.5">{{ $h['nama'] }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($h['status'] === 'berhasil')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-success/10 text-success text-[10px] font-bold rounded-full">
                                                <span class="material-symbols-outlined text-[12px]">check</span>
                                                Berhasil
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-danger/10 text-danger text-[10px] font-bold rounded-full">
                                                <span class="material-symbols-outlined text-[12px]">close</span>
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5">{{ $h['pesan'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <!-- Format Info -->
            <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-primary">
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">description</span>
                    Format File Excel
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 font-data text-xs text-slate-600 overflow-x-auto">
                    <pre>NIK,Nama Lengkap,Kode Cabang,Tempat Lahir,Tanggal Lahir,Jenis Kelamin,Alamat,No. HP,Email,Departemen,Jabatan,Tanggal Mulai Kerja,No. Pegawai
3171012304900001,Budi Santoso,CBG-JKT,Jakarta,1990-04-23,L,Jl. Merdeka No.1,081234567890,budi@email.com,Produksi,Supervisor,2020-06-01,PEG-001</pre>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-secondary/10 text-secondary text-[10px] font-bold rounded-full">NIK * wajib</span>
                    <span class="px-2 py-1 bg-secondary/10 text-secondary text-[10px] font-bold rounded-full">Nama Lengkap * wajib</span>
                    <span class="px-2 py-1 bg-secondary/10 text-secondary text-[10px] font-bold rounded-full">Kode Cabang * wajib</span>
                    <span class="px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-full">lainnya opsional</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('anggota.download_template') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary text-sm font-semibold rounded-xl hover:bg-primary/20 transition-all">
                        <span class="material-symbols-outlined text-[18px]">file_download</span>
                        Download Template Excel
                    </a>
                </div>
            </section>

            <!-- Upload Form -->
            <section class="bg-white rounded-xl shadow-sm p-6">
                <form action="{{ route('anggota.import.process') }}" method="POST" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">File Excel <span class="text-danger">*</span></label>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:border-primary/50 transition-colors">
                            <span class="material-symbols-outlined text-[40px] text-slate-300 mb-3 block">cloud_upload</span>
                            <input type="file" name="file" accept=".xlsx,.xls" required
                                   class="text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:transition-all file:cursor-pointer">
                            <p class="text-xs text-slate-400 mt-2">Format .xlsx atau .xls — maksimal 10MB</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                        <a href="{{ route('anggota.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                        <button type="submit" :disabled="loading" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 hover:bg-primary-dark transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="loading" class="material-symbols-outlined text-[18px] animate-spin">sync</span>
                            <span x-show="!loading" class="material-symbols-outlined text-[18px]">upload</span>
                            <span x-text="loading ? 'Mengimport...' : 'Import & Proses'"></span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
