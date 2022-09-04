<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OAuthException;
use App\Http\Controllers\Controller;
use App\Models\OAuthProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class OAuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * @param $provider
     * @return JsonResponse
     */
    public function fetchUrl($provider): JsonResponse
    {
        return response()->json(
            [
                'url' => Socialite::driver($provider)
                    ->scopes('user:read:email user:read:follows')
                    ->stateless()
                    ->with(['force_verify' => 'true'])
                    ->redirect()
                    ->getTargetUrl(),
            ]
        );
    }


    /**
     * @param string $provider
     * @return User|mixed
     * @throws OAuthException
     */
    public function handleCallback(string $provider)
    {
        try {
            /** @var SocialiteUser $sUser */
            $sUser = Socialite::driver($provider)->stateless()->user();

            $oauthProvider = OAuthProvider::where('provider', $provider)
                ->where('provider_user_id', $sUser->getId())
                ->first();

            if ($oauthProvider) {
                //existing user
                $oauthProvider->update(
                    [
                        'access_token' => $sUser->token,
                        'refresh_token' => $sUser->refreshToken,
                        'avatar' => $sUser->avatar,
                    ]
                );

                $user = $oauthProvider->user;
            } else {
                if (User::where('email', $sUser->getEmail())->exists()) {
                    throw new OAuthException('Email was taken!', 400);
                }

                //creating new user
                $user = User::create(
                    [
                        'user_name' => $sUser->getName(),
                        'email' => $sUser->getEmail(),
                        'email_verified_at' => now(),
                    ]
                );

                $user->oauthProviders()->create(
                    [
                        'provider' => $provider,
                        'provider_user_id' => $sUser->getId(),
                        'access_token' => $sUser->token,
                        'refresh_token' => $sUser->refreshToken,
                        'avatar' => $sUser->getAvatar(),
                    ]
                );
            }

            $user->tokens()->delete();
            $token = $user->createToken("{$provider}_token")->plainTextToken;

            return view('oauth/callback', [
                'token' => $token,
                'tokenType' => 'bearer',
                'expireAt' => config('sanctum.expiration'),
            ]);
        } catch (OAuthException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new OAuthException('Something errors', 500);
        }
    }
}
