<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Approval Pemindahbukuan</h2>
                <p class="text-slate-500 text-sm mt-1">Pinbuk yang membutuhkan persetujuan (nominal &gt; Rp 1.000.000)</p>
            </div>
            <a href="{{ route('simpanan.pinbuk') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-secondary/10 border border-secondary/20 text-secondary-dark rounded-xl">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl">
                <span class="material-symbols-outlined">error</span>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Pending Count -->
        @if($pending->count() > 0)
            <div class="flex items-center gap-3 p-4 bg-tertiary/10 border border-tertiary/20 rounded-xl">
                <span class="material-symbols-outlined text-tertiary-dark">schedule</span>
                <span class="text-sm font-medium text-tertiary-dark"><strong>{{ $pending->total() }}</strong> pinbuk menunggu persetujuan</span>
            </div>
        @endif

        <!-- Table -->
        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">No. Transaksi</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Dari</th>
                            <th class="px-6 py-4">Ke</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($pending as $pinbuk)
                            <tr class="hover:bg-tertiary/5 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-data text-sm font-semibold text-primary">{{ $pinbuk->no_transaksi }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $pinbuk->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-danger/10 flex items-center justify-center text-danger font-bold text-xs">
                                            {{ strtoupper(substr($pinbuk->rekeningSumber?->anggota?->nama_lengkap ?? '?', 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold text-blue-900 block">{{ $pinbuk->rekeningSumber?->anggota?->nama_lengkap }}</span>
                                            <span class="text-[11px] font-data text-slate-400">{{ $pinbuk->rekeningSumber?->no_rekening }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-secondary font-bold text-xs">
                                            {{ strtoupper(substr($pinbuk->rekeningTujuan?->anggota?->nama_lengkap ?? '?', 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold text-blue-900 block">{{ $pinbuk->rekeningTujuan?->anggota?->nama_lengkap }}</span>
                                            <span class="text-[11px] font-data text-slate-400">{{ $pinbuk->rekeningTujuan?->no_rekening }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-data font-bold text-sm text-danger">Rp {{ number_format($pinbuk->nominal, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 max-w-[200px] truncate">{{ $pinbuk->keterangan }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <form action="{{ route('simpanan.pinbuk.approve', $pinbuk->id) }}" method="POST" class="inline" x-data="{ loading: false }" @submit="loading = true">
                                            @csrf
                                            <button type="submit" :disabled="loading" onclick="return confirm('Setujui pinbuk ini?')" class="flex items-center gap-1.5 px-3.5 py-2 bg-secondary text-white text-xs font-bold rounded-lg hover:bg-secondary-dark transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span x-show="loading" class="material-symbols-outlined text-[14px] animate-spin">sync</span>
                                                <span x-show="!loading" class="material-symbols-outlined text-[14px]">check</span>
                                                <span x-text="loading ? '...' : 'Setujui'"></span>
                                            </button>
                                        </form>
                                        <form action="{{ route('simpanan.pinbuk.reject', $pinbuk->id) }}" method="POST" class="inline" x-data="{ loading: false }" @submit="loading = true">
                                            @csrf
                                            <button type="submit" :disabled="loading" onclick="return confirm('Tolak pinbuk ini? Saldo akan dikembalikan ke rekening sumber.')" class="flex items-center gap-1.5 px-3.5 py-2 bg-danger text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span x-show="loading" class="material-symbols-outlined text-[14px] animate-spin">sync</span>
                                                <span x-show="!loading" class="material-symbols-outlined text-[14px]">close</span>
                                                <span x-text="loading ? '...' : 'Tolak'"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <span class="material-symbols-outlined text-[48px] text-secondary/30 mb-3 block">task_alt</span>
                                    <p class="text-sm text-slate-400 font-medium">Semua pinbuk sudah diproses</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if($pending->hasPages())
            <div class="px-1">
                {{ $pending->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
