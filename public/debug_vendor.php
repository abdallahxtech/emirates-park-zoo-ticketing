<?php

// Bypass Laravel and go straight to Composer
require __DIR__ . '/../vendor/autoload.php';

echo "<h1>Vendor Debugger</h1>";

// 1. Check Class Existence
$class = 'Filament\PanelProvider';
if (class_exists($class)) {
    echo "<p style='color:green'>✅ Class <b>$class</b> exists!</p>";
} else {
    echo "<p style='color:red'>❌ Class <b>$class</b> NOT FOUND.</p>";
}

// 2. Check File System
$filamentPath = __DIR__ . '/../vendor/filament/filament';
if (is_dir($filamentPath)) {
    echo "<p>📂 vendor/filament/filament directory exists.</p>";
} else {
    echo "<p>❌ vendor/filament/filament directory MISSING.</p>";
}

// 3. Dump Composer Packages
echo "<hr><h3>Installed Packages:</h3><pre>";
$packages = \Composer\InstalledVersions::getAllRawData();
print_r($packages[0]['versions']['filament/filament'] ?? 'Filament NOT detected in InstalledVersions');
echo "</pre>";
