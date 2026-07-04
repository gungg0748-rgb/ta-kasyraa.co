<x-app-layout>
    <x-slot name="header">Tambah Akun</x-slot>

    <div class="mb-8">
        <p class="font-sans text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Admin · Manajemen Akun</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Buat Akun <span class="text-primary italic">Baru</span>
        </h2>
    </div>

    <div class="max-w-lg">
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Nama Lengkap (Name)</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium
                                  focus:ring-2 focus:ring-primary/20 transition-all" required>
                    <p class="text-[10px] text-slate-400 mt-1">Nama pengguna</p>
                    @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium
                                  focus:ring-2 focus:ring-primary/20 transition-all" required>
                    <p class="text-[10px] text-slate-400 mt-1">Contoh: email@kasyraa.co</p>
                    @error('email') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Role (Peran)</label>
                    <select name="role"
                            class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium
                                   focus:ring-2 focus:ring-primary/20 transition-all" required>
                        <option value="">— Pilih Role —</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
                        <option value="gudang" {{ old('role') === 'gudang' ? 'selected' : '' }}>Gudang</option>
                    </select>
                    @error('role') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Password (Password)</label>
                    <input type="password" name="password"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium
                                  focus:ring-2 focus:ring-primary/20 transition-all" required>
                    <p class="text-[10px] text-slate-400 mt-1">Minimal 8 karakter</p>
                    @error('password') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Konfirmasi Password (Confirm)</label>
                    <input type="password" name="password_confirmation"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium
                                  focus:ring-2 focus:ring-primary/20 transition-all" required>
                    <p class="text-[10px] text-slate-400 mt-1">Ulangi password</p>
                </div>

                <div class="flex items-center gap-4 pt-3">
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm tracking-wide shadow-lg hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-base">save</span>
                        Simpan Akun
                    </button>
                    <a href="{{ route('users.index') }}"
                       class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
