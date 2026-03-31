@extends('install.layout')

@section('content')
<div class="step-indicator">
    <div class="step completed">
        <div class="step-circle">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="step-text">Database</div>
    </div>
    <div class="step completed">
        <div class="step-circle">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="step-text">Setup</div>
    </div>
    <div class="step active">
        <div class="step-circle">3</div>
        <div class="step-text">Finish</div>
    </div>
</div>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold mb-4">Success!</h1>
    
    <p class="mb-4">Beulah Verification Suite has been installed. Thank you, and enjoy!</p>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <p class="text-sm text-blue-700">
            <strong>Important:</strong> Your admin login path is set to <code>/{{ config('app.admin_path', 'admin') }}</code>. Bookmark this URL!
        </p>
    </div>

    <table class="w-full text-left mt-6 mb-8 border-collapse">
        <tbody>
            <tr class="border-b border-gray-100">
                <th class="font-medium text-gray-900 py-3 w-1/3">Username</th>
                <td class="py-3 text-gray-700"><em>The username you chose</em></td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="font-medium text-gray-900 py-3">Password</th>
                <td class="py-3 text-gray-700"><em>Your chosen password</em></td>
            </tr>
            <tr>
                <th class="font-medium text-gray-900 py-3">Admin URL</th>
                <td class="py-3 text-gray-700"><code>{{ url('/' . config('app.admin_path', 'admin')) }}</code></td>
            </tr>
        </tbody>
    </table>

    <div class="mt-8 flex justify-end">
        <a href="{{ url('/' . config('app.admin_path', 'admin') . '/login') }}" class="wp-button text-base px-6 py-2">
            Log In
        </a>
    </div>
</div>
@endsection