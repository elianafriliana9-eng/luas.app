<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Pembiayaan</h2>
            <div class="flex gap-2">
                <a href="{{ route('pembiayaan.simulasi') }}" class="px-3 py-2 bg-blue-100 text-blue-700 text-sm rounded-lg hover:bg-blue-200 transition">📊 Simulasi</a>
                <a href="{{ route('pembiayaan.pengajuan.create') }}" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">+ Pengajuan Baru</a>
            </div>
        </div>
    </x-slot>
    <div class="space-y-4">
        @if(session('success'))<div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-lg shadow-sm"><div class="text-xs text-gray-500 uppercase">Total Pembiayaan Aktif</div><div class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalPinjaman }}</div></div>
            <div class="bg-white p-4 rounded-lg shadow-sm"><div class="text-xs text-gray-500 uppercase">Total Outstanding</div><div class="text-lg font-bold text-indigo-600 font-mono mt-1">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div></div>
            <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-end gap-2">
                @if(auth()->user()->role === 'super_admin')
                <a href="{{ route('pembiayaan.registrasi') }}" class="px-3 py-2 bg-yellow-100 text-yellow-700 text-sm rounded-lg hover:bg-yellow-200 transition">⏳ Registrasi</a>
                @endif
                <a href="{{ route('pembiayaan.laporan.pembiayaan') }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">📄 Laporan</a>
            </div>
        </div>

        <!-- Filter & Table -->
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <form method="GET" action="{{ route('pembiayaan.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pembiayaan atau nama..." class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[120px]"><select name="status" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua Status</option><option value="disetujui" {{ request('status')=='disetujui'?'selected':'' }}>Disetujui</option><option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option><option value="lunas" {{ request('status')=='lunas'?'selected':'' }}>Lunas</option><option value="macet" {{ request('status')=='macet'?'selected':'' }}>Macet</option></select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pembiayaan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Plafon</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sisa Pokok</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tenor</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($pembiayaan as $pem)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-indigo-600 font-medium">{{ $pem->no_pembiayaan }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium">{{ $pem->anggota?->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ $pem->anggota?->no_anggota }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm">Rp {{ number_format($pem->nominal_disetujui, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-sm text-red-600">Rp {{ number_format($pem->saldo_pokok, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-sm">{{ $pem->jangka_bulan }} bln</td>
                            <td class="px-4 py-3 text-center">
                                @if($pem->status === 'aktif')<span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 font-bold">Aktif</span>
                                @elseif($pem->status === 'lunas')<span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 font-bold">Lunas</span>
                                @elseif($pem->status === 'macet')<span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 font-bold">Macet</span>
                                @else<span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 font-bold">{{ ucfirst($pem->status) }}</span>@endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1">
                                    @if(auth()->user()->role === 'super_admin' && $pem->status === 'disetujui')
                                        <a href="{{ route('pembiayaan.pencairan', $pem->id) }}" class="p-1 text-green-600 hover:text-green-900" title="Cairkan">💰</a>
                                    @endif
                                    @if(auth()->user()->role === 'super_admin' && $pem->status === 'aktif')
                                        <a href="{{ route('pembiayaan.pelunasan', $pem->id) }}" class="p-1 text-blue-600 hover:text-blue-900" title="Lunasi">✅</a>
                                    @endif
                                    <a href="{{ route('pembiayaan.cetak.sp3', $pem->id) }}" class="p-1 text-gray-600 hover:text-gray-900" title="Cetak SP3">📄</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data pembiayaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $pembiayaan->links() }}</div>
        </div>
    </div>
</x-app-layout>
