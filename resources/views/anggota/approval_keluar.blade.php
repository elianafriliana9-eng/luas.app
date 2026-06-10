<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Approval Anggota Keluar</h2>
            <a href="{{ route('anggota.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Anggota</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Keluar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($pending as $a)
                        <tr class="hover:bg-yellow-50">
                            <td class="px-6 py-4 text-sm font-mono text-indigo-600">{{ $a->no_anggota }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $a->nama_lengkap }}</td>
                            <td class="px-6 py-4 text-sm">{{ $a->tanggal_keluar?->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $a->alasan_keluar ?? '-' }}</td>
                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                <form action="{{ route('anggota.approve_keluar', $a->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Approve anggota keluar?')" class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700">Approve</button>
                                </form>
                                <form action="{{ route('anggota.reject_keluar', $a->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Tolak pengajuan keluar?')" class="px-3 py-1 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada pengajuan keluar yang pending.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>
