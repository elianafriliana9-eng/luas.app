<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Pengajuan Pembiayaan</h2><a href="{{ route('pembiayaan.registrasi') }}" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Ke Registrasi →</a></div>
    </x-slot>
    <div class="p-6"><div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <span class="material-symbols-outlined text-5xl text-gray-300 mb-3">move_to_inbox</span>
        <p class="text-gray-500 text-sm">Pengajuan pembiayaan telah dipindahkan ke halaman <strong>Registrasi</strong>.</p>
        <a href="{{ route('pembiayaan.registrasi') }}" class="inline-block mt-4 px-6 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Ke Halaman Registrasi →</a>
    </div></div>
</x-app-layout>
