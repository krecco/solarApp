<?php

// Check which commands need fixing
$commandsDir = '/home/test/saas/saas-central/app/Console/Commands/ApiTest/';
$files = scandir($commandsDir);

echo "Checking API Test Commands for abstract method implementations...\n\n";

$commandsToFix = [];
$commandsOk = [];

foreach ($files as $file) {
    if (!str_ends_with($file, '.php') || $file === 'BaseApiCommand.php') {
        continue;
    }
    
    $content = file_get_contents($commandsDir . $file);
    
    // Check for the abstract methods - they should have "protected function" not just "function"
    $hasPrepareRequestData = preg_match('/protected\s+function\s+prepareRequestData\s*\(\)\s*:?\s*array/i', $content);
    $hasGetExamplePayload = preg_match('/public\s+function\s+getExamplePayload\s*\(\)\s*:?\s*array/i', $content);
    $hasGetEndpointDescription = preg_match('/public\s+function\s+getEndpointDescription\s*\(\)\s*:?\s*string/i', $content);
    
    // Check for custom makeRequest method (indicates old pattern)
    $hasCustomMakeRequest = preg_match('/private\s+function\s+makeRequest|protected\s+function\s+makeRequest/', $content);
    
    if (!$hasPrepareRequestData || !$hasGetExamplePayload || !$hasGetEndpointDescription || $hasCustomMakeRequest) {
        $commandsToFix[] = $file;
        echo "❌ $file\n";
        if (!$hasPrepareRequestData) echo "   - Missing prepareRequestData()\n";
        if (!$hasGetExamplePayload) echo "   - Missing getExamplePayload()\n";
        if (!$hasGetEndpointDescription) echo "   - Missing getEndpointDescription()\n";
        if ($hasCustomMakeRequest) echo "   - Has custom makeRequest() method (old pattern)\n";
    } else {
        $commandsOk[] = $file;
        echo "✅ $file\n";
    }
}

echo "\n";
echo "Summary:\n";
echo "✅ Commands OK: " . count($commandsOk) . "\n";
echo "❌ Commands to fix: " . count($commandsToFix) . "\n";

if (!empty($commandsToFix)) {
    echo "\nCommands that need fixing:\n";
    foreach ($commandsToFix as $file) {
        echo "  - $file\n";
    }
}
