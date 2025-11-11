<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/', function () {
    return view('index');
});