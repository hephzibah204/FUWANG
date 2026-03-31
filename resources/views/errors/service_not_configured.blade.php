<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Service Not Configured - The requested API service is currently unavailable.">
    <title>Service Not Configured - FUWA</title>
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f9fafb;
            --text-main: #111827;
            --text-muted: #6b7280;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        .container {
            max-width: 32rem;
            width: 100%;
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2.5rem 2rem;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .icon-container {
            width: 4rem;
            height: 4rem;
            background-color: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .icon-container svg {
            width: 2rem;
            height: 2rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        p {
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 480px) {
            .actions {
                flex-direction: row;
                justify-content: center;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: white;
            color: var(--text-main);
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--bg-color);
        }

        .reference {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .reference-id {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background-color: var(--bg-color);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-labelledby="error-title">
        <div class="icon-container" aria-hidden="true">
            <svg xmlns="http://www.w3.org/-2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        
        <h1 id="error-title">Service Not Configured</h1>
        
        <p>{{ $message ?? 'The requested service is currently unavailable or has not been configured. Please try again later or contact support if the issue persists.' }}</p>
        
        <div class="actions">
            <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Return Home</a>
        </div>

        @if(isset($reference_id))
        <div class="reference">
            <span>Error Reference ID:</span>
            <span class="reference-id">{{ $reference_id }}</span>
        </div>
        @endif
    </div>
</body>
</html>
