@extends('layouts.admin')
@section('title', $user->exists ? __('cms.edit_user') : __('cms.add_user'))
@section('heading', $user->exists ? __('cms.edit_user') : __('cms.add_user'))
@section('description', __('cms.access_note'))
@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="cms-card max-w-2xl space-y-6">
    @csrf
    @if($user->exists) @method('PUT') @endif
    @foreach(['name' => 'text', 'email' => 'email', 'password' => 'password', 'password_confirmation' => 'password'] as $field => $type)
        <div>
            <label for="user-{{ $field }}" class="mb-2 block text-sm font-semibold">{{ __('cms.'.$field) }}</label>
            <input id="user-{{ $field }}" name="{{ $field }}" type="{{ $type }}" class="cms-input" autocomplete="{{ $type === 'password' ? 'new-password' : $field }}"
                @if($type !== 'password') value="{{ old($field, $user->$field) }}" required maxlength="255" @else minlength="12" maxlength="255" @if(!$user->exists) required @endif @endif
                @if($errors->has($field)) aria-invalid="true" aria-describedby="error-{{ $field }}" @elseif($field === 'password') aria-describedby="password-help" @endif>
            @if($field === 'password')<p id="password-help" class="mt-2 text-xs text-muted">{{ __('cms.password_help') }} @if($user->exists){{ __('cms.password_optional') }}@endif</p>@endif
            @error($field)<p id="error-{{ $field }}" class="mt-2 text-sm text-danger">{{ $message }}</p>@enderror
        </div>
    @endforeach
    <div class="flex flex-wrap gap-3 border-t border-line pt-6">
        <button class="cms-button cms-button-primary">{{ $user->exists ? __('cms.save') : __('cms.create') }}</button>
        <a href="{{ route('admin.users.index') }}" class="cms-button">{{ __('cms.cancel') }}</a>
    </div>
</form>
@if($user->exists && auth()->id() !== $user->id)
    <section class="cms-card mt-6 max-w-2xl">
        <h2 class="text-base font-semibold">{{ __('cms.delete_user') }}</h2>
        <p class="mt-2 text-sm text-muted">{{ __('cms.delete_help') }}</p>
        <button type="button" hidden data-cms-delete-open aria-controls="delete-user-dialog" class="cms-button mt-4 text-danger">{{ __('cms.delete_user') }}</button>
        <dialog id="delete-user-dialog" class="cms-confirm-dialog" aria-labelledby="delete-user-title">
            <h2 id="delete-user-title" class="text-lg font-semibold">{{ __('cms.delete_confirm', ['email' => $user->email]) }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-muted">{{ __('cms.delete_help') }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <form method="dialog"><button class="cms-button">{{ __('cms.cancel') }}</button></form>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">@csrf @method('DELETE')<button class="cms-button text-danger">{{ __('cms.delete_user') }}</button></form>
            </div>
        </dialog>
        <noscript><details class="mt-4"><summary class="cms-button">{{ __('cms.delete_user') }}</summary><p class="my-4 text-sm">{{ __('cms.delete_confirm', ['email' => $user->email]) }}</p><form method="POST" action="{{ route('admin.users.destroy', $user) }}">@csrf @method('DELETE')<button class="cms-button text-danger">{{ __('cms.delete_user') }}</button></form></details></noscript>
    </section>
@endif
@endsection
