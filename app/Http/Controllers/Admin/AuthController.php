<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower($data['email']))->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Email or password is incorrect.']);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('admin', ['*'], now()->addDays(14))->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'current_password' => ['required_with:password', 'current_password:sanctum'],
            'password' => ['nullable', 'confirmed', Password::min(10)->mixedCase()->numbers()],
        ]);

        $user->fill(['name' => $data['name'], 'email' => strtolower($data['email'])]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        }
        $user->save();

        return response()->json($user);
    }
}
