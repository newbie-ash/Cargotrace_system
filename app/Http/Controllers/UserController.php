<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): Response
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->withCount('shipments')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $safeData = $user->makeHidden(['password', 'remember_token'])->toArray();
        $this->auditLogService->log('user.created', $user, null, $safeData);

        return redirect()->route('users.index')
            ->with('success', "User account for {$user->name} created successfully.");
    }

    public function show(User $user): Response
    {
        $user->loadCount('shipments');

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $oldValues = $user->makeHidden(['password', 'remember_token'])->toArray();
        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $newValues = $user->fresh()->makeHidden(['password', 'remember_token'])->toArray();
        $this->auditLogService->log('user.updated', $user, $oldValues, $newValues);

        return redirect()->route('users.index')
            ->with('success', "User account for {$user->name} updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->shipments()->exists()) {
            return back()->with('error', "Cannot delete user {$user->name} because active shipments are associated with this customer.");
        }

        $oldValues = $user->makeHidden(['password', 'remember_token'])->toArray();
        $user->delete();

        $this->auditLogService->log('user.deleted', $user, $oldValues, null);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} deleted successfully.");
    }
}
