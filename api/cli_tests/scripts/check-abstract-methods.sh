#!/bin/bash

echo "Checking for commands with missing abstract methods..."
echo ""

cd /home/test/saas/saas-central

# Try to run artisan and capture all errors
php artisan list 2>&1 | grep -E "contains [0-9]+ abstract method" -B 2 | grep -E "Class App\\\\Console\\\\Commands\\\\ApiTest" | sed 's/.*Class App\\Console\\Commands\\ApiTest\\//' | sed 's/ contains.*//' | sort | uniq

echo ""
echo "These commands need to implement abstract methods from BaseApiCommand"
