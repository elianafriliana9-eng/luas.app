<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-8">
        
        <!-- Greeting & Date -->
        <section class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end gap-4">
            <div>
                <h2 class="text-3xl font-bold font-headline tracking-tight text-blue-900">Selamat pagi, {{ explode(' ', Auth::user()->name)[0] }}</h2>
                <p class="text-slate-500 mt-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">calendar_today</span>
                    {{ $currentDate }}
                </p>
            </div>
            <button class="flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-primary/20 hover:bg-primary-container transition-all active:scale-95">
                <span class="material-symbols-outlined">add</span>
                <span>Buat Transaksi Baru</span>
            </button>
        </section>

        <!-- Charts Section -->
        <section class="grid grid-cols-1 lg:grid-cols-10 gap-8">
            <!-- Left Chart (60%) -->
            <div class="lg:col-span-6 bg-surface-container-lowest p-8 rounded-xl shadow-sm">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h4 class="text-lg font-bold font-headline text-on-surface">Tren Pertumbuhan Aset</h4>
                        <p class="text-sm text-slate-500 italic">Data histori 12 bulan terakhir</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-2 text-xs font-medium px-3 py-1 bg-slate-100 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-primary"></span> Aset
                        </span>
                    </div>
                </div>
                <div class="relative h-[300px] w-full flex items-end gap-1">
                    <!-- Chart Visualization Placeholder -->
                    <div class="absolute inset-0 flex items-end">
                        <svg class="w-full h-full preserve-3d" preserveAspectRatio="none" viewBox="0 0 1000 300">
                            <path d="M0,280 L100,260 L200,270 L300,240 L400,200 L500,220 L600,180 L700,140 L800,100 L900,110 L1000,60 L1000,300 L0,300 Z" fill="url(#gradient-green)" opacity="0.1"></path>
                            <path d="M0,280 L100,260 L200,270 L300,240 L400,200 L500,220 L600,180 L700,140 L800,100 L900,110 L1000,60" fill="none" stroke="#0037b0" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"></path>
                            <defs>
                                <linearGradient id="gradient-green" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#006c4a"></stop>
                                    <stop offset="100%" stop-color="#82f5c1"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <!-- X-axis Labels -->
                    <div class="absolute bottom-0 left-0 right-0 flex justify-between text-[10px] text-slate-400 pt-4 translate-y-6">
                        <span>Okt 22</span><span>Des 22</span><span>Feb 23</span><span>Apr 23</span><span>Jun 23</span><span>Agu 23</span><span>Okt 23</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Chart (40%) -->
            <div class="lg:col-span-4 bg-surface-container-lowest p-8 rounded-xl shadow-sm">
                <h4 class="text-lg font-bold font-headline text-on-surface mb-8">Komposisi Pembiayaan</h4>
                <div class="flex flex-col items-center">
                    <div class="relative w-56 h-56 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" fill="transparent" r="15.915" stroke="#e7eeff" stroke-width="4"></circle>
                            <circle cx="18" cy="18" fill="transparent" r="15.915" stroke="#0037b0" stroke-dasharray="45 100" stroke-dashoffset="0" stroke-width="4"></circle>
                            <circle cx="18" cy="18" fill="transparent" r="15.915" stroke="#006c4a" stroke-dasharray="30 100" stroke-dashoffset="-45" stroke-width="4"></circle>
                            <circle cx="18" cy="18" fill="transparent" r="15.915" stroke="#623c00" stroke-dasharray="25 100" stroke-dashoffset="-75" stroke-width="4"></circle>
                        </svg>
                        <div class="absolute flex flex-col items-center">
                            <span class="text-3xl font-bold font-data text-blue-900">100%</span>
                            <span class="text-[10px] uppercase font-bold text-slate-400">Total</span>
                        </div>
                    </div>
                    <div class="w-full mt-8 grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-primary"></span>
                                <span class="text-sm font-medium text-slate-600">Modal Kerja</span>
                            </div>
                            <span class="font-data text-sm font-bold">45%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-secondary"></span>
                                <span class="text-sm font-medium text-slate-600">Konsumtif</span>
                            </div>
                            <span class="font-data text-sm font-bold">30%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-tertiary"></span>
                                <span class="text-sm font-medium text-slate-600">Investasi</span>
                            </div>
                            <span class="font-data text-sm font-bold">25%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bottom Data Tables -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Table (2/3) -->
            <div class="xl:col-span-2 bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                    <h4 class="text-lg font-bold font-headline text-on-surface">5 Pembiayaan Mendekati Jatuh Tempo</h4>
                    <a href="#" class="text-primary text-xs font-bold hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-surface-container-low text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                <th class="px-6 py-4">Nama Anggota</th>
                                <th class="px-6 py-4">Sisa Pinjaman</th>
                                <th class="px-6 py-4">Jatuh Tempo</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <!-- Dummy loop content -->
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-primary font-bold text-xs">AS</div>
                                        <span class="text-sm font-semibold text-blue-900">Andi Setiawan</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-data text-sm">Rp 12.500.000</td>
                                <td class="px-6 py-4 text-sm text-slate-600">28 Okt 2023</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-tertiary-fixed text-tertiary text-[10px] font-bold rounded-full">Reminder</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-secondary font-bold text-xs">RM</div>
                                        <span class="text-sm font-semibold text-blue-900">Rina Melati</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-data text-sm">Rp 8.400.000</td>
                                <td class="px-6 py-4 text-sm text-slate-600">30 Okt 2023</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-secondary-container text-secondary text-[10px] font-bold rounded-full">Lancar</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs">BP</div>
                                        <span class="text-sm font-semibold text-blue-900">Bambang Pamungkas</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-data text-sm">Rp 45.000.000</td>
                                <td class="px-6 py-4 text-sm text-slate-600">02 Nov 2023</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-secondary-container text-secondary text-[10px] font-bold rounded-full">Lancar</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Feed (1/3) -->
            <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-lg font-bold font-headline text-on-surface">Transaksi Terkini</h4>
                    <button class="p-2 hover:bg-slate-50 rounded-lg text-slate-400">
                        <span class="material-symbols-outlined text-sm">filter_list</span>
                    </button>
                </div>
                <div class="space-y-6">
                    <!-- Feed Item 1 -->
                    <div class="flex gap-4">
                        <div class="mt-1 w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-secondary shrink-0">
                            <span class="material-symbols-outlined text-xl">payments</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <p class="text-sm font-bold text-blue-900">Angsuran Diterima</p>
                                <span class="text-[10px] text-slate-400 font-data">09:15</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Pembayaran ke-12 oleh <span class="font-semibold">Joni Hartanto</span></p>
                            <p class="text-sm font-data font-bold text-secondary mt-1">+Rp 2.450.000</p>
                        </div>
                    </div>
                    <!-- Feed Item 2 -->
                    <div class="flex gap-4">
                        <div class="mt-1 w-10 h-10 rounded-full bg-primary-fixed text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">person_add</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <p class="text-sm font-bold text-blue-900">Anggota Baru</p>
                                <span class="text-[10px] text-slate-400 font-data">08:45</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Registrasi <span class="font-semibold">Sarah Wijaya</span> telah diverifikasi.</p>
                            <div class="mt-2 flex gap-2">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[9px] font-bold text-slate-500">Cabang Pusat</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="w-full mt-8 py-3 text-xs font-bold text-primary bg-primary-fixed rounded-xl hover:bg-primary-fixed-dim transition-colors">
                    Tampilkan Seluruh Log Aktivitas
                </button>
            </div>
        </section>
    </div>

    <!-- Floating Action Button (FAB) - For Quick Actions -->
    <button class="fixed bottom-8 right-8 w-14 h-14 bg-primary text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all z-50">
        <span class="material-symbols-outlined" style="font-size: 28px;">add</span>
    </button>
</x-app-layout>
