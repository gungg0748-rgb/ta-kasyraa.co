<x-app-layout>
    <x-slot name="header">Tambah Supplier</x-slot>

    <div class="mb-8">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Supplier <span class="text-primary italic">Baru</span>
        </h2>
    </div>

    <div class="max-w-lg bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
        <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Nama Supplier</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20"
                       required>
                <p class="text-[10px] text-slate-400 mt-1">Nama perusahaan / toko</p>
                @error('name') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Email <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    <p class="text-[10px] text-slate-400 mt-1">Contoh: email@supplier.com</p>
                    @error('email') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Telepon <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    <p class="text-[10px] text-slate-400 mt-1">Contoh: 08xx-xxxx-xxxx</p>
                    @error('phone') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Alamat <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                <textarea name="address" rows="3"
                          class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">{{ old('address') }}</textarea>
                <p class="text-[10px] text-slate-400 mt-1">Alamat lengkap supplier...</p>
                @error('address') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-base">save</span> Simpan
                </button>
                <a href="{{ route('suppliers.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
