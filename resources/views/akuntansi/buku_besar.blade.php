<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-8 w-full">
            <div class="hidden md:flex flex-col">
                <nav class="flex text-[10px] font-medium text-slate-400 gap-2 mb-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary cursor-pointer transition-colors">Dashboard</a>
                    <span>/</span>
                    <span class="text-primary font-bold">Akuntansi</span>
                </nav>
                <h2 class="font-headline font-bold text-lg text-neutral-dark">Buku Besar</h2>
            </div>
            
            <form method="GET" action="{{ route('akuntansi.buku_besar') }}" class="flex-1 max-w-lg ml-auto">
                <div class="flex items-center gap-2">
                    <select name="akun_id" class="w-full bg-surface-lowest border border-slate-200 text-sm rounded-lg py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary shadow-sm text-slate-700" onchange="this.form.submit()">
                        <option value="">-- Pilih Akun Buku Besar --</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </x-slot>

    <!-- Main Content Canvas -->
    <div class="p-8 flex-1 w-full max-w-7xl mx-auto">
        
        <!-- Tab Navigation -->
        <div class="mb-8 border-b border-slate-200 flex gap-6">
            <a href="{{ route('akuntansi.coa') }}" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-medium text-sm transition-colors">
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">account_tree</span> Chart of Accounts</span>
            </a>
            <a href="{{ route('akuntansi.jurnal') }}" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-medium text-sm transition-colors relative">
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">menu_book</span> Jurnal Umum</span>
            </a>
            <a href="{{ route('akuntansi.buku_besar') }}" class="pb-3 border-b-2 border-primary text-primary font-semibold text-sm transition-colors">
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">library_books</span> Buku Besar</span>
            </a>
        </div>

        @if(!$activeAccount)
            <div class="bg-surface-lowest rounded-xl shadow-sm border border-slate-100 p-16 text-center text-slate-500">
                <span class="material-symbols-outlined text-5xl mb-4 text-slate-300">manage_search</span>
                <h3 class="text-lg font-bold text-neutral-dark mb-2">Pilih Akun Buku Besar</h3>
                <p>Silakan pilih akun pada dropdown di atas untuk melihat mutasi ledger akun tersebut.</p>
            </div>
        @else
            <!-- Action Bar -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-headline font-bold text-neutral-dark uppercase">Buku Besar: {{ $activeAccount->nama_akun }}</h3>
                    <p class="text-sm text-slate-500">
                        Kode: <span class="font-data font-bold tracking-wider text-slate-700 mr-4">{{ $activeAccount->kode_akun }}</span>
                        Posisi Normal: <span class="font-bold uppercase text-{{ $activeAccount->posisi_normal == 'debet' ? 'secondary' : 'tertiary-dark' }}">{{ $activeAccount->posisi_normal }}</span>
                    </p>
                </div>
                <button class="bg-white text-slate-600 border border-slate-200 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-sm">print</span> Export Ledger
                </button>
            </div>

            <!-- Mutasi Table -->
            <div class="bg-surface-lowest rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 w-32">Tanggal</th>
                                <th class="px-6 py-4 w-32">No. Jurnal</th>
                                <th class="px-6 py-4">Keterangan Transaksi</th>
                                <th class="px-6 py-4 text-right">Debet</th>
                                <th class="px-6 py-4 text-right">Kredit</th>
                                <th class="px-6 py-4 text-right border-l border-slate-200">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- In a fully verified accounting system, this row shows running balance from previous period -->
                            <tr class="bg-slate-50 font-data">
                                <td colspan="3" class="px-6 py-3 text-right text-sm text-slate-600 font-semibold">SALDO AWAL (MOCK)</td>
                                <td class="px-6 py-3 text-right text-sm text-slate-400">-</td>
                                <td class="px-6 py-3 text-right text-sm text-slate-400">-</td>
                                <td class="px-6 py-3 text-right text-sm font-bold text-neutral-dark border-l border-slate-200">0</td>
                            </tr>
                            
                            @php
                                $runningBalance = 0; // Temporary mockup, assumes initial 0
                            @endphp

                            @forelse ($mutasi as $item)
                                @php
                                    // Update Running Balance
                                    if ($activeAccount->posisi_normal === 'debet') {
                                        $runningBalance += $item->debet;
                                        $runningBalance -= $item->kredit;
                                    } else {
                                        $runningBalance -= $item->debet;
                                        $runningBalance += $item->kredit;
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors {{ $loop->even ? 'bg-slate-50/30' : 'bg-surface-lowest' }}">
                                    <td class="px-6 py-3 text-sm text-slate-600">
                                        {{ \Carbon\Carbon::parse($item->jurnal->tanggal)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-primary font-data font-semibold">
                                        {{ $item->jurnal->no_jurnal }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-neutral">
                                        {{ $item->keterangan ?? $item->jurnal->keterangan }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-data text-sm {{ $item->debet > 0 ? 'text-slate-700' : 'text-slate-300' }}">
                                        {{ $item->debet > 0 ? number_format($item->debet, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-data text-sm {{ $item->kredit > 0 ? 'text-slate-700' : 'text-slate-300' }}">
                                        {{ $item->kredit > 0 ? number_format($item->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-data text-sm font-bold {{ $runningBalance < 0 ? 'text-danger' : 'text-neutral-dark' }} border-l border-slate-100">
                                        {{ number_format($runningBalance, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                        Tidak ada mutasi ditemukan untuk akun ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-surface-lowest border-t border-slate-100">
                    {{ $mutasi->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
