<x-app-layout>
    <x-slot name="header">Detail Supplier</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master · Supplier</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                {{ $supplier->name }}
            </h2>
        </div>
        <a href="{{ route('suppliers.edit', $supplier) }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">edit</span> Edit
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8 space-y-5">
            <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Informasi Kontak</h3>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Email</p>
                <p class="text-sm text-blue-900 font-medium">{{ $supplier->email ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Telepon</p>
                <p class="text-sm text-blue-900 font-medium">{{ $supplier->phone ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Alamat</p>
                <p class="text-sm text-blue-900 font-medium leading-relaxed">{{ $supplier->address ?: '—' }}</p>
            </div>
        </div>

        <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100">
                <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Riwayat Pembelian Terakhir</h3>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Tanggal</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Total</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @forelse($supplier->purchases as $purchase)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-8 py-4 text-sm text-slate-600">{{ $purchase->date->format('d M Y') }}</td>
                        <td class="px-8 py-4 font-manrope font-bold text-blue-900 text-sm">
                            Rp {{ number_format($purchase->total, 0, ',', '.') }}
                        </td>
                        <td class="px-8 py-4 text-sm text-slate-400">{{ $purchase->notes ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-8 py-10 text-center text-slate-400 text-sm">Belum ada riwayat pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('suppliers.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">
            ← Kembali ke daftar supplier
        </a>
    </div>
</x-app-layout>
