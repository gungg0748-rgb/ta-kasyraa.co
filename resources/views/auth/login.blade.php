<x-guest-layout>
    {{-- Clear scanner session saat halaman login dibuka (setelah logout) --}}
    <script>
        localStorage.removeItem('scanner_connected');
        localStorage.removeItem('scanner_token');
        localStorage.removeItem('scanner_date');
    </script>
    <div class="mb-8">
        <p class="font-sans text-xs font-bold tracking-[0.2em] uppercase text-primary mb-3">Selamat Datang</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Masuk ke <span class="text-primary italic">Sistem</span>
        </h2>
        <p class="text-slate-400 text-sm mt-2">Gunakan akun yang diberikan admin.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium
                          focus:ring-2 focus:ring-primary/20 transition-all"
                   required autofocus autocomplete="username">
            <p class="text-[10px] text-slate-400 mt-1">Contoh: email@kasyraa.co</p>
            @error('email') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Password</label>
            <input id="password" type="password" name="password"
                   class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium
                          focus:ring-2 focus:ring-primary/20 transition-all"
                   required autocomplete="current-password">
            <p class="text-[10px] text-slate-400 mt-1">Masukkan password akun Anda.</p>
            @error('password') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="w-full py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm tracking-wide shadow-lg hover:opacity-90 transition-opacity">
            Masuk
        </button>
    </form>
</x-guest-layout>
