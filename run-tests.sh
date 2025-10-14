#!/bin/bash

# Test runner script for Docker container
# Tests use RefreshDatabase trait which auto-migrates for each test

echo "========================================="
echo "Test Environment Information"
echo "========================================="

# Check PHP version
echo "PHP Version:"
php --version
echo ""

echo "Database: SQLite (in-memory)"
echo "Tests will automatically migrate using RefreshDatabase trait"
echo ""

# Run the tests with coverage
echo "========================================="
echo "Running tests with coverage..."
echo "========================================="
echo ""

# Execute the test command passed as arguments, or default command
if [ $# -eq 0 ]; then
    # Default: Run all tests with coverage
    php -d pcov.enabled=1 vendor/bin/pest --coverage --coverage-text
else
    # Run the specific test command provided
    "$@"
fi

EXIT_CODE=$?

# Check if coverage directory exists
if [ -d "/app/coverage" ]; then
    echo ""
    echo "========================================="
    echo "Coverage reports"
    echo "========================================="
    
    # If clover.xml exists, show summary
    if [ -f "/app/coverage/clover.xml" ]; then
        echo "✓ Clover XML: /app/coverage/clover.xml"
    fi
    
    if [ -f "/app/coverage/coverage.txt" ]; then
        echo "✓ Text report: /app/coverage/coverage.txt"
        cat /app/coverage/coverage.txt
    fi
fi

echo ""
echo "========================================="
echo "Test execution completed (exit code: $EXIT_CODE)"
echo "========================================="

exit $EXIT_CODE
