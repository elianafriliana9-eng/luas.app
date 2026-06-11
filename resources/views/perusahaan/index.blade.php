<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Data Perusahaan (PT)') }}</h2>
            <a href="{{ route('perusahaan.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Perusahaan
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Kode</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Nama Perusahaan</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Telp</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Email</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600">Status</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($perusahaan as $p)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-mono text-xs font-medium text-slate-500">{{ $p->kode }}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $p->nama }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $p->telp ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $p->email ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($p->aktif)
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('perusahaan.edit', $p->id) }}" class="px-3 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-xs font-medium transition">Edit</a>
                                        <form method="POST" action="{{ route('perusahaan.destroy', $p->id) }}" onsubmit="return confirm('Yakin ingin menghapus perusahaan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-medium transition">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada data perusahaan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($perusahaan->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $perusahaan->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
