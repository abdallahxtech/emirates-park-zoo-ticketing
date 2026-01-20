<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Laravel Diagnostic Tool</h1>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";

// 1. Check Autoloader
echo "<h2>1. Autoloader</h2>";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    echo "<p style='color:green'>✅ Vendor autoload loaded.</p>";
} else {
    die("<p style='color:red'>❌ Vendor autoload NOT found!</p>");
}

// 2. Class Check
echo "<h2>2. Critical Classes</h2>";
$classes = [
    'Filament\PanelProvider',
    'Illuminate\Foundation\Application',
    'Illuminate\Support\Facades\DB'
];
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<div style='color:green'>✅ $class exists</div>";
    } else {
        echo "<div style='color:red'>❌ $class NOT found</div>";
    }
}

// 3. Boot Laravel
echo "<h2>3. Booting Laravel</h2>";
try {
    if (!file_exists(__DIR__ . '/../bootstrap/app.php')) {
        throw new Exception("bootstrap/app.php missing");
    }
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // Attempt to make the kernel
    echo "<div>Making Kernel...</div>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "<div>Handling Request...</div>";
    // Mock a request
    $request = Illuminate\Http\Request::create('/health', 'GET');
    $response = $kernel->handle($request);
    
    echo "<h3 style='color:green'>✅ Laravel Booted Successfully!</h3>";
    echo "Response Status: " . $response->getStatusCode();
    
} catch (Throwable $e) {
    echo "<h3 style='color:red'>❌ Laravel Crash: " . get_class($e) . "</h3>";
    echo "<h3>Message: " . $e->getMessage() . "</h3>";
    echo "<strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre style='background:#f0f0f0; padding:10px; overflow:scroll;'>" . $e->getTraceAsString() . "</pre>";
}

// 4. Environment Check
echo "<h2>4. Environment Variables</h2>";
// Don't show values for security, just presence
$keys = ['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE'];
echo "<table border='1' cellpadding='5'>";
foreach ($keys as $key) {
    $val = getenv($key);
    if ($val === false) $val = $_ENV[$key] ?? null; // Try $_ENV too
    
    $display = $val ? "SET" : "<span style='color:red'>MISSING</span>";
    if ($key === 'DB_CONNECTION' || $key === 'APP_ENV') {
        $display .= " (" . ($val ?: 'null') . ")";
    }
    echo "<tr><td>$key</td><td>$display</td></tr>";
}
echo "</table>";

// 5. Config Check (if app booted enough)
if (isset($app)) {
    echo "<h2>5. Config Values</h2>";
    try {
        echo "DB Default: " . config('database.default') . "<br>";
        echo "App Debug: " . (config('app.debug') ? 'True' : 'False') . "<br>";
    } catch (Throwable $e) {
        echo "Could not read config: " . $e->getMessage();
    }
}

// 6. Logs
echo "<h2>6. Recent Logs</h2>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "Log file size: " . filesize($logFile) . " bytes<br>";
    $lines = file($logFile);
    $last = array_slice($lines, -50);
    echo "<pre>" . htmlspecialchars(implode("", $last)) . "</pre>";
} else {
    echo "Log file not found at $logFile";
}
