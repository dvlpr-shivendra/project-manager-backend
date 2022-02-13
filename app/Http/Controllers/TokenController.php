<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response([
                "message" => 'The given data was invalid',
                "errors" => [
                    'email' => 'The provided credentials do not match our records.',
                ]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $token = $user->createToken('token');

        return ['token' => $token->plainTextToken];

    }
}
