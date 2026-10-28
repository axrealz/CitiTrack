@extends('layouts.admin')

@section('title', 'Categories')

@section('content')

<x-page-header title="Categories" subtitle="Organize your products">
    <x-slot:actions>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ Add Category</a>
    </x-slot:actions>
</x-page-header>

@if($categories->isEmpty())
    <x-empty-state title="No categories yet" message="Create your first category to group products.">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ Add Category</a>
    </x-empty-state>
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Products</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($categories as $category)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 max-w-xs truncate">
                            {{ $category->description ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <x-badge color="indigo">{{ $category->products_count }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($category->is_active)
                                <x-badge color="green">Active</x-badge>
                            @else
                                <x-badge color="slate">Inactive</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</a>
                            <span class="ml-3">
                                <x-delete-button :action="route('admin.categories.destroy', $category)" entity="category" :subject="$category->name" />
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$categories" />
@endif

@endsection