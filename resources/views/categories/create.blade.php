<x-app-layout>
    <x-slot name="header">Tambah Kategori</x-slot>

    <div class="mb-8">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Kategori <span class="text-primary italic">Baru</span>
        </h2>
    </div>

    <div class="max-w-lg bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
        <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20"
                       required>
                <p class="text-[10px] text-slate-400 mt-1">Contoh: Baju, Celana, Aksesoris</p>
                @error('name') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Deskripsi <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                <textarea name="description" rows="3"
                          class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">{{ old('description') }}</textarea>
                <p class="text-[10px] text-slate-400 mt-1">Deskripsi singkat kategori...</p>
                @error('description') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-base">save</span> Simpan
                </button>
                <a href="{{ route('categories.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
