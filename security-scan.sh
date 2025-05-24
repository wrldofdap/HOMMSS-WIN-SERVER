#!/bin/bash

# Security scanning script for HOMMSS-PHP
# This script performs basic security checks on the codebase

echo "Starting security scan..."

# Check for composer vulnerabilities
echo "Checking for composer vulnerabilities..."
composer audit

# Check for hardcoded credentials
echo "Checking for hardcoded credentials..."
grep -r --include="*.php" "password\|secret\|key\|token" app/

# Check for SQL injection vulnerabilities
echo "Checking for potential SQL injection vulnerabilities..."
grep -r --include="*.php" "DB::raw\|whereRaw\|orderByRaw\|havingRaw\|selectRaw\|LIKE.*%" app/

# Check for XSS vulnerabilities
echo "Checking for potential XSS vulnerabilities..."
grep -r --include="*.php" "html_entity_decode\|htmlspecialchars_decode\|raw_output" app/

# Check for file inclusion vulnerabilities
echo "Checking for potential file inclusion vulnerabilities..."
grep -r --include="*.php" "include\|require\|file_get_contents" app/

# Check for command injection vulnerabilities
echo "Checking for potential command injection vulnerabilities..."
grep -r --include="*.php" "exec\|shell_exec\|system\|passthru\|proc_open" app/

# Check for debug mode
echo "Checking for debug mode..."
grep "APP_DEBUG=true" .env

echo "Security scan completed."
