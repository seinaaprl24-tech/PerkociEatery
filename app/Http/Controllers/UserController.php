<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = ['admin', 'kasir', 'manager'];

    public function index(): AnonymousResourceCollection
    {
        $users = User::orderByDesc('id_user')->paginate(10);

        return UserResource::collection($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        // Password otomatis di-hash oleh cast 'hashed' pada model User.
        $user = User::create($validated);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user): UserResource
    {
        $validated = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'role' => ['sometimes', 'required', Rule::in(self::ROLES)],
        ]);

        // Password kosong/null = tidak diganti (jangan sampai menimpa dengan null).
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        // Ganti password atau role: paksa login ulang di semua perangkat.
        if ($user->wasChanged(['password', 'role'])) {
            $user->tokens()->delete();
        }

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun yang sedang dipakai.',
            ], 422);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            // 23000 = pelanggaran foreign key (user sudah punya transaksi/pemasukan/pengeluaran).
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }

            return response()->json([
                'message' => 'User tidak dapat dihapus karena sudah memiliki data transaksi.',
            ], 409);
        }

        $user->tokens()->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }
}
