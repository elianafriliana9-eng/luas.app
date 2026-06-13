<x-app-layout>
    <x-slot name="header">
        <nav class="flex items-center gap-1 text-xs text-gray-500 mb-1">
            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
            <span class="material-symbols-outlined text-[12px]">chevron_right</span>
            <span class="text-indigo-600 font-medium">Anggota</span>
        </nav>
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Data Anggota') }}</h2>
            <div class="flex gap-2">

                @if(auth()->user()->role === 'super_admin')
                <a href="{{ route('anggota.approval_keluar') }}" class="inline-flex items-center px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Approval Keluar
                    @php $pendingKeluar = Cache::remember('badge.pending_keluar', 300, fn() => \App\Models\Anggota::where('status', 'pengajuan_keluar')->count()); @endphp
                    @if($pendingKeluar > 0)
                        <span class="ml-1 px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full">{{ $pendingKeluar }}</span>
                    @endif
                </a>
                @endif
                <a href="{{ route('anggota.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Anggota
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>
            @endif

            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-t-xl shadow-sm flex flex-wrap gap-3">
                <form method="GET" action="{{ route('anggota.index') }}" class="flex-1 flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pencarian</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, no. anggota..."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="pending_aktif" {{ request('status') == 'pending_aktif' ? 'selected' : '' }}>Pending Aktif</option>
                            <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="pengajuan_keluar" {{ request('status') == 'pengajuan_keluar' ? 'selected' : '' }}>Pengajuan Keluar</option>
                            <option value="keluar" {{ request('status') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                        </select>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cabang</label>
                        <select name="cabang_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Cabang</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Perusahaan</label>
                        <select name="perusahaan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua</option>
                            @foreach($perusahaans as $p)
                                <option value="{{ $p->id }}" {{ request('perusahaan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
                        <a href="{{ route('anggota.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-x-auto rounded-b-xl shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Anggota</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pegawai</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perusahaan</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($anggota as $a)
                            <tr class="hover:bg-indigo-50 transition">
                                <td class="px-4 py-3 text-sm font-mono text-indigo-600 font-medium">
                                    <a href="{{ route('anggota.show', $a->id) }}">{{ $a->no_anggota }}</a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($a->nama_lengkap) }}&background=E0E7FF&color=4F46E5&size=32" class="w-8 h-8 rounded-full">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $a->nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ $a->cabang?->nama }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $a->no_pegawai ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $a->perusahaan?->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($a->status === 'aktif')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">Aktif</span>
                                    @elseif($a->status === 'pengajuan_keluar')
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">Pengajuan Keluar</span>
                                    @elseif($a->status === 'keluar')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-medium">Keluar</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">{{ ucfirst($a->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ route('anggota.show', $a->id) }}" class="p-1 text-indigo-600 hover:text-indigo-900" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('anggota.edit', $a->id) }}" class="p-1 text-blue-600 hover:text-blue-900" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if($a->status === 'aktif')
                                            <a href="{{ route('anggota.keluar', $a->id) }}" class="p-1 text-orange-600 hover:text-orange-900" title="Ajukan Keluar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data anggota.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-gray-200">{{ $anggota->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
