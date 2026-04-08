@extends('layouts.admin')

@section('title', 'Advanced Settings')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Advanced Settings</h3>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.advanced_settings.update') }}">
            @csrf
            @method('PUT')

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settings as $setting)
                        <tr>
                            <td>{{ $setting->key }}</td>
                            <td>
                                <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
@endsection
