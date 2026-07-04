<x-app-layout>
    <x-slot name="header">Manajemen Akun</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="font-sans text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Manajemen Akun</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Daftar <span class="text-primary italic">Pengguna</span>
            </h2>
        </div>
        <a href="{{ route('users.create') }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm tracking-wide shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">person_add</span>
            Tambah Akun
        </a>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl overflow-hidden editorial-shadow">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">#</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Nama (Name)</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Email</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Peran (Role)</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Aksi (Actions)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @forelse($users as $user)
                    <tr class="hover:bg-blue-50/30 even:bg-slate-50/40 transition-colors">
                        <td class="px-8 py-5 text-xs text-slate-400 font-mono">{{ $users->firstItem() + $loop->index }}</td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-manrope font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-manrope font-bold text-blue-900 text-sm">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-sm text-slate-500">{{ $user->email }}</td>
                        <td class="px-8 py-5">
                            <span class="px-2 py-1 text-[9px] font-black uppercase tracking-tighter rounded border
                                {{ $user->role === 'kasir'
                                    ? 'bg-blue-50 text-blue-700 border-blue-100'
                                    : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-2 py-1 text-[9px] font-black uppercase tracking-tighter rounded border
                                {{ $user->is_active
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                    : 'bg-rose-50 text-rose-600 border-rose-100' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="text-xs font-bold uppercase tracking-widest hover:underline underline-offset-4
                                               {{ $user->is_active ? 'text-rose-500' : 'text-emerald-600' }}">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <span class="material-symbols-outlined text-slate-200 text-5xl block mb-3">group</span>
                            <p class="text-slate-400 text-sm font-medium">Belum ada akun pengguna.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-surface-container-low/30 border-t border-slate-100/50 flex justify-between items-center">
            <span class="text-xs text-slate-400 font-medium">{{ $users->total() }} pengguna terdaftar</span>
            {{ $users->links() }}
        </div>
    </div>

</x-app-layout>
