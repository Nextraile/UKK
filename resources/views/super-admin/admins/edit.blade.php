@extends('layouts.admin')

@section('title', 'Edit Admin Account')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            Edit Admin Account: {{ $admin->first_name }} {{ $admin->last_name }}
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            Update administrator information. Email can be changed if requested.
        </p>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm font-medium text-red-800 mb-2">Please correct the following errors:</p>
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('super-admin.admins.update', $admin) }}" class="bg-white shadow rounded-lg p-6">
        @csrf
        @method('PATCH')

        {{-- First Name --}}
        <div class="mb-4">
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                First Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $admin->first_name) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 @enderror">
            @error('first_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Last Name --}}
        <div class="mb-4">
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                Last Name
            </label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $admin->last_name) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name') border-red-500 @enderror">
            @error('last_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-600">
                Email dapat diubah jika Admin meminta perubahan.
            </p>
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Role (Read-only) --}}
        <div class="mb-6">
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                Role
            </label>
            <input type="text" id="role" value="{{ ucfirst($admin->role) }}" disabled
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
            <p class="mt-1 text-xs text-gray-600">
                Role cannot be changed.
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('super-admin.admins.index') }}">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Update Admin Account
            </button>
        </div>
    </form>
</div>
@endsection
