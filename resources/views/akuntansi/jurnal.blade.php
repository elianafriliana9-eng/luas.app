<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-8 w-full">
            <div class="hidden md:flex flex-col">
                <nav class="flex text-[10px] font-medium text-slate-400 gap-2 mb-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary cursor-pointer transition-colors">Dashboard</a>
                    <span>/</span>
                    <span class="text-primary font-bold">Akuntansi</span>
                </nav>
                <h2 class="font-headline font-bold text-lg text-neutral-dark">Jurnal Umum</h2>
            </div>
            
            <form method="GET" action="{{ route('akuntansi.jurnal') }}" class="flex-1 max-w-lg ml-auto">
                <div class="flex items-center bg-surface-lowest rounded-lg px-3 py-1.5 shadow-sm border border-slate-200 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                    <span class="material-symbols-outlined text-slate-400 text-sm">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" class="border-none focus:ring-0 text-sm w-full bg-transparent placeholder-slate-400" placeholder="Cari No Jurnal atau Uraian...">
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
            <a href="{{ route('akuntansi.jurnal') }}" class="pb-3 border-b-2 border-primary text-primary font-semibold text-sm transition-colors relative">
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">menu_book</span> Jurnal Umum</span>
            </a>
            <a href="{{ route('akuntansi.buku_besar') }}" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-medium text-sm transition-colors">
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">library_books</span> Buku Besar</span>
            </a>
        </div>

        <!-- Action Bar -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-headline font-bold text-neutral-dark">Daftar Transaksi Jurnal</h3>
            <button class="bg-primary text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 shadow hover:bg-primary-dark transition-all">
                <span class="material-symbols-outlined text-sm">add</span> Input Jurnal Manual
            </button>
        </div>

        <!-- Journals List -->
        <div class="space-y-4">
            @forelse ($jurnals as $jurnal)
                <div class="bg-surface-lowest rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <!-- Jurnal Header -->
                    <div class="bg-slate-50 px-6 py-4 flex flex-col md:flex-row md:items-center justify-between border-b border-slate-200 gap-4">
                        <div class="flex items-center gap-6">
                            <div>
                                <span class="text-xs text-slate-500 block">Tanggal</span>
                                <span class="font-data font-bold text-neutral-dark">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">No. Jurnal</span>
                                <span class="font-data font-bold text-primary">{{ $jurnal->no_jurnal }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Sumber</span>
                                @if($jurnal->jenis === 'otomatis')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-secondary/10 text-secondary uppercase mx-auto mt-0.5">SISTEM</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-tertiary/10 text-tertiary-dark uppercase mx-auto mt-0.5">MANUAL</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-left md:text-right">
                            <span class="text-sm font-medium text-neutral block">{{ $jurnal->keterangan }}</span>
                            <span class="text-xs text-slate-400 mt-1 block">Oleh: {{ $jurnal->pembuat ? $jurnal->pembuat->name : 'Sistem' }}</span>
                        </div>
                    </div>
                    
                    <!-- Jurnal Details -->
                    <table class="w-full text-left">
                        <thead class="bg-white text-[11px] font-semibold text-slate-400 uppercase tracking-wider hidden md:table-header-group border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-2 w-1/4">Kode Akun</th>
                                <th class="px-6 py-2 w-1/4">Nama Akun</th>
                                <th class="px-6 py-2 w-1/4 text-right">Debet</th>
                                <th class="px-6 py-2 w-1/4 text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @php
                                $totalDebet = 0;
                                $totalKredit = 0;
                            @endphp
                            @foreach ($jurnal->details as $detail)
                                @php
                                    $totalDebet += $detail->debet;
                                    $totalKredit += $detail->kredit;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 font-data text-sm text-slate-600">
                                        {{ $detail->akun->kode_akun }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-neutral">
                                        @if($detail->kredit > 0)
                                            <span class="ml-4 italic">{{ $detail->akun->nama_akun }}</span>
                                        @else
                                            <span>{{ $detail->akun->nama_akun }}</span>
                                        @endif
                                        @if($detail->keterangan)
                                            <span class="block text-xs text-slate-400 mt-0.5 ml-{{ $detail->kredit > 0 ? '4' : '0' }}">{{ $detail->keterangan }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-right font-data text-sm text-slate-700">
                                        {{ $detail->debet > 0 ? number_format($detail->debet, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-data text-sm text-slate-700">
                                        {{ $detail->kredit > 0 ? number_format($detail->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-surface-container font-data font-bold">
                                <td colspan="2" class="px-6 py-3 text-right text-sm text-slate-600 uppercase">Total</td>
                                <td class="px-6 py-3 text-right text-sm text-neutral-dark border-t border-slate-200">
                                    {{ number_format($totalDebet, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3 text-right text-sm text-neutral-dark border-t border-slate-200">
                                    {{ number_format($totalKredit, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="bg-surface-lowest rounded-xl shadow-sm border border-slate-200 p-12 text-center text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-3 text-slate-300">menu_book</span>
                    <p>Tidak ada riwayat jurnal keuangan.</p>
                </div>
            @endforelse
            
            <div class="pt-4">
                {{ $jurnals->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
