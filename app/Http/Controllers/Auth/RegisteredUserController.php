<?php

// app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * Passes all approved universities (with state/city) to the view
     * so the frontend JS can filter by state dynamically.
     */
    public function create(): View
    {
        // Only approved universities are shown to students
        $universities = University::where('status', 'approved')
            ->select('id', 'name', 'state', 'city', 'domain')
            ->orderBy('state')
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('universities'));
    }

    /**
     * Handle an incoming registration request.
     *
     * Flow:
     * 1. Validate input
     * 2. Confirm selected university exists and is approved
     * 3. Validate email domain matches university domain
     * 4. Create user with status = 'pending' (no auto-login)
     * 5. Redirect to pending approval page
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Step 1: Basic validation
        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'university_id'   => ['required', 'integer', 'exists:universities,id'],
            'password'        => ['required', 'confirmed', Password::defaults()],
        ]);

        // Step 2: Confirm university is approved
        $university = University::where('id', $request->university_id)
            ->where('status', 'approved')
            ->firstOrFail();

        // Step 3: Validate email domain against university domain
        // e.g. university domain = "bits-pilani.ac.in"
        // student email = "john@online.bits-pilani.ac.in" — the domain must end with university domain
        $emailDomain = substr(strrchr($request->email, '@'), 1); // extract domain from email

        if (! str_ends_with($emailDomain, $university->domain)) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => "Your email must be from the {$university->name} domain (@{$university->domain}).",
                ]);
        }

        // Step 4: Create user — status is 'pending', NOT logged in
        User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => 'user',
            'status'        => 'pending',   // Must be verified by Uni Admin before access
            'university_id' => $university->id,
        ]);

        // Step 5: Redirect to pending page — no Auth::login() here
        return redirect()->route('register.pending');
    }
}