<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // If anyone’s already logged in, send them where they belong
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        $remember = $request->boolean('remember');

        // SINGLE guard for everyone
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user()->role);
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->withInput($request->only('email', 'remember'));
    }

      private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin'         => redirect()->route('admin.dashboard'),
            'admission'     => redirect()->route('admission.dashboard'),
            'pharmacy'      => redirect()->route('pharmacy.dashboard'),
            'doctor'        => redirect()->route('doctor.dashboard'),
            'patient'       => redirect()->route('patient.dashboard'),
            'imaging'       => redirect()->route('imaging.dashboard'),
            'supplies'      => redirect()->route('supplies.dashboard'),
            'operating_room'=> redirect()->route('operating-room.dashboard'),
            default         => redirect()->route('home'),
        };
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
