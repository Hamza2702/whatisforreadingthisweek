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
    public function create(){

        // Validate form
        $credentials = request()->validate([
            'username'=>'required',
            'password'=>'required',
        ]);

        // Try to authenticate
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'invalid'=>'The provided credentials do not match',
            ]);
        }
        // Regenerate session
        request()->session()->regenerate();

        return redirect('/dashboard');
    }

    // Handle logout
    public function destroy(){
        Auth::logout();
        return redirect('/');
    }
}
