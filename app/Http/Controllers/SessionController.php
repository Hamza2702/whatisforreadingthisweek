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
            'login'=>'required',
            'password'=>'required',
        ]);

        // map login to username col in db
        $attempt = [
            'username' => $credentials['login'],
            'password' => $credentials['password'],
        ];

        // Try to authenticate
        if (!Auth::attempt($attempt)) {
            throw ValidationException::withMessages([
                'invalid'=>'The provided credentials do not match',
            ]);
        }
        // Regenerate session
        request()->session()->regenerate();

        // if teacher/admin, redirect to dashboards
        if (Auth::user()->isAdmin()) {
            return redirect('/admin');
        } elseif (Auth::user()->isTeacher()) {
            return redirect('/teacher');
        } else {
            return redirect()->intended('/dashboard');
        }
    }

    // Handle logout
    public function destroy(){
        Auth::logout();
        return redirect('/');
    }
}
