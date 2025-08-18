<?php
// prepare-for-production.php - Production deployment preparation
echo "<h1>🚀 Production Deployment Preparation</h1>";

echo "<h2>📋 Pre-Production Checklist</h2>";

$checks = [
    'Database Connection' => false,
    'File Permissions' => false,
    'Security Settings' => false,
    'Debug Files Removed' => false,
    'Error Logging' => false
];

// Check 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once 'config.php';
    if (isset($pdo) && $pdo) {
        $pdo->query("SELECT 1");
        echo "<p style='color: green;'>✅ Database connection successful</p>";
        $checks['Database Connection'] = true;
        
        // Check tables exist
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $requiredTables = ['admins', 'students', 'modules', 'assessments'];
        $missingTables = array_diff($requiredTables, $tables);
        
        if (empty($missingTables)) {
            echo "<p style='color: green;'>✅ All required tables exist</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Missing tables: " . implode(', ', $missingTables) . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>💡 Fix Database Issues:</h4>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Start MySQL service</li>";
    echo "<li>Check PHP extensions (mysqli, pdo_mysql)</li>";
    echo "<li>Verify database credentials in config.php</li>";
    echo "</ol>";
    echo "</div>";
}

// Check 2: File Permissions
echo "<h3>2. File Permissions & Security</h3>";
$uploadDir = 'public/modules/';
if (is_dir($uploadDir) && is_writable($uploadDir)) {
    echo "<p style='color: green;'>✅ Upload directory is writable</p>";
    $checks['File Permissions'] = true;
} else {
    echo "<p style='color: red;'>❌ Upload directory not writable</p>";
}

// Check 3: Debug Files (should be removed for production)
echo "<h3>3. Debug & Test Files</h3>";
$debugFiles = [
    'debug.php', 'test.php', 'phpinfo.php', 'debug-dashboard.php',
    'test-security-isolation.php', 'check-sqlite.php', 'simple-admins-test.php'
];

$foundDebugFiles = [];
foreach ($debugFiles as $file) {
    if (file_exists($file)) {
        $foundDebugFiles[] = $file;
    }
}

if (empty($foundDebugFiles)) {
    echo "<p style='color: green;'>✅ No debug files found</p>";
    $checks['Debug Files Removed'] = true;
} else {
    echo "<p style='color: orange;'>⚠️ Debug files found (remove for production):</p>";
    echo "<ul>";
    foreach ($foundDebugFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

// Check 4: Security Configuration
echo "<h3>4. Security Configuration</h3>";
$securityIssues = [];

// Check if display_errors is off (should be for production)
if (ini_get('display_errors')) {
    $securityIssues[] = "display_errors is ON (should be OFF for production)";
}

// Check if error_reporting is appropriate
$errorLevel = error_reporting();
if ($errorLevel === E_ALL) {
    $securityIssues[] = "error_reporting shows all errors (should be limited for production)";
}

if (empty($securityIssues)) {
    echo "<p style='color: green;'>✅ Basic security settings OK</p>";
    $checks['Security Settings'] = true;
} else {
    echo "<p style='color: orange;'>⚠️ Security recommendations:</p>";
    echo "<ul>";
    foreach ($securityIssues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
}

// Check 5: Required Features Test
echo "<h3>5. Core Features Quick Test</h3>";
echo "<ul>";
echo "<li><a href='public/admin-register.html' target='_blank'>Test Admin Registration</a></li>";
echo "<li><a href='public/admin-login.html' target='_blank'>Test Admin Login</a></li>";
echo "<li><a href='public/register.html' target='_blank'>Test Student Registration</a></li>";
echo "<li><a href='public/login.html' target='_blank'>Test Student Login</a></li>";
echo "</ul>";

// Overall Assessment
echo "<h2>🎯 Overall Production Readiness</h2>";
$passedChecks = array_sum($checks);
$totalChecks = count($checks);
$readinessPercentage = round(($passedChecks / $totalChecks) * 100);

echo "<div style='background: " . ($readinessPercentage >= 80 ? "#d4edda" : ($readinessPercentage >= 60 ? "#fff3cd" : "#f8d7da")) . "; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Readiness Score: $readinessPercentage% ($passedChecks/$totalChecks checks passed)</h3>";

if ($readinessPercentage >= 80) {
    echo "<p><strong>🟢 READY for staging/pilot deployment</strong></p>";
    echo "<p>Minor issues can be addressed during testing.</p>";
} elseif ($readinessPercentage >= 60) {
    echo "<p><strong>🟡 PARTIALLY READY</strong></p>";
    echo "<p>Address critical issues before production deployment.</p>";
} else {
    echo "<p><strong>🔴 NOT READY for production</strong></p>";
    echo "<p>Critical issues must be fixed first.</p>";
}
echo "</div>";

// Next Steps
echo "<h2>📋 Immediate Next Steps</h2>";
echo "<ol>";
if (!$checks['Database Connection']) {
    echo "<li><strong>CRITICAL:</strong> Fix database connection issues</li>";
}
echo "<li>Test all user flows (registration → login → create/take assessments)</li>";
echo "<li>Verify teacher-student isolation security</li>";
echo "<li>Set up production environment (hosting, domain, SSL)</li>";
echo "<li>Create backup strategy</li>";
echo "<li>Plan rollout to real users</li>";
echo "</ol>";

echo "<h2>🔗 Useful Links</h2>";
echo "<ul>";
echo "<li><a href='PRODUCTION-READINESS.md'>Detailed Production Readiness Guide</a></li>";
echo "<li><a href='SECURITY-FIXES.md'>Security Fixes Documentation</a></li>";
echo "<li><a href='switch-database.php'>Database Configuration</a></li>";
echo "<li><a href='README.md'>Project Documentation</a></li>";
echo "</ul>";
?>
