<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Digital Claim System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="sidebar w-64 min-h-screen">
                <div class="text-white mb-8">
                    <h1 class="text-2xl font-bold tracking-tight">DIGITAL<br/>CLAIM</h1>
                    <p class="text-white/90 text-sm mt-2 font-medium">Management System</p>
                </div>

                <!-- Navigation -->
                <nav>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="nav-link {{ request()->routeIs('dashboard') ? 'active text-white' : 'text-white/80 hover:text-white' }} block">
                                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                                Dashboard
                            </a>
                        </li>

                        @auth
                            <li>
                                <a href="{{ route('profile.edit') }}"
                                   class="nav-link {{ request()->routeIs('profile.*') ? 'active text-white' : 'text-white/80 hover:text-white' }} block">
                                    <i class="fas fa-user-cog w-5 mr-3"></i>
                                    Profile Settings
                                </a>
                            </li>
                            @if (auth()->user()->isFinanceAdmin())
                                <li>
                                    <a href="{{ route('admin.users.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.users.*') ? 'active text-white' : 'text-white/80 hover:text-white' }} block">
                                        <i class="fas fa-users w-5 mr-3"></i>
                                        Employees
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.claims.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.claims.*') ? 'active text-white' : 'text-white/80 hover:text-white' }} block">
                                        <i class="fas fa-file-invoice-dollar w-5 mr-3"></i>
                                        Claims
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('employee.claims.index') }}"
                                       class="nav-link {{ request()->routeIs('employee.claims.*') ? 'active text-white' : 'text-white/80 hover:text-white' }} block">
                                        <i class="fas fa-file-invoice w-5 mr-3"></i>
                                        My Claims
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('employee.claims.create') }}"
                                       class="nav-link {{ request()->routeIs('employee.claims.create') ? 'active text-white' : 'text-white/80 hover:text-white' }} block">
                                        <i class="fas fa-plus-circle w-5 mr-3"></i>
                                        New Claim
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>
                </nav>

                <!-- User Info -->
                <div class="absolute bottom-8 left-8 right-8">
                    <div class="bg-white/10 backdrop-filter backdrop-blur-sm rounded-lg p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">
                                    {{ Auth::user()->name }}
                                </p>
                                <p class="text-xs text-white/70 truncate">
                                    {{ Auth::user()->role->display_name }}
                                </p>
                            </div>
                        </div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="w-full btn btn-ghost text-white/80 hover:text-white border-white/20 text-sm">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-8 overflow-y-auto">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 fade-in-down">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 fade-in-down">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-6 fade-in-down">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ session('warning') }}
                        </div>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="mb-6 fade-in-down">
                        <div class="alert alert-danger">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                                <div>
                                    <strong>Please fix the following errors:</strong>
                                    <ul class="mt-2 ml-4 list-disc">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Page Heading -->
                @isset($header)
                    <header class="mb-8 fade-in-down">
                        <div class="glass-card">
                            <div class="p-6">
                                {{ $header }}
                            </div>
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <div class="fade-in">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Modal Stack -->
        @stack('modals')

        <!-- Script Stack -->
        @stack('scripts')
    </body>
</html>
