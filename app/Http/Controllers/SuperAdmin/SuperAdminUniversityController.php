<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperAdminUniversityController extends Controller
{
    /**
     * List all universities with filter tabs.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $universities = University::query()
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('state', 'like', "%{$request->search}%")
                  ->orWhere('applicant_email', 'like', "%{$request->search}%");
            }))
            ->withCount('users')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'      => University::count(),
            'pending'  => University::pending()->count(),
            'approved' => University::approved()->count(),
            'rejected' => University::rejected()->count(),
        ];

        return view('super-admin.universities.index', compact('universities', 'status', 'counts'));
    }

    /**
     * Show university details.
     */
    public function show(University $university): View
    {
        $university->load(['users', 'items', 'admin']);

        $stats = [
            'total_users'     => $university->totalUsers(),
            'verified_users'  => $university->totalVerifiedUsers(),
            'pending_users'   => $university->totalPendingUsers(),
            'total_items'     => $university->totalItems(),
            'available_items' => $university->totalActiveItems(),
        ];

        return view('super-admin.universities.show', compact('university', 'stats'));
    }

    /**
     * Approve a pending university application.
     */
    public function approve(University $university): RedirectResponse
    {
        if (!$university->isPending()) {
            return back()->with('warning', 'This university is not in pending status.');
        }

        $university->approve();

        return redirect()->route('super-admin.universities.show', $university)
            ->with('success', "{$university->name} has been approved. You can now issue admin credentials.");
    }

    /**
     * Reject a university application with a reason.
     */
    public function reject(Request $request, University $university): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $university->reject($request->rejection_reason);

        return back()->with('success', "{$university->name}'s application has been rejected.");
    }

    /**
     * Suspend an approved university.
     */
    public function suspend(University $university): RedirectResponse
    {
        if (!$university->isApproved()) {
            return back()->with('warning', 'Only approved universities can be suspended.');
        }

        $university->update(['status' => 'pending']);

        return back()->with('success', "{$university->name} has been suspended.");
    }

    /**
     * Issue admin credentials for the first time.
     * Auto-generates a password, stores it plain + hashed, creates the uni_admin user.
     */
    public function issueCredentials(University $university): RedirectResponse
    {
        if (!$university->isApproved()) {
            return back()->with('warning', 'University must be approved before issuing credentials.');
        }

        if ($university->admin_email) {
            return back()->with('warning', 'Credentials have already been issued. Use the edit form to update them.');
        }

        $adminEmail    = 'admin@' . $university->domain;
        $plainPassword = Str::random(12);

        // Store on university record — plain + hashed
        $university->update([
            'admin_email'            => $adminEmail,
            'admin_password_hash'    => Hash::make($plainPassword),
            'admin_password_plain'   => $plainPassword,
            'credentials_updated_at' => now(),
            'approved_at'            => $university->approved_at ?? now(),
        ]);

        // Create the uni_admin User record
        User::create([
            'name'          => $university->name . ' Admin',
            'email'         => $adminEmail,
            'password'      => Hash::make($plainPassword),
            'role'          => 'uni_admin',
            'status'        => 'verified',
            'university_id' => $university->id,
        ]);

        return back()->with('success',
            "Credentials issued successfully for {$university->name}."
        );
    }

    /**
     * Update credentials — handles both email and/or password changes.
     * Called from the edit credentials form on the show page.
     *
     * Rules:
     * - Email is always required (cannot be blanked out after issue)
     * - Password is optional — if left blank, existing password is kept
     * - If email changes, the uni_admin User record email is updated too
     * - If password changes, both the User record and university record are updated
     */
    public function updateCredentials(Request $request, University $university): RedirectResponse
    {
        if (!$university->isApproved()) {
            return back()->with('warning', 'University must be approved to manage credentials.');
        }

        $request->validate([
            'admin_email'    => ['required', 'email', 'max:255'],
            'admin_password' => ['nullable', 'string', 'min:8', 'max:100'],
        ]);

        $newEmail    = $request->admin_email;
        $newPassword = $request->admin_password; // null if left blank
        $oldEmail    = $university->admin_email;

        // ── Find the existing uni_admin user ─────────────────────
        $uniAdminUser = User::where('email', $oldEmail)
            ->where('role', 'uni_admin')
            ->where('university_id', $university->id)
            ->first();

        // ── Build update payloads ─────────────────────────────────
        $universityUpdates = [
            'admin_email'            => $newEmail,
            'credentials_updated_at' => now(),
        ];

        $userUpdates = [
            'email' => $newEmail,
        ];

        if ($newPassword) {
            $universityUpdates['admin_password_hash']  = Hash::make($newPassword);
            $universityUpdates['admin_password_plain'] = $newPassword;
            $userUpdates['password']                   = Hash::make($newPassword);
        }

        // ── Apply updates ─────────────────────────────────────────
        $university->update($universityUpdates);

        if ($uniAdminUser) {
            $uniAdminUser->update($userUpdates);
        } else {
            // Edge case: user record is missing — recreate it
            User::create([
                'name'          => $university->name . ' Admin',
                'email'         => $newEmail,
                'password'      => Hash::make($newPassword ?? Str::random(12)),
                'role'          => 'uni_admin',
                'status'        => 'verified',
                'university_id' => $university->id,
            ]);
        }

        $message = $newPassword
            ? 'Credentials updated — email and password changed.'
            : 'Admin email updated. Password was not changed.';

        return back()->with('success', $message);
    }

    /**
     * Quick-reset password — generates a new random password.
     * Kept for the one-click reset button (separate from the full edit form).
     */
    public function resetCredentials(University $university): RedirectResponse
    {
        if (!$university->admin_email) {
            return back()->with('warning', 'No credentials have been issued yet.');
        }

        $plainPassword = Str::random(12);

        $university->update([
            'admin_password_hash'    => Hash::make($plainPassword),
            'admin_password_plain'   => $plainPassword,
            'credentials_updated_at' => now(),
        ]);

        User::where('email', $university->admin_email)
            ->where('role', 'uni_admin')
            ->first()
            ?->update(['password' => Hash::make($plainPassword)]);

        return back()->with('success', 'Password has been reset successfully.');
    }

    /**
     * Permanently delete a university and all its data.
     */
    public function destroy(University $university): RedirectResponse
    {
        $university->delete();

        return redirect()->route('super-admin.universities.index')
            ->with('success', "{$university->name} has been permanently removed from the platform.");
    }

    /**
     * AJAX: count of pending university applications.
     */
    public function pendingCount(): JsonResponse
    {
        return response()->json([
            'count' => University::pending()->count(),
        ]);
    }
}