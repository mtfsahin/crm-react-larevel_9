<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Carbon;

class AuthConroller extends Controller
{
    public function register(Request $request){
        $request -> validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|confirmed'
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => md5($request->password)
        ]);
        $user = $user->save();

        $credentials = ['email' => $request->email, 'password' => $request->password];
        if(!Auth::attempt($credentials))
        {
            return response()->json([
                'message' => 'Giriş Yapılamadı Bilgileri Kontrol Ediniz.'
            ], 401);
        }
        $user = $request->user();
        $tokenResult = $user->createToken('Personel Access');
        $token = $tokenResult->token;
        if($request->remember_me){
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        $token->save();
        return response()->json([
            'success'=>true,
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=>$user->email,
            'access_token'=>$tokenResult->access_token,
            'token_type'=>'Bearer',
            'expires_at'=>Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ],201);
    }

}
