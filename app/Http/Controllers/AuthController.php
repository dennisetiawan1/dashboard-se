<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('role') === 'admin') {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $password = $request->input('password');

        if ($password === config('admin.upload_password')) {
            session(['role' => 'admin']);

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'password' => 'Password salah.'
        ]);
    }

    public function viewOnly()
    {
        session(['role' => 'viewer']);

        return redirect()->route('dashboard');
    }

   public function logout()
{
    session(['role' => 'viewer']);

    return redirect()->route('dashboard');
}
}