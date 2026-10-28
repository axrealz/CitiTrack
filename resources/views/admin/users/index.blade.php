@extends('layouts.admin')

@section('title', 'Staff')

@section('content')

<x-page-header title="Staff" subtitle="Manage cashiers, stockers, and admins">
    <x-slot:actions>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">+ Add Staff</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <x-search-input name="q" placeholder="Search by name or email..." />
        </div>

        <x-filter-select
            name="role"
            :options="['admin' => 'Admin', 'cashier' => 'Cashier', 'stocker' => 'Stocker']"
            placeholder="All Roles"
        />

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Reset</a>
        </div>
    </div>
</form>

@if($users->isEmpty())
    <x-empty-state title="No staff found" message="Add your first staff account." />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Email</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Role</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $roleColors = ['admin' => 'indigo', 'cashier' => 'emerald', 'stocker' => 'sky'];
                            @endphp
                            <x-badge :color="$roleColors[$user->role] ?? 'slate'">
                                {{ ucfirst($user->role) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->is_active)
                                <x-badge color="green">Active</x-badge>
                            @else
                                <x-badge color="red">Inactive</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="text-sky-700 hover:text-sky-800 text-sm font-medium">View</a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium ml-3">Edit</a>
                            @if($user->id !== auth()->id())
                                <span class="ml-3">
                                    <x-delete-button :action="route('admin.users.destroy', $user)" entity="staff account" :subject="$user->name . ' (' . $user->email . ')'" confirm="This account will be removed and will no longer be able to log in." />
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$users" />
@endif

@endsection