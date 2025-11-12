#!/usr/bin/env php
<?php

$commandsDir = '/home/test/saas/saas-central/app/Console/Commands/ApiTest/';
$files = scandir($commandsDir);

echo "Checking API Test Commands for abstract method implementations...\n\n";

$issuesFound = [];

foreach ($files as $file) {
    if (!str_ends_with($file, '.php') || $file === 'BaseApiCommand.php') {
        continue;
    }
    
    $content = file_get_contents($commandsDir . $file);
    
    $hasPrepareRequestData = strpos($content, 'function prepareRequestData()') !== false ||
                             strpos($content, 'function prepareRequestData():') !== false;
    $hasGetExamplePayload = strpos($content, 'function getExamplePayload()') !== false ||
                            strpos($content, 'function getExamplePayload():') !== false;
    $hasGetEndpointDescription = strpos($content, 'function getEndpointDescription()') !== false ||
                                strpos($content, 'function getEndpointDescription():') !== false;
    
    if (!$hasPrepareRequestData || !$hasGetExamplePayload || !$hasGetEndpointDescription) {
        $issuesFound[] = $file;
        echo "❌ $file - Missing abstract methods:\n";
        if (!$hasPrepareRequestData) echo "   - prepareRequestData()\n";
        if (!$hasGetExamplePayload) echo "   - getExamplePayload()\n";
        if (!$hasGetEndpointDescription) echo "   - getEndpointDescription()\n";
        echo "\n";
    } else {
        echo "✅ $file - All abstract methods implemented\n";
    }
}

echo "\n";
if (empty($issuesFound)) {
    echo "✅ All commands properly implement abstract methods!\n";
} else {
    echo "❌ Found " . count($issuesFound) . " commands with missing abstract methods:\n";
    foreach ($issuesFound as $file) {
        echo "   - $file\n";
    }
}
