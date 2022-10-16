<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function getLoginPage(): View
    {
        DB::table('captcha')->insert(['value' => random_int(10000, 99999), 'isNew' => true]);
        return view('login', ['captcha' => DB::table('captcha')->orderBy('id', 'desc')->limit(1)->first()]);
    }

    public function login(Request $request)
    {
        if (RateLimiter::tooManyAttempts('key-login' . '|' . $request->ip(), 3)) {
            DB::table('captcha')->update(['isNew' => false]);
            event(new Lockout($request));
            return redirect()->back()->with('error', 'Too Many Attempts Wait for' . ' ' . RateLimiter::availableIn('key-login' . '|' . $request->ip()));
        }
        if ($request->input('captcha') == null) {
            DB::table('captcha')->update(['isNew' => false]);
            RateLimiter::hit('key-login' . '|' . $request->ip(), 30);
            return redirect()->back()->with('error', 'captcha required');
        }
        $isAuth = Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')]);
        $captcha = DB::table('captcha')->where('value', $request->input('captcha'))->where('isNew', true)->first();
        if (!$captcha) {
            DB::table('captcha')->update(['isNew' => false]);
            RateLimiter::hit('key-login' . '|' . $request->ip(), 30);
            return redirect()->back()->with('error', 'Wrong Captcha');
        }
        if ($isAuth) {
            RateLimiter::clear('key-login' . '|' . $request->ip());
            DB::table('captcha')->update(['isNew' => false]);
            dd('Success,Login');
            return redirect()->intended();
        }
        DB::table('captcha')->update(['isNew' => false]);
        RateLimiter::hit('key-login' . '|' . $request->ip(), 30);
        return redirect()->back()->with('error', 'Username atau password salah');
    }

    public function forgotPage(): View
    {
        return view('forgot');
    }
}
