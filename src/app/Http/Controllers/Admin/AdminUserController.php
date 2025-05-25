<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'admin.role:manager']);
    }

    public function index(Request $request)
    {
        $query = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->input('role'), function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->input('status') !== null, function ($query) use ($request) {
                $query->where('is_active', $request->input('status'));
            });

        $users = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/AdminUsers/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/AdminUsers/Create', [
            'roles' => [
                'manager' => 'Manager',
                'corrector' => 'Corrector',
                'general' => 'General Admin',
            ],
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        Log::info('Admin user created', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/AdminUsers/Edit', [
            'user' => $user,
            'roles' => [
                'manager' => 'Manager',
                'corrector' => 'Corrector',
                'general' => 'General Admin',
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Only update password if provided
        if (empty($data['password'])) {
            unset($data['password']);
            unset($data['password_confirmation']);
        }

        $user->update($data);

        Log::info('Admin user updated', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'updated_fields' => array_keys($data),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        Log::info('Admin user deleted', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        return back()->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        Log::info('Admin user status toggled', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'new_status' => $user->is_active ? 'active' : 'inactive',
        ]);

        return back()->with('success', 'User status updated successfully.');
    }
} 