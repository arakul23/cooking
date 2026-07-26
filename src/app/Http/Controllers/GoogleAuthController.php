<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function callback(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            \Log::warning($e->getMessage());
            return response()->json(['message' => 'Invalid code'], 401);
        }

        $user = User::whereHas('oauthProviders', function ($query) use ($googleUser) {
            $query->where('provider', 'google')
                ->where('provider_id', $googleUser->getId());
        })->first();

        if (! $user) {
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                ['name' => $googleUser->getName(), 'password' => null]
            );

            $user->oauthProviders()->create([
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken ?? null,
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
