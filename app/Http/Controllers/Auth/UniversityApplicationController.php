<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UniversityApplicationController extends Controller
{
    /**
     * Show the university application form.
     * Accessible by anyone (guest or logged in).
     */
    public function create(): View
    {
        return view('university.apply');
    }

    /**
     * Handle the university application submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'domain'          => ['required', 'string', 'max:255', 'unique:universities,domain'],
            'state'           => ['required', 'string', 'max:100'],
            'city'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'applicant_name'  => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:20'],
        ], [
            'domain.unique' => 'A university with this email domain is already registered.',
        ]);

        // Strip leading @ if user typed it
        $domain = ltrim(strtolower(trim($request->domain)), '@');

        University::create([
            'name'            => $request->name,
            'domain'          => $domain,
            'state'           => $request->state,
            'city'            => $request->city,
            'country'         => 'India',
            'description'     => $request->description,
            'status'          => 'pending',
            'applicant_name'  => $request->applicant_name,
            'applicant_email' => $request->applicant_email,
            'applicant_phone' => $request->applicant_phone,
        ]);

        return redirect()->route('university.apply.submitted');
    }

    /**
     * Confirmation page shown after successful application.
     */
    public function submitted(): View
    {
        return view('university.submitted');
    }

    /**
     * JSON endpoint — returns approved universities filtered by state.
     * Used by the student registration page JS dropdown filter.
     */
    public function byState(Request $request)
    {
        $state = $request->query('state');

        $universities = University::approved()
            ->when($state, fn($q) => $q->where('state', $state))
            ->select('id', 'name', 'city', 'state', 'domain')
            ->orderBy('name')
            ->get();

        return response()->json($universities);
    }
}
