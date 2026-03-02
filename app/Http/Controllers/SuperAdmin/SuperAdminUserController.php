<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminUserController extends Controller
{
    /**
     * List all users across the entire platform.
     */
    public function index(Request $request): View
    {
        $role   = $request->get('role', 'user');
        $status = $request->get('status', 'all');

        $users = User::with('university')
            ->when($role !== 'all', fn($q) => $q->where('role', $role))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($request->university, fn($q) => $q->where('university_id', $request->university))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'total'      => User::where('role', 'user')->count(),
            'verified'   => User::where('role', 'user')->where('status', 'verified')->count(),
            'pending'    => User::where('role', 'user')->where('status', 'pending')->count(),
            'uni_admins' => User::where('role', 'uni_admin')->count(),
        ];

        return view('super-admin.users.index', compact('users', 'counts', 'role', 'status'));
    }

    /**
     * Show a single user's full platform profile.
     */
    public function show(User $user): View
    {
        // Super admin cannot view themselves here
        if ($user->isSuperAdmin()) {
            abort(403);
        }

        $user->load([
            'university',
            'items',
            'transactionsAsBorrower.item',
            'transactionsAsOwner.item',
            'penalties.transaction.item',
        ]);

        return view('super-admin.users.show', compact('user'));
    }

    /**
     * Suspend a user globally (set status to pending, blocks login).
     */
    public function suspend(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            abort(403, 'Cannot suspend a super admin.');
        }

        $user->update(['status' => 'pending']);

        return back()->with('success', "{$user->name} has been suspended.");
    }

    /**
     * Permanently delete a user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            abort(403, 'Cannot delete a super admin.');
        }

        // Block deletion if user has active transactions
        if ($user->transactionsAsBorrower()->whereIn('status', ['active', 'pending'])->exists()) {
            return back()->with('warning', 'Cannot delete a user with active transactions.');
        }

        $user->delete();

        return redirect()->route('super-admin.users.index')
            ->with('success', "User {$user->name} has been permanently removed.");
    }
}