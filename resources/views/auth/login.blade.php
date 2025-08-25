<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Printer System') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        accent: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-bg-alt {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .input-focus {
            transition: all 0.3s ease;
        }
        
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-hover {
            transition: all 0.3s ease;
        }
        
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .floating-shapes::before,
        .floating-shapes::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-shapes::before {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation-delay: -2s;
        }
        
        .floating-shapes::after {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: -4s;
        }
    </style>
</head>
<body class="font-sans antialiased gradient-bg min-h-screen relative overflow-hidden">
    <div class="floating-shapes"></div>
    
    <div class="min-h-screen flex items-center justify-center px-4 py-8 relative z-10">
        <div class="w-full max-w-md animate-slide-up">
            <!-- Logo and Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl mb-4 animate-float">
                    <i class="fas fa-print text-2xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Welcome Back</h1>
                <p class="text-white/80 text-lg">Sign in to your admin account</p>
            </div>
            
            <!-- Login Card -->
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20">
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <p class="text-green-800 font-medium">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-red-800 font-medium mb-2">{{ __('Please fix the following errors:') }}</p>
                                <ul class="text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li class="flex items-center">
                                            <i class="fas fa-dot-circle text-xs mr-2"></i>
                                            {{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6" x-data="{ showPassword: false }">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus 
                                   class="input-focus block w-full pl-12 pr-4 py-4 border border-gray-200 rounded-2xl bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 text-gray-900 placeholder-gray-500 transition-all duration-300"
                                   placeholder="Enter your email address">
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required 
                                   class="input-focus block w-full pl-12 pr-12 py-4 border border-gray-200 rounded-2xl bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 text-gray-900 placeholder-gray-500 transition-all duration-300"
                                   placeholder="Enter your password">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Remember Me and Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox" 
                                   class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded-lg">
                            <span class="ml-3 text-sm font-medium text-gray-700">Remember me</span>
                        </label>
                        
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    
                    <!-- Sign In Button -->
                    <button type="submit" class="btn-hover w-full py-4 px-6 gradient-bg text-white font-semibold rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/20 transition-all duration-300">
                        <span class="flex items-center justify-center">
                            <i class="fas fa-sign-in-alt mr-3"></i>
                            Sign In
                        </span>
                    </button>
                </form>
                
                <!-- Register Link -->
                @if (Route::has('register'))
                    <div class="mt-8 text-center">
                        <p class="text-gray-600">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                                Create one now
                            </a>
                        </p>
                    </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-white/80 text-sm">
                    &copy; {{ date('Y') }} CPrint Pro System. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
