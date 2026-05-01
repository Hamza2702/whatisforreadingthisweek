<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    // Show login form
    public function show(){
        return view('auth.login');
    }

    // Handle login
    public function create()
    {
        $credentials = request()->validate([
            'login'    => 'required',
            'password' => 'required',
        ]);

        $attempt = [
            'username' => $credentials['login'],
            'password' => $credentials['password'],
        ];

        if (!Auth::attempt($attempt)) {
            throw ValidationException::withMessages([
                'invalid' => 'The provided credentials do not match',
            ]);
        }

        request()->session()->regenerate();

        $role = strtolower(Auth::user()->role ?? '');

        return match ($role) {
            'admin' => redirect('/admin'),
            'teacher', 'headteacher' => redirect('/teacher'),
            default => redirect('/dashboard'),
        };
    }

    // Handle logout
    public function destroy(){
        Auth::logout();
        return redirect('/');
    }
}
