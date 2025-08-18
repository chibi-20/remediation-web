<?php
// debug/index.php - Debug & Development Tools Directory
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug & Development Tools - Remediation Web</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .hover-lift { transition: transform 0.2s; }
        .hover-lift:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="gradient-bg text-white py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold mb-2">🛠️ Debug & Development Tools</h1>
            <p class="text-blue-100">Development utilities and testing tools for Remediation Web System</p>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- Navigation Back -->
        <div class="mb-6">
            <a href="../" class="inline-flex items-center space-x-2 text-blue-600 hover:text-blue-800 transition-colors">
                <span>← Back to Main Application</span>
            </a>
        </div>

        <!-- Critical Tools -->
        <div class="bg-white rounded-xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                <span>🚨</span>
                <span>Critical System Tools</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="fix-database-connection.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Database Connection Fix</h3>
                    <p class="text-sm text-gray-600 mt-1">Diagnose and fix database connectivity issues</p>
                </a>
                <a href="auto-fix-php-extensions.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Auto-Fix PHP Extensions</h3>
                    <p class="text-sm text-gray-600 mt-1">Automatically enable MySQL extensions</p>
                </a>
                <a href="prepare-for-production.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Production Readiness</h3>
                    <p class="text-sm text-gray-600 mt-1">Check if system is ready for deployment</p>
                </a>
            </div>
        </div>

        <!-- Database Tools -->
        <div class="bg-white rounded-xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                <span>💾</span>
                <span>Database Tools</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="check-database.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Database Status</h3>
                    <p class="text-sm text-gray-600 mt-1">Check database connection and tables</p>
                </a>
                <a href="check-sqlite.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">SQLite Inspector</h3>
                    <p class="text-sm text-gray-600 mt-1">Inspect contents of students.db file</p>
                </a>
                <a href="mysql-status.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">MySQL Status</h3>
                    <p class="text-sm text-gray-600 mt-1">Check MySQL server status</p>
                </a>
                <a href="migrate-db.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Database Migration</h3>
                    <p class="text-sm text-gray-600 mt-1">Run database migrations</p>
                </a>
                <a href="migrate-teachers-db.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Teachers Migration</h3>
                    <p class="text-sm text-gray-600 mt-1">Migrate teacher data structure</p>
                </a>
                <a href="update-admins-table.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Update Admins Table</h3>
                    <p class="text-sm text-gray-600 mt-1">Update admin table structure</p>
                </a>
            </div>
        </div>

        <!-- Security & Testing -->
        <div class="bg-white rounded-xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                <span>🔒</span>
                <span>Security & Testing Tools</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="test-security-isolation.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Security Isolation Test</h3>
                    <p class="text-sm text-gray-600 mt-1">Test teacher-student data isolation</p>
                </a>
                <a href="test-admins-api.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Admin API Test</h3>
                    <p class="text-sm text-gray-600 mt-1">Test admin-related API endpoints</p>
                </a>
                <a href="simple-admins-test.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Simple Admin Test</h3>
                    <p class="text-sm text-gray-600 mt-1">Basic admin functionality test</p>
                </a>
            </div>
        </div>

        <!-- Debug & Development -->
        <div class="bg-white rounded-xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                <span>🐛</span>
                <span>Debug & Development</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="debug.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">System Debug</h3>
                    <p class="text-sm text-gray-600 mt-1">General system debugging information</p>
                </a>
                <a href="debug-dashboard.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Dashboard Debug</h3>
                    <p class="text-sm text-gray-600 mt-1">Debug dashboard-specific issues</p>
                </a>
                <a href="debug-routes.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Routes Debug</h3>
                    <p class="text-sm text-gray-600 mt-1">Debug API routing issues</p>
                </a>
                <a href="phpinfo.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">PHP Info</h3>
                    <p class="text-sm text-gray-600 mt-1">View PHP configuration</p>
                </a>
                <a href="check-php.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">PHP Check</h3>
                    <p class="text-sm text-gray-600 mt-1">Check PHP environment</p>
                </a>
                <a href="direct-test.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Direct Test</h3>
                    <p class="text-sm text-gray-600 mt-1">Direct system testing</p>
                </a>
            </div>
        </div>

        <!-- General Tests -->
        <div class="bg-white rounded-xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                <span>🧪</span>
                <span>General Tests</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="test.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">General Test</h3>
                    <p class="text-sm text-gray-600 mt-1">General system testing</p>
                </a>
                <a href="test-routes.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Routes Test</h3>
                    <p class="text-sm text-gray-600 mt-1">Test API route functionality</p>
                </a>
                <a href="api-fix-summary.php" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">API Fix Summary</h3>
                    <p class="text-sm text-gray-600 mt-1">Summary of API fixes applied</p>
                </a>
                <a href="test-output.html" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover-lift transition-all">
                    <h3 class="font-medium text-gray-900">Test Output</h3>
                    <p class="text-sm text-gray-600 mt-1">View saved test output</p>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-blue-50 rounded-xl p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                <span>⚡</span>
                <span>Quick Actions</span>
            </h2>
            <div class="flex flex-wrap gap-3">
                <a href="../switch-database.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Switch Database</a>
                <a href="../public/admin-login.html" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">Admin Login</a>
                <a href="../public/admin-register.html" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Admin Register</a>
                <a href="../README.md" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">Documentation</a>
            </div>
        </div>

        <!-- Warning for Production -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mt-8">
            <div class="flex items-start space-x-3">
                <span class="text-yellow-500 text-xl">⚠️</span>
                <div>
                    <h3 class="font-medium text-yellow-800">Production Warning</h3>
                    <p class="text-yellow-700 mt-1">These debug tools should be removed or secured before production deployment. They contain sensitive system information and testing capabilities.</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
