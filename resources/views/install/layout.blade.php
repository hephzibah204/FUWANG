<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beulah Verification Suite - Installation Process</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f1f1f1;
            color: #3c434a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        .wp-box {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            border-radius: 8px;
        }
        .wp-input {
            border: 1px solid #8c8f94;
            border-radius: 4px;
            padding: 0 8px;
            min-height: 40px;
            box-shadow: 0 0 0 transparent;
            transition: box-shadow .1s linear;
        }
        .wp-input:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        .wp-button {
            background: #2271b1;
            border-color: #2271b1;
            color: #fff;
            text-decoration: none;
            text-shadow: none;
            cursor: pointer;
            border-width: 1px;
            border-style: solid;
            border-radius: 4px;
            white-space: nowrap;
            box-sizing: border-box;
            padding: 0 10px;
            min-height: 32px;
            line-height: 2.30769231;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .wp-button:hover {
            background: #135e96;
            border-color: #135e96;
            color: #fff;
        }
        .wp-button:disabled {
            background: #a7aaad;
            border-color: #a7aaad;
            color: #dcdcde;
            cursor: not-allowed;
        }
        .wp-logo {
            width: auto;
            max-width: 250px;
            margin: 0 auto 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #2271b1;
            font-weight: bold;
        }
        .wp-logo-icon {
            width: 64px;
            height: 64px;
            background: #2271b1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin-bottom: 12px;
            box-shadow: 0 4px 6px rgba(34, 113, 177, 0.2);
        }
        .wp-logo-text {
            font-size: 24px;
            color: #1d2327;
            text-align: center;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 30px;
            right: 30px;
            height: 2px;
            background: #e5e5e5;
            z-index: 1;
        }
        .step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 33.33%;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #c3c4c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #646970;
            margin-bottom: 8px;
            transition: all 0.3s;
        }
        .step.active .step-circle {
            border-color: #2271b1;
            background: #2271b1;
            color: white;
        }
        .step.completed .step-circle {
            border-color: #2271b1;
            background: #fff;
            color: #2271b1;
        }
        .step-text {
            font-size: 12px;
            color: #646970;
            font-weight: 500;
        }
        .step.active .step-text {
            color: #2271b1;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        
        <div class="wp-logo">
            <div class="wp-logo-icon">B</div>
            <div class="wp-logo-text">Beulah Verification Suite</div>
        </div>

        <div class="wp-box p-8 sm:p-10 text-base leading-relaxed">
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('install_log'))
                <div class="bg-gray-50 border border-gray-200 p-4 mb-6 rounded">
                    <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ session('install_log') }}</pre>
                </div>
            @endif

            @yield('content')
        </div>
        
        <div class="text-center mt-6 text-sm text-gray-500">
            <a href="https://beulah.com" target="_blank" class="hover:text-gray-900 transition">Beulah Verification Suite</a>
        </div>
    </div>
</body>
</html>
