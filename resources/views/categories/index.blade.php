<x-app-layout>
    <x-slot name="header">Kategori</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Kategori <span class="text-primary italic">Barang</span>
            </h2>
        </div>
        <a href="{{ route('categories.create') }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">add</span>
            Tambah Kategori
        </a>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">#</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Nama (Name)</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Deskripsi (Description)</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Produk (Products)</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Aksi (Actions)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @forelse($categories as $cat)
                <tr class="hover:bg-blue-50/30 even:bg-slate-50/40 transition-colors">
                    <td class="px-8 py-4 text-xs text-slate-400 font-mono">{{ $categories->firstItem() + $loop->index }}</td>
                    <td class="px-8 py-4 font-manrope font-bold text-blue-900 text-sm">{{ $cat->name }}</td>
                    <td class="px-8 py-4 text-sm text-slate-500">{{ $cat->description ?: '—' }}</td>
                    <td class="px-8 py-4">
                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-tighter rounded border border-blue-100">
                            {{ $cat->products_count }} produk
                        </span>
                    </td>
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('categories.edit', $cat) }}"
                               class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-bold text-rose-500 hover:underline underline-offset-4 tracking-widest uppercase">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-16 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-5xl block mb-3">category</span>
                        <p class="text-slate-400 text-sm">Belum ada kategori.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-8 py-4 bg-surface-container-low/30 border-t border-slate-100/50 flex items-center justify-between">
            <span class="text-xs text-slate-400">{{ $categories->total() }} kategori</span>
            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
