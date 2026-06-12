<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Laporan Profil Anggota</h2>
            <div class="flex gap-2 no-print">
                <a href="{{ route('anggota.export.profil', request()->query()) }}"
                   class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
                <a href="{{ route('anggota.pdf.profil', request()->query()) }}" target="_blank"
                   class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v4a1 1 0 001 1h4"/></svg>
                    PDF
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak
                </button>
            </div>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4 flex flex-wrap gap-3 items-end no-print">
            <form method="GET" action="{{ route('anggota.laporan.profil') }}" class="flex-1 flex flex-wrap gap-3 items-end">
                <div class="min-w-[150px]"><label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua</option><option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option><option value="keluar" {{ request('status')=='keluar'?'selected':'' }}>Keluar</option></select></div>
                <div class="min-w-[150px]"><label class="block text-xs font-medium text-gray-500 mb-1">Perusahaan</label>
                    <select name="perusahaan_id" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua</option>@foreach($perusahaans as $p) <option value="{{ $p->id }}" {{ request('perusahaan_id')==$p->id?'selected':'' }}>{{ $p->nama }}</option> @endforeach</select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Anggota</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. HP</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perusahaan</th>
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($anggota as $i => $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm">{{ $i + 1 }}</td>
                            <td class="px-3 py-2 text-sm font-mono">{{ $a->no_anggota }}</td>
                            <td class="px-3 py-2 text-sm font-medium">{{ $a->nama_lengkap }}</td>
                            <td class="px-3 py-2 text-sm font-mono">{{ $a->nik }}</td>
                            <td class="px-3 py-2 text-sm">{{ $a->no_hp }}</td>
                            <td class="px-3 py-2 text-sm">{{ $a->perusahaan?->nama ?? '-' }}</td>
                            <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 text-xs rounded-full {{ $a->status==='aktif'?'bg-green-100 text-green-800':'bg-red-100 text-red-800' }}">{{ $a->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><span class="material-symbols-outlined text-[40px] block mb-2">database_off</span>Tidak ada data anggota</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>
