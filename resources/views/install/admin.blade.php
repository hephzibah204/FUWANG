@extends('install.layout')

@section('content')
<div class="step-indicator">
    <a href="{{ route('install.index') }}" class="step completed cursor-pointer hover:opacity-80 transition-opacity">
        <div class="step-circle">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="step-text">Database</div>
    </a>
    <div class="step active">
        <div class="step-circle">2</div>
        <div class="step-text">Setup</div>
    </div>
    <div class="step cursor-not-allowed opacity-50">
        <div class="step-circle">3</div>
        <div class="step-text">Finish</div>
    </div>
</div>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold mb-4">Welcome</h1>
    <p class="mb-4">Welcome to the famous five-minute installation process! Just fill in the information below and you'll be on your way to using the most extendable and powerful verification platform.</p>

    <h2 class="text-xl font-medium mb-2 border-b pb-2">Information needed</h2>
    <p class="mb-6">Please provide the following information. Don't worry, you can always change these settings later.</p>

    <form class="space-y-6" action="{{ route('install.admin.store') }}" method="POST">
        @csrf
        
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                <label for="site_name" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Website Name</label>
                <div class="sm:col-span-2 relative group">
                    <input type="text" name="site_name" id="site_name" value="{{ old('site_name', 'Beulah Verification Suite') }}" required class="wp-input w-full">
                    <p class="text-xs text-gray-500 mt-1">The name of your application. This will be displayed to your users.</p>
                    @error('site_name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                <label for="admin_path" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Admin Login Path</label>
                <div class="sm:col-span-2 relative">
                    <div class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            {{ url('/') }}/
                        </span>
                        <input type="text" name="admin_path" id="admin_path" value="{{ old('admin_path', 'admin') }}" required class="wp-input flex-1 block w-full rounded-none rounded-r-md">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">For security, set a custom path for the admin panel (e.g. 'secure-admin', 'backend').</p>
                    @error('admin_path')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="my-6 border-gray-200">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                <label for="username" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Username</label>
                <div class="sm:col-span-2">
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required class="wp-input w-full">
                    <p class="text-xs text-gray-500 mt-1">Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.</p>
                    @error('username')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                <label for="password" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Password</label>
                <div class="sm:col-span-2">
                    <input type="password" name="password" id="password" required class="wp-input w-full">
                    <p class="text-xs text-gray-500 mt-1">Important: You will need this password to log in. Please store it in a secure location.</p>
                    @error('password')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                <label for="password_confirmation" class="text-sm font-medium text-gray-700 sm:text-right">Confirm Password</label>
                <div class="sm:col-span-2">
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="wp-input w-full">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                <label for="email" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Your Email</label>
                <div class="sm:col-span-2">
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="wp-input w-full">
                    <p class="text-xs text-gray-500 mt-1">Double-check your email address before continuing.</p>
                    @error('email')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="wp-button text-base px-6 py-2">
                Install Beulah Verification Suite
            </button>
        </div>
    </form>
</div>
@endsection