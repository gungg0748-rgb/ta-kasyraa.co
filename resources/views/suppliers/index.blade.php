<x-app-layout>
    <x-slot name="header">Supplier</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Data <span class="text-primary italic">Supplier</span>
            </h2>
        </div>
        <a href="{{ route('suppliers.create') }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">add</span>
            Tambah Supplier
        </a>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">#</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Nama</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Email</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Telepon</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Pembelian</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-blue-50/30 even:bg-slate-50/40 transition-colors">
                    <td class="px-8 py-4 text-xs text-slate-400 font-mono">{{ $suppliers->firstItem() + $loop->index }}</td>
                    <td class="px-8 py-4">
                        <p class="font-manrope font-bold text-blue-900 text-sm">{{ $supplier->name }}</p>
                        @if($supplier->address)
                            <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $supplier->address }}</p>
                        @endif
                    </td>
                    <td class="px-8 py-4 text-sm text-slate-500">{{ $supplier->email ?: '—' }}</td>
                    <td class="px-8 py-4 text-sm text-slate-500">{{ $supplier->phone ?: '—' }}</td>
                    <td class="px-8 py-4">
                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-tighter rounded border border-blue-100">
                            {{ $supplier->purchases_count }} transaksi
                        </span>
                    </td>
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('suppliers.show', $supplier) }}"
                               class="text-xs font-bold text-slate-500 hover:underline underline-offset-4 tracking-widest uppercase">Detail</a>
                            <a href="{{ route('suppliers.edit', $supplier) }}"
                               class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">Edit</a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                  onsubmit="return confirm('Hapus supplier ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-bold text-rose-500 hover:underline underline-offset-4 tracking-widest uppercase">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-16 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-5xl block mb-3">storefront</span>
                        <p class="text-slate-400 text-sm">Belum ada supplier.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-8 py-4 bg-surface-container-low/30 border-t border-slate-100/50 flex items-center justify-between">
            <span class="text-xs text-slate-400">{{ $suppliers->total() }} supplier</span>
            {{ $suppliers->links() }}
        </div>
    </div>
</x-app-layout>
