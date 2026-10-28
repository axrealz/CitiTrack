@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')

<x-page-header title="Edit Category" :subtitle="$category->name" />

<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="max-w-2xl">
    @csrf
    @method('PUT')

    <x-section-card title="Category Details">
        <div class="space-y-4">

            <x-form-input name="name" label="Category Name" :value="old('name', $category->name)" required />

            <x-form-textarea name="description" label="Description (optional)" :value="old('description', $category->description)" />

            <div>
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))
                           class="rounded border-slate-500 text-amber-600 focus:ring-amber-600">
                    <span class="ml-2 text-sm text-slate-700">Active</span>
                </label>
            </div>

        </div>
    </x-section-card>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="btn-primary">Update Category</button>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection