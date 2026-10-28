@extends('layouts.admin')

@section('title', 'Add Category')

@section('content')

<x-page-header title="Add Category" subtitle="Create a new product grouping" />

<form method="POST" action="{{ route('admin.categories.store') }}" class="max-w-2xl">
    @csrf

    <x-section-card title="Category Details">
        <div class="space-y-4">

            <x-form-input name="name" label="Category Name" :value="old('name')" required />

            <x-form-textarea name="description" label="Description (optional)" :value="old('description')" />

            <div>
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                           class="rounded border-slate-500 text-amber-600 focus:ring-amber-600">
                    <span class="ml-2 text-sm text-slate-700">Active</span>
                </label>
            </div>

        </div>
    </x-section-card>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="btn-primary">Save Category</button>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection