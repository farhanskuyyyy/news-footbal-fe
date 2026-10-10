<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $query = trim((string) $request->query('q', ''));
        $users = User::query()->when($query !== '', fn ($builder) => $builder->where(fn ($search) => $search
            ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($query).'%'])
            ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($query).'%'])))
            ->orderBy('name')->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'query'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return redirect()->route('admin.users.index')->with('status', __('cms.user_created'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $user->remember_token = Str::random(60);
        }
        if ($user->email !== $data['email']) {
            $user->email_verified_at = null;
        }
        $user->fill($data)->save();

        return redirect()->route('admin.users.index')->with('status', __('cms.user_updated'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', __('cms.self_delete'));
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', __('cms.user_deleted'));
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:12', 'max:255', 'confirmed'],
        ]);
    }
}
