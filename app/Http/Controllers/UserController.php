<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Controller untuk mengelola data pengguna.
 */
class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user kecuali yang sedang login.
     */
    public function index()
    {
        // Menampilkan daftar semua user kecuali yang sedang login.
        $users = User::where('id', '!=', auth()->id())->latest('id')->paginate(15)->withQueryString();
        return view('users.index', compact('users'));
    }

    /**
     * Menampilkan form tambah akun user baru.
     */
    public function create()
    {
        // Menampilkan form tambah akun user baru.
        return view('users.create');
    }

    /**
     * Menyimpan akun user baru ke database.
     */
    public function store(Request $request)
    {
        // Menyimpan akun user baru ke database.
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:kasir,gudang,admin',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'Akun berhasil dibuat.');
    }

    /**
     * Menampilkan form edit data user.
     */
    public function edit(User $user)
    {
        // Menampilkan form edit data user.
        return view('users.edit', compact('user'));
    }

    /**
     * Memperbarui data user, termasuk password kalau diisi.
     */
    public function update(Request $request, User $user)
    {
        // Memperbarui data user, termasuk password kalau diisi.
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => 'required|in:kasir,gudang,admin',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('users.index')->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Toggle status aktif/nonaktif akun user.
     */
    public function toggleActive(User $user)
    {
        // Toggle status aktif/nonaktif akun user.
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('users.index')->with('success', "Akun berhasil {$status}.");
    }
}
