<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login()
    {
        if (!Auth::attempt(request(['email', 'password'])))
        {
            return redirect('login')->withErrors([
                'email' => 'These credentials do not match our credentials.'
            ])->withInput(request(['email']));
        }

        return redirect('backstage/concerts/');
    }

    public function logout()
    {
        Auth::logout();
        
        return redirect('login');
    }
}
