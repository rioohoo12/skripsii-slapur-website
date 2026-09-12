<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function ensureJsonInput(Request $request): void
    {
        $contentType = $request->header('Content-Type', '');
        $isJson = str_starts_with($contentType, 'application/json');
        if ($isJson && $request->getContent()) {
            $data = json_decode($request->getContent(), true);
            if (is_array($data)) {
                $request->merge($data);
            }
        }
    }

    public function register(Request $request)
    {
        $this->ensureJsonInput($request);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan login dengan email ini.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        ]);

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'siswa',
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ]);

        $token = $user->createToken('slapur-register')->plainTextToken;

        return response()->json([
            'message' => 'Akun berhasil dibuat dan tersimpan di server. Silakan login.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'jenis_kelamin' => $user->jenis_kelamin,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function loginStaff(Request $request)
    {
        $this->ensureJsonInput($request);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->first(['id', 'name', 'email', 'password', 'role']);

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $role = $user->role ?? 'siswa';
        $allowed = ['staff', 'admin', 'super_admin'];
        if (!in_array($role, $allowed)) {
            throw ValidationException::withMessages([
                'email' => ['Akun ini bukan akun staff. Gunakan login siswa atau login guru.'],
            ]);
        }

        $token = $user->createToken('slapur-login-staff')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {
        $this->ensureJsonInput($request);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->first(['id', 'name', 'email', 'password', 'role', 'jenis_kelamin']);

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah. Belum punya akun? Silakan daftar dulu.'],
            ]);
        }

        $role = $user->role ?? 'siswa';
        if ($role !== 'siswa') {
            throw ValidationException::withMessages([
                'email' => ['Akun ini bukan akun siswa.'],
            ]);
        }

        $token = $user->createToken('slapur-login')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'jenis_kelamin' => $user->jenis_kelamin,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function loginGuru(Request $request)
    {
        $this->ensureJsonInput($request);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->first(['id', 'name', 'email', 'password', 'role']);

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $role = $user->role ?? 'siswa';
        $allowed = ['guru', 'admin', 'super_admin'];
        if (!in_array($role, $allowed)) {
            throw ValidationException::withMessages([
                'email' => ['Akun ini bukan akun guru. Gunakan login siswa atau login staff.'],
            ]);
        }

        $token = $user->createToken('slapur-login-guru')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function registerGuru(Request $request)
    {
        $this->ensureJsonInput($request);

        $rules = [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'jenjang_guru' => 'required|in:smp,sma,smp_sma',
            'subject_smp_id' => 'nullable|exists:subjects,id',
            'subject_sma_id' => 'nullable|exists:subjects,id',
        ];
        $jenjang = $request->input('jenjang_guru');
        if ($jenjang === 'smp' || $jenjang === 'smp_sma') {
            $rules['subject_smp_id'] = 'required|exists:subjects,id';
        }
        if ($jenjang === 'sma' || $jenjang === 'smp_sma') {
            $rules['subject_sma_id'] = 'required|exists:subjects,id';
        }

        $validated = $request->validate($rules, [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'jenjang_guru.required' => 'Pilih jenjang (SMP / SMA / Kedua-duanya).',
            'jenjang_guru.in' => 'Jenjang tidak valid.',
            'subject_smp_id.required' => 'Pilih mata pelajaran SMP.',
            'subject_sma_id.required' => 'Pilih mata pelajaran SMA.',
        ]);

        $subjectId = $validated['subject_smp_id'] ?? $validated['subject_sma_id'] ?? null;

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'guru',
            'subject_id' => $subjectId,
            'jenjang_guru' => $validated['jenjang_guru'],
            'subject_smp_id' => $validated['subject_smp_id'] ?? null,
            'subject_sma_id' => $validated['subject_sma_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Akun guru berhasil dibuat. Silakan login.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'jenjang_guru' => $user->jenjang_guru,
                'subject_smp_id' => $user->subject_smp_id,
                'subject_sma_id' => $user->subject_sma_id,
            ],
        ], 201);
    }

    public function forgotPasswordGuru(Request $request)
    {
        $this->ensureJsonInput($request);

        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
        ]);

        $user = User::where('email', $request->email)->first();
        $allowed = ['guru', 'admin', 'super_admin'];
        if (!$user || !in_array($user->role ?? '', $allowed)) {
            return response()->json([
                'message' => 'Jika email terdaftar sebagai akun guru, Anda akan menerima tautan reset password.',
            ]);
        }

        $token = Str::random(60);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // TODO: kirim email dengan link reset (misalnya /guru/reset-password?token=...&email=...)
        // Untuk pengembangan, token bisa di-log atau dikirim response (jangan di production)
        return response()->json([
            'message' => 'Jika email terdaftar sebagai akun guru, Anda akan menerima tautan reset password.',
            'reset_token' => $token,
            'email' => $user->email,
        ]);
    }

    public function resetPasswordGuru(Request $request)
    {
        $this->ensureJsonInput($request);

        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'Email wajib diisi.',
            'token.required' => 'Token reset wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $row = DB::table('password_resets')->where('email', $validated['email'])->first();
        if (!$row || !Hash::check($validated['token'], $row->token)) {
            throw ValidationException::withMessages([
                'token' => ['Token tidak valid atau sudah kadaluarsa.'],
            ]);
        }

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            throw ValidationException::withMessages(['email' => ['Akun tidak ditemukan.']]);
        }

        $user->update(['password' => Hash::make($validated['password'])]);
        DB::table('password_resets')->where('email', $validated['email'])->delete();

        return response()->json(['message' => 'Password berhasil direset. Silakan login.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
