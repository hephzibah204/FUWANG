@extends('install.layout')

@section('content')
<div class="step-indicator">
    <div class="step active">
        <div class="step-circle">1</div>
        <div class="step-text">Database</div>
    </div>
    <div class="step cursor-not-allowed opacity-50">
        <div class="step-circle">2</div>
        <div class="step-text">Setup</div>
    </div>
    <div class="step cursor-not-allowed opacity-50">
        <div class="step-circle">3</div>
        <div class="step-text">Finish</div>
    </div>
</div>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold mb-4">Database Setup</h1>
    <p class="mb-4">Before getting started, we need some information on the database. You will need to know the following items before proceeding.</p>

    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded text-sm text-blue-800">
        <p class="font-bold mb-2">🚀 Secure Setup (Recommended)</p>
        <p class="mb-2">For enhanced security, we recommend installing the application via the Command Line Interface (CLI) using:</p>
        <div class="bg-gray-900 text-green-400 p-3 rounded mb-3 font-mono">php artisan app:setup</div>
        <p class="mb-2">This is the most secure method for production environments as it avoids exposing installation routes to the public internet.</p>
        <ul class="list-disc list-inside space-y-1 ml-4 mt-2 text-blue-700">
            <li><strong>Database name:</strong> e.g., <code>username_dbname</code></li>
            <li><strong>Database username:</strong> e.g., <code>username_dbuser</code></li>
            <li><strong>Database password:</strong> The password you set when creating the database user</li>
            <li><strong>Database host:</strong> Often just <code>localhost</code> or <code>127.0.0.1</code></li>
        </ul>
    </div>

    <div>
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">System Requirements</h3>
        <ul class="space-y-2 mb-6">
            @foreach ($requirements as $requirement => $met)
                <li class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                    <span class="text-gray-700">{{ $requirement }}</span>
                    @if ($met)
                        <span class="text-green-600 font-bold">
                            OK
                        </span>
                    @else
                        <span class="text-red-600 font-bold">
                            Missing
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    @if ($allRequirementsMet)
        <p class="mb-6">We're going to use this information to create a <code>.env</code> file and setup your database. <strong>If for any reason this automatic file creation doesn't work, don't worry. All this does is fill in the database information to a configuration file.</strong></p>
        
        <form class="space-y-6" id="install-form" action="{{ route('install.database') }}" method="POST">
            @csrf
            
            <div id="form-steps" class="space-y-4">
                
                <!-- Step 1: Database Connection -->
                <div class="form-step active-step transition-opacity duration-300">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                        <label for="db_connection" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Database Connection</label>
                        <div class="sm:col-span-2 group relative">
                            <select id="db_connection" name="db_connection" class="wp-input w-full bg-white">
                                <option value="mysql">MySQL (Recommended)</option>
                                <option value="pgsql">PostgreSQL</option>
                                <option value="sqlite">SQLite</option>
                                <option value="sqlsrv">SQL Server</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select the type of database you are using. MySQL is the most common for web hosting.</p>
                            <p class="text-red-600 text-xs mt-1 hidden validation-error"></p>
                            @error('db_connection')
                                <p class="text-red-600 text-xs mt-1 server-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 2: Database Host -->
                <div class="form-step hidden opacity-0 transition-opacity duration-300">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                        <label for="db_host" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Database Host</label>
                        <div class="sm:col-span-2 group relative">
                            <input type="text" name="db_host" id="db_host" value="{{ old('db_host', '127.0.0.1') }}" class="wp-input w-full">
                            <p class="text-xs text-gray-500 mt-1">You should be able to get this info from your web host, if <code>127.0.0.1</code> or <code>localhost</code> doesn't work.</p>
                            <p class="text-red-600 text-xs mt-1 hidden validation-error"></p>
                            @error('db_host')
                                <p class="text-red-600 text-xs mt-1 server-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 3: Database Port -->
                <div class="form-step hidden opacity-0 transition-opacity duration-300">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                        <label for="db_port" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Database Port</label>
                        <div class="sm:col-span-2 group relative">
                            <input type="text" name="db_port" id="db_port" value="{{ old('db_port', '3306') }}" class="wp-input w-full">
                            <p class="text-xs text-gray-500 mt-1">The port your database is running on. Default for MySQL is <code>3306</code>.</p>
                            <p class="text-red-600 text-xs mt-1 hidden validation-error"></p>
                            @error('db_port')
                                <p class="text-red-600 text-xs mt-1 server-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 4: Database Name -->
                <div class="form-step hidden opacity-0 transition-opacity duration-300">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                        <label for="db_database" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Database Name</label>
                        <div class="sm:col-span-2 group relative">
                            <input type="text" name="db_database" id="db_database" value="{{ old('db_database', 'laravel') }}" class="wp-input w-full">
                            <p class="text-xs text-gray-500 mt-1">The name of the database you want to use with GSoft. If using SQLite, this is the file path (e.g., <code>database/database.sqlite</code>).</p>
                            <p class="text-red-600 text-xs mt-1 hidden validation-error"></p>
                            @error('db_database')
                                <p class="text-red-600 text-xs mt-1 server-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 5: Database Username -->
                <div class="form-step hidden opacity-0 transition-opacity duration-300">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                        <label for="db_username" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Username</label>
                        <div class="sm:col-span-2 group relative">
                            <input type="text" name="db_username" id="db_username" value="{{ old('db_username', 'root') }}" class="wp-input w-full">
                            <p class="text-xs text-gray-500 mt-1">Your database username. Often provided by your web host.</p>
                            <p class="text-red-600 text-xs mt-1 hidden validation-error"></p>
                            @error('db_username')
                                <p class="text-red-600 text-xs mt-1 server-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 6: Database Password -->
                <div class="form-step hidden opacity-0 transition-opacity duration-300">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                        <label for="db_password" class="text-sm font-medium text-gray-700 sm:text-right mt-2">Password</label>
                        <div class="sm:col-span-2 group relative">
                            <input type="password" name="db_password" id="db_password" class="wp-input w-full">
                            <p class="text-xs text-gray-500 mt-1">Your database password. Leave blank if your local setup doesn't require one (not recommended for production).</p>
                            <p class="text-red-600 text-xs mt-1 hidden validation-error"></p>
                            @error('db_password')
                                <p class="text-red-600 text-xs mt-1 server-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between items-center border-t border-gray-200 pt-6">
                <button type="button" id="prev-btn" class="wp-button bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 text-base px-6 py-2 hidden">
                    &laquo; Back
                </button>
                <div class="flex-1"></div> <!-- Spacer -->
                <button type="button" id="next-btn" class="wp-button text-base px-6 py-2">
                    Next &raquo;
                </button>
                <div id="submit-actions" class="hidden flex gap-4">
                    <button type="button" id="test-connection-btn" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Test Connection
                    </button>
                    <button type="submit" id="submit-btn" class="wp-button text-base px-6 py-2">
                        Install Database
                    </button>
                </div>
            </div>
            
            <div id="connection-result" class="hidden mt-4 p-3 rounded text-sm font-medium"></div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const steps = document.querySelectorAll('.form-step');
                const nextBtn = document.getElementById('next-btn');
                const prevBtn = document.getElementById('prev-btn');
                const submitActions = document.getElementById('submit-actions');
                let currentStep = 0;

                // Validation rules
                const validateStep = (stepIndex) => {
                    const step = steps[stepIndex];
                    const input = step.querySelector('input, select');
                    const errorMsg = step.querySelector('.validation-error');
                    let isValid = true;
                    
                    if (!input) return true; // Skip if no input found

                    // Clear previous errors
                    errorMsg.classList.add('hidden');
                    errorMsg.textContent = '';
                    input.classList.remove('border-red-500', 'ring-red-500');

                    const value = input.value.trim();
                    const dbConnection = document.getElementById('db_connection').value;

                    switch(input.name) {
                        case 'db_connection':
                            if (!value) {
                                isValid = false;
                                errorMsg.textContent = 'Please select a database connection.';
                            }
                            break;
                        case 'db_host':
                            if (dbConnection !== 'sqlite' && !value) {
                                isValid = false;
                                errorMsg.textContent = 'Database host is required for non-SQLite connections.';
                            }
                            break;
                        case 'db_port':
                            if (dbConnection !== 'sqlite' && !value) {
                                isValid = false;
                                errorMsg.textContent = 'Database port is required.';
                            } else if (value && isNaN(value)) {
                                isValid = false;
                                errorMsg.textContent = 'Port must be a number.';
                            }
                            break;
                        case 'db_database':
                            if (!value) {
                                isValid = false;
                                errorMsg.textContent = 'Database name (or path) is required.';
                            }
                            break;
                        case 'db_username':
                            if (dbConnection !== 'sqlite' && !value) {
                                isValid = false;
                                errorMsg.textContent = 'Database username is required.';
                            }
                            break;
                        case 'db_password':
                            // Password can often be blank in local dev, so we don't strictly require it
                            break;
                    }

                    if (!isValid) {
                        errorMsg.classList.remove('hidden');
                        input.classList.add('border-red-500', 'ring-red-500');
                    }

                    return isValid;
                };

                // Handle input changes for real-time validation
                steps.forEach((step, index) => {
                    const input = step.querySelector('input, select');
                    if (input) {
                        input.addEventListener('input', () => {
                            // Hide server errors when user starts typing
                            const serverError = step.querySelector('.server-error');
                            if (serverError) serverError.classList.add('hidden');
                            
                            const isValid = validateStep(index);
                            nextBtn.disabled = !isValid;
                        });

                        // Special case for connection change
                        if (input.name === 'db_connection') {
                            input.addEventListener('change', () => {
                                const isSqlite = input.value === 'sqlite';
                                // Skip host, port, user, pass if sqlite is selected
                                if (isSqlite && currentStep === 0) {
                                    // Update visual cues that steps will be skipped
                                }
                            });
                        }
                    }
                });

                // Navigation logic
                const updateUI = () => {
                    // Handle SQLite skipping
                    const isSqlite = document.getElementById('db_connection').value === 'sqlite';
                    
                    steps.forEach((step, index) => {
                        if (index === currentStep) {
                            step.classList.remove('hidden');
                            // Small delay for transition
                            setTimeout(() => {
                                step.classList.remove('opacity-0');
                                step.classList.add('opacity-100');
                            }, 50);
                            
                            // Focus input
                            const input = step.querySelector('input, select');
                            if (input) input.focus();
                        } else {
                            step.classList.add('opacity-0');
                            step.classList.remove('opacity-100');
                            setTimeout(() => {
                                step.classList.add('hidden');
                            }, 300); // match transition duration
                        }
                    });

                    // Update buttons
                    prevBtn.classList.toggle('hidden', currentStep === 0);
                    
                    if (currentStep === steps.length - 1 || (isSqlite && currentStep === 3)) { // Step 3 is db_database (index 3)
                        nextBtn.classList.add('hidden');
                        submitActions.classList.remove('hidden');
                        submitActions.classList.add('flex');
                    } else {
                        nextBtn.classList.remove('hidden');
                        submitActions.classList.add('hidden');
                        submitActions.classList.remove('flex');
                    }

                    // Initial validation for the new step
                    nextBtn.disabled = !validateStep(currentStep);
                };

                nextBtn.addEventListener('click', () => {
                    if (validateStep(currentStep)) {
                        const isSqlite = document.getElementById('db_connection').value === 'sqlite';
                        
                        if (currentStep === 0 && isSqlite) {
                            // Skip to database name step if SQLite
                            currentStep = 3;
                        } else if (currentStep < steps.length - 1) {
                            currentStep++;
                        }
                        updateUI();
                    }
                });

                prevBtn.addEventListener('click', () => {
                    const isSqlite = document.getElementById('db_connection').value === 'sqlite';
                    
                    if (currentStep === 3 && isSqlite) {
                        // Go back to connection step if SQLite
                        currentStep = 0;
                    } else if (currentStep > 0) {
                        currentStep--;
                    }
                    updateUI();
                });

                // Enter key to go next
                document.getElementById('install-form').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); // Prevent default form submission
                        
                        if (!nextBtn.classList.contains('hidden') && !nextBtn.disabled) {
                            nextBtn.click();
                        } else if (!submitActions.classList.contains('hidden')) {
                            // Only trigger test connection if on final step and pressing enter
                            document.getElementById('test-connection-btn').click();
                        }
                    }
                });

                // Initialize UI
                updateUI();
            });

            // Test Connection AJAX
            document.getElementById('test-connection-btn').addEventListener('click', async function() {
                const btn = this;
                const submitBtn = document.getElementById('submit-btn');
                const resultDiv = document.getElementById('connection-result');
                const form = document.getElementById('install-form');
                
                btn.disabled = true;
                submitBtn.disabled = true;
                btn.innerHTML = 'Testing...';
                resultDiv.className = 'mt-4 p-3 rounded text-sm font-medium bg-gray-100 text-gray-700';
                resultDiv.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Connecting to database...';
                resultDiv.classList.remove('hidden');

                const formData = new FormData(form);
                formData.append('_test_only', 'true');

                try {
                    const response = await fetch('{{ route("install.database") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        resultDiv.className = 'mt-4 p-3 rounded text-sm font-medium bg-green-50 border border-green-200 text-green-700';
                        resultDiv.innerHTML = '✅ Connection successful! You can now proceed.';
                        submitBtn.disabled = false; // Enable submit only on success
                    } else {
                        throw new Error(data.message || 'Connection failed');
                    }
                } catch (error) {
                    resultDiv.className = 'mt-4 p-3 rounded text-sm font-medium bg-red-50 border border-red-200 text-red-700';
                    resultDiv.innerHTML = '❌ ' + error.message;
                    submitBtn.disabled = true; // Keep disabled on error
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = 'Test Connection';
                }
            });
            
            // Handle actual form submission loading state
            document.getElementById('install-form').addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submit-btn');
                const testBtn = document.getElementById('test-connection-btn');
                const resultDiv = document.getElementById('connection-result');
                
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }
                
                submitBtn.disabled = true;
                testBtn.disabled = true;
                
                resultDiv.className = 'mt-4 p-3 rounded text-sm font-medium bg-blue-50 border border-blue-200 text-blue-700';
                resultDiv.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-700 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Installing database and importing schema. This may take a few moments...';
                resultDiv.classList.remove('hidden');

                submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Installing...';
            });
        </script>
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <p class="text-sm text-yellow-700 font-medium">
                Please resolve the system requirements above before continuing with the installation. Refresh the page once resolved.
            </p>
        </div>
    @endif
</div>
@endsection