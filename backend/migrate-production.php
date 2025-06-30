<?php
/**
 * Estatly - Production Migration Script for cPanel hosting
 * Upload this file to your public_html/estatly/api/ directory and visit it once in your browser
 * Then DELETE this file for security
 */

// Prevent running in non-production environments
if (!file_exists('.env') || !str_contains(file_get_contents('.env'), 'APP_ENV=production')) {
    die('This script can only run in production environment');
}

try {
    require_once 'vendor/autoload.php';
    
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<h2>Running Database Migrations...</h2>";
    echo "<pre>";
    
    // Run migrations
    $exitCode = $kernel->call('migrate', ['--force' => true]);
    echo "Migrations completed with exit code: " . $exitCode . "\n";
    
    // Create initial admin user if needed
    $seedExitCode = $kernel->call('db:seed', ['--force' => true, '--class' => 'DatabaseSeeder']);
    echo "Seeding completed with exit code: " . $seedExitCode . "\n";
    
    echo "</pre>";
    echo "<h3 style='color: green;'>✅ Database setup completed successfully!</h3>";
    echo "<p><strong>IMPORTANT:</strong> Please delete this file (migrate-production.php) for security reasons.</p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<p>Please check your database configuration in .env file</p>";
}
?>