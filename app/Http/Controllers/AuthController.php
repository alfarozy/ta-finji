<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function loginProccess(Request $request)
    {
        // 1. Validasi input
        $credentials = $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ],
            [
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Email tidak valid',
                'password.required' => 'Kata sandi wajib diisi',
            ]
        );

        // 2. Coba login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 3. Redirect setelah login sukses
            return redirect()->route('dashboard.index');
        }

        // 4. Jika gagal
        return back()->with('error', 'Email atau kata sandi salah.');
    }
    public function register()
    {
        return view('auth.register');
    }


    public function registerProccess(Request $request)
    {
        // 1. Validasi input + custom message
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'whatsapp' => ['required', 'string', 'min:10'],
                'password' => ['required', 'min:6'],
            ],
            [
                'name.required' => 'Nama wajib diisi',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'whatsapp.required' => 'Nomor WhatsApp wajib diisi',
                'password.required' => 'Kata sandi wajib diisi',
                'password.min' => 'Kata sandi minimal 6 karakter',
            ]
        );

        // 2. Normalisasi WhatsApp
        try {
            $normalizedWhatsapp = $this->normalizeWhatsapp($validated['whatsapp']);
        } catch (\InvalidArgumentException $e) {
            return back()
                ->with('error', 'Format nomor WhatsApp tidak valid')
                ->withInput();
        }

        // 3. Validasi whatsapp unik
        $exists = User::where('whatsapp', $normalizedWhatsapp)->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'whatsapp' => 'Nomor WhatsApp sudah terdaftar',
                ])
                ->withInput();
        }

        // 4. Simpan user (password DI-HASH)
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $normalizedWhatsapp,
            'password' => Hash::make($validated['password']),
        ]);

        // 5. Redirect ke login dengan pesan sukses
        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran berhasil. Silakan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Hapus auth user

        $request->session()->invalidate(); // Hapus session
        $request->session()->regenerateToken(); // Regenerate CSRF

        return redirect()->route('login');
    }

    private function normalizeWhatsapp(string $whatsapp): string
    {
        // Hapus spasi, strip, dan karakter non-angka
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);

        // Jika diawali 08 → ganti jadi 62
        if (str_starts_with($whatsapp, '08')) {
            return '62' . substr($whatsapp, 1);
        }

        // Jika diawali 8 → ganti jadi 62
        if (str_starts_with($whatsapp, '8')) {
            return '62' . $whatsapp;
        }

        // Jika sudah diawali 62 → biarkan
        if (str_starts_with($whatsapp, '62')) {
            return $whatsapp;
        }

        // Jika format tidak dikenali
        throw new \InvalidArgumentException('Format nomor WhatsApp tidak valid');
    }
}
