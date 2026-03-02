<?php

namespace App\Http\Controllers\UniAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniAdminUserController extends Controller
{
    /**
     * Helper: base query scoped to this uni admin's university.
     */
    private function scopedUsers()
    {
        return User::where('university_id', auth()->user()->university_id)
            ->where('role', 'user'); // only students/teachers, not other admins
    }

    /**
     * List all users in this university with filter tabs.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending'); // default to pending tab

        $users = $this->scopedUsers()
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'      => $this->scopedUsers()->count(),
            'pending'  => $this->scopedUsers()->where('status', 'pending')->count(),
            'verified' => $this->scopedUsers()->where('status', 'verified')->count(),
            'rejected' => $this->scopedUsers()->where('status', 'rejected')->count(),
        ];

        return view('uni-admin.users.index', compact('users', 'status', 'counts'));
    }

    /**
     * Show a single user's details.
     */
    public function show(User $user): View
    {
        $this->authorizeUser($user);

        $user->load([
            'items',
            'transactionsAsBorrower.item',
            'transactionsAsOwner.item',
            'penalties.transaction.item',
            // ratingsReceived removed from here
        ]);

        $ratingsReceived = $user->ratingsReceived()->get();

        return view('uni-admin.users.show', compact('user', 'ratingsReceived'));
    }

    /**
     * Verify (approve) a pending user.
     */
    public function verify(User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        if (!$user->isPending()) {
            return back()->with('warning', 'User is not in pending status.');
        }

        $user->verify();

        return back()->with('success', "{$user->name} has been verified and can now access the platform.");
    }

    /**
     * Reject a pending user's registration.
     */
    public function reject(User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        if (!$user->isPending()) {
            return back()->with('warning', 'User is not in pending status.');
        }

        $user->reject();

        return back()->with('success', "{$user->name}'s registration has been rejected.");
    }

    /**
     * Suspend a verified user (set back to pending, blocking access).
     */
    public function suspend(User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        $user->update(['status' => 'pending']);

        return back()->with('success', "{$user->name} has been suspended and will need re-verification.");
    }

    /**
     * Ensure the target user belongs to this uni admin's university.
     * Prevents cross-university access.
     */
    private function authorizeUser(User $user): void
    {
        if ($user->university_id !== auth()->user()->university_id) {
            abort(403, 'You do not have permission to manage this user.');
        }
    }
}