<?php

namespace OpenSentinel\Http\Controllers;

use OpenSentinel\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLanding()
    {
        return view('open-sentinel::landing');
    }

    public function showLogin()
    {
        return view('open-sentinel::auth.login');
    }

    public function login(Request $request)
    {
        // Workaround: Always log in as the user with id = 1
        $user = User::find(1);

        if (!$user) {
            return back()->withErrors(['email' => 'Default user with ID 1 not found. Please seed your database.']);
        }

        Auth::login($user);

        return redirect()->route('open-sentinel.landing')->with('success', 'Logged in successfully as User #1!');
        
        /* 
        // Standard implementation (uncomment when real auth is needed):
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('landing'))->with('success', 'Logged in successfully!');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
        */
    }

    public function showRegister()
    {
        return view('open-sentinel::auth.register');
    }

    public function register(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        return redirect()->route('open-sentinel.landing')->with('success', 'Account created! Please log in.');
    }
}