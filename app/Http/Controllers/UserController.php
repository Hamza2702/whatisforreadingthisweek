<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\File;


class UserController extends Controller
{
    // Create
    public function create(){
        return view('auth.register');
    }

    // Store new user
    public function store(){
        $attributes = request()->validate(['name'=>['required'],
            'email'=>['required','email','unique:users,email','confirmed'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'password'=>['confirmed','required',Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);
        
        $images = [];
        $files = File::files('images/pfp');
        foreach($files as $file){
            // Check if its an image file
            $extension = strtolower($file->getExtension());
            if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])){
                // Convert path to web URL
                $relativePath = 'images/pfp/' . $file->getFilename();
                $images[] = '/' . $relativePath;
            }
        }

        $attributes['pfp'] = $images[array_rand($images)];
        $user = User::create($attributes);
        Auth::login($user);
        
        return redirect('/');
    }
}
