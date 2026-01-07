<?php
// test_facebook_manual.php
// Usage: php test_facebook_manual.php "YOUR_FACEBOOK_URL"

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Process\Process;

$link = $argv[1] ?? null;

if (!$link) {
    echo "Usage: php test_facebook_manual.php \"https://www.facebook.com/watch/?v=...\"\n";
    exit(1);
}

echo "Testing Facebook Scraper for: $link\n";

$scriptPath = __DIR__ . '/scrapers/facebook.js';
// Detect node path
$nodePath = 'node';
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $detectNode = trim(shell_exec('which node'));
    if ($detectNode) {
        $nodePath = $detectNode;
    }
}
echo "Node Path: $nodePath\n";

$process = new Process([$nodePath, $scriptPath, $link]);
$process->setWorkingDirectory(__DIR__);
$process->setTimeout(60);

// Use the same env logic as the controller
$tempDir = sys_get_temp_dir();
$env = [
    'PATH' => getenv('PATH'),
    'HOME' => $tempDir,
    'PUPPETEER_EXECUTABLE_PATH' => getenv('VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH'), 
];

// Try to load .env manually if VITE_... is missing
if (empty($env['PUPPETEER_EXECUTABLE_PATH']) && file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env');
    foreach ($lines as $line) {
        if (strpos(trim($line), 'VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH=') === 0) {
            $parts = explode('=', $line, 2);
            if (count($parts) > 1) {
                $env['PUPPETEER_EXECUTABLE_PATH'] = trim(trim($parts[1]), '"\'');
                break;
            }
        }
    }
}

echo "Puppeteer Path: " . ($env['PUPPETEER_EXECUTABLE_PATH'] ?? 'Not found (using default)') . "\n";

$process->setEnv($env);
$process->run();

echo "\n--- Output ---\n";
echo $process->getOutput();

echo "\n--- Error Output ---\n";
echo $process->getErrorOutput();

echo "\nExit Code: " . $process->getExitCode() . "\n";
