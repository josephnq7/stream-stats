<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
//        $result = $client->request(
//            'POST',
//            'https://id.twitch.tv/oauth2/revoke',
//            [
//                'form_params' => [
//                    'client_id' => config('services.twitch.client_id'),
//                    'token' => 'g5c7kdx9ix9r4vqey2bmaclzg5of59'
//                ]
//            ]
//        );
        $request->user()->currentAccessToken()->delete();
    }
}
