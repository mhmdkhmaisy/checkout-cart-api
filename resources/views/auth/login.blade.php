@extends('layouts.public')

@section('title', 'Admin Login - Aragon RSPS')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12">
    <div class="glass-card w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/aragon_logo.png') }}" alt="Aragon RSPS" class="h-20 mx-auto mb-4">
            <h2 class="text-3xl font-bold mb-2" style="background: linear-gradient(135deg, var(--primary-bright) 0%, var(--accent-gold) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                Admin Login
            </h2>
            <p class="text-muted">Sign in to access the admin panel</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error mb-4">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-input @error('username') border-red-500 @enderror" 
                       value="{{ old('username') }}" 
                       required 
                       autofocus
                       placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input @error('password') border-red-500 @enderror" 
                       required
                       placeholder="Enter your password">
            </div>

            <div class="form-group">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="mr-2 h-4 w-4 rounded border-2 border-gray-600 bg-gray-900 text-red-600 focus:ring-red-500 focus:ring-offset-gray-900">
                    <span class="text-sm text-muted">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full mt-4">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>
    </div>
</div>
@endsection
