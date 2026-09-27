<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/** Staff accounts — owners only. */
class UserController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->user()?->isOwner(), 403, 'Only the owner can manage staff accounts.');
    }

    public function index(Request $request): JsonResponse
    {
        $this->guard($request);

        return response()->json(['data' => User::orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->guard($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:owner,editor'],
            'password' => ['required', Password::min(10)->mixedCase()->numbers()],
        ]);

        return response()->json(User::create([...$data, 'email' => strtolower($data['email'])]), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->guard($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:owner,editor'],
            'password' => ['nullable', Password::min(10)->mixedCase()->numbers()],
        ]);
        if ($user->is($request->user()) && $data['role'] !== 'owner') {
            abort(422, 'You cannot remove your own owner role.');
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return response()->json($user);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->guard($request);
        abort_if($user->is($request->user()), 422, 'You cannot delete your own account.');
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['ok' => true]);
    }
}
