<x-base-layout 
    title="Create Admin Account - Super Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Create Admin Account">
    
<div class="container mx-auto px-4 py-6 max-w-2xl">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Admin Account</h1>
        <p class="mt-1 text-sm text-gray-600">
            Create new administrator account. Credentials will be sent via email.
        </p>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-4 p-4 bg-error-50 border border-error-200 rounded-lg">
            <p class="text-sm font-medium text-error-800 mb-2">Please correct the following errors:</p>
            <ul class="list-disc list-inside text-sm text-error-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Create Form --}}
    <form method="POST" action="{{ route('super-admin.admins.store') }}" class="bg-white shadow rounded-lg p-6">
        @csrf

        {{-- First Name --}}
        <div class="mb-4">
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                First Name <span class="text-error-500">*</span>
            </label>
            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 @error('first_name') border-error-500 @enderror">
            @error('first_name')
                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Last Name --}}
        <div class="mb-4">
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                Last Name
            </label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 @error('last_name') border-error-500 @enderror">
            @error('last_name')
                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-error-500">*</span>
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 @error('email') border-error-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                Nomor Telepon <span class="text-error-500">*</span>
            </label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                   placeholder="08123456789" maxlength="13"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 @error('phone') border-error-500 @enderror">
            <p class="mt-1 text-xs text-gray-600">Format: 08******** (10-13 digit)</p>
            @error('phone')
                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-6" x-data="{ showPassword: false }">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                Password <span class="text-error-500">*</span>
            </label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" value="{{ old('password') }}" required
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 @error('password') border-error-500 @enderror">
                <button type="button" 
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-600">
                Password ini akan dikirim ke Admin via email. Sarankan Admin mengganti password setelah login pertama.
            </p>
            @error('password')
                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('super-admin.admins.index') }}">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
            </a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                Create Admin Account
            </button>
        </div>
    </form>
</div>
</x-base-layout>
