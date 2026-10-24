<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Mendaftarkan user baru dan mengembalikan token API.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'kasir', 'manager'])],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        $token = $user->createToken(
            $validated['device_name'] ?? 'api-token'
        )->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user dan mengembalikan token API.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        $token = $user->createToken(
            $validated['device_name'] ?? 'api-token'
        )->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user dengan menghapus token yang sedang dipakai.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Mengembalikan data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
