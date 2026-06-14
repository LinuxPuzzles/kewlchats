<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\XmppUsername;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', new XmppUsername, 'unique:users,xmpp_username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'xmpp_username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        // Stash the chosen password (encrypted via model cast) until the email is
        // verified; the provisioning job wipes it afterwards. forceFill because the
        // secret + status are deliberately not mass-assignable.
        $user->forceFill([
            'xmpp_pending_secret' => $request->password,
            'xmpp_status' => 'pending',
        ])->save();

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
