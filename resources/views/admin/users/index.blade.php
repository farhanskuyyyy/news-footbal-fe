@extends('layouts.admin')
@section('title', __('cms.users'))
@section('heading', __('cms.users'))
@section('description', __('cms.users_help'))
@section('actions')<a href="{{ route('admin.users.create') }}" class="cms-button cms-button-primary">{{ __('cms.add_user') }}</a>@endsection
@section('content')
<x-cms-search :label="__('cms.search_users')" :query="$query" />
<p class="mb-4 text-sm text-muted">{{ __('cms.access_note') }}</p>
<div class="cms-table-wrap" role="region" aria-label="{{ __('cms.users') }}" tabindex="0">
    <table class="cms-table">
        <thead><tr><th>{{ __('cms.name') }}</th><th>{{ __('cms.email') }}</th><th>{{ __('cms.created') }}</th><th>{{ __('cms.actions') }}</th></tr></thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <th scope="row">{{ $user->name }} @if(auth()->id() === $user->id)<span class="ml-2 text-xs font-normal text-muted">{{ __('cms.you') }}</span>@endif</th>
                    <td data-label="{{ __('cms.email') }}" class="break-all">{{ $user->email }}</td>
                    <td data-label="{{ __('cms.created') }}" class="whitespace-nowrap text-muted">{{ $user->created_at?->timezone('Asia/Jakarta')->format('d M Y') ?? __('cms.unknown') }}</td>
                    <td data-label="{{ __('cms.actions') }}"><a href="{{ route('admin.users.edit', $user) }}" class="cms-button" aria-label="{{ __('cms.edit_user') }}: {{ $user->name }}">{{ __('cms.edit') }}</a></td>
                </tr>
            @empty<tr><td colspan="4" class="text-muted">{{ __('cms.users_empty') }}</td></tr>@endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $users->links() }}</div>
@endsection
