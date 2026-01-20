<?php

// Turn on all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

echo "<h1>Debug Info</h1>";

if (class_exists('Filament\PanelProvider')) {
    echo "<p style='color:green'>✅ Class Filament\PanelProvider exists!</p>";
} else {
    echo "<p style='color:red'>❌ Class Filament\PanelProvider NOT found!</p>";
}

echo "<h2>Recent Logs</h2>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';

if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    echo "<pre>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
} else {
    echo "Log file not found at: $logFile";
}
