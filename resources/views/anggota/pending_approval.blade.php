<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Approval Anggota Baru') }}</h2>
            <a href="{{ route('anggota.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b text-sm text-gray-600">
                    Anggota yang menunggu persetujuan: <strong>{{ $pending->count() }}</strong>
                </div>

                @if($pending->isEmpty())
                    <div class="p-8 text-center text-gray-500">Tidak ada anggota yang menunggu approval.</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cabang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Didaftarkan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pending as $index => $a)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-mono">{{ $a->nik }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <a href="{{ route('anggota.show', $a->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ $a->nama_lengkap }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $a->cabang->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-2">
                                            <form action="{{ route('anggota.approve_anggota', $a->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition" onclick="return confirm('Setujui anggota ini?')">Setujui</button>
                                            </form>
                                            <form action="{{ route('anggota.reject_anggota', $a->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition" onclick="return confirm('Tolak anggota ini?')">Tolak</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
