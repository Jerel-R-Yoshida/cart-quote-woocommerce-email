#!/bin/bash
# Build Test Script for Cart Quote WooCommerce Plugin
# Runs automated tests before building to prevent integration issues

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(dirname "$BUILD_SCRIPT_DIR")"
TEST_DIR="$PLUGIN_DIR/tests"
BUILD_DIR="$PLUGIN_DIR/build"

# Test counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Logging function
log() {
    echo -e "${YELLOW}[BUILD TEST]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[BUILD TEST PASSED]${NC} $1"
}

log_error() {
    echo -e "${RED}[BUILD TEST FAILED]${NC} $1"
}

# Function to count test results
count_test_results() {
    local total=$1
    local passed=$2
    local failed=$3

    TOTAL_TESTS=$((TOTAL_TESTS + total))
    PASSED_TESTS=$((PASSED_TESTS + passed))
    FAILED_TESTS=$((FAILED_TESTS + failed))
}

# 1. PHP Syntax Check
echo ""
echo "======================================"
echo "1. PHP Syntax Check"
echo "======================================"
SYNTAX_ERRORS=0
SYNTAX_COUNT=$(find "$BUILD_SCRIPT_DIR" -type f -name "*.php" | xargs -r php -l 2>&1 | grep "Parse error" | wc -l || echo "0")
SYNTAX_ERRORS=$SYNTAX_COUNT
if [ "$SYNTAX_ERRORS" -eq "0" ]; then
    log_success "All PHP files have valid syntax"
else
    log_error "Found $SYNTAX_ERRORS PHP syntax errors"
    echo "Full syntax check output:"
    find "$BUILD_SCRIPT_DIR" -type f -name "*.php" | xargs -r php -l 2>&1 | grep -v "Could not open input file" || true
    exit 1
fi

# 2. Check Required Files Exist
echo ""
echo "======================================"
echo "2. Required Files Check"
echo "======================================"
REQUIRED_FILES=(
    "cart-quote-woocommerce-email.php"
    "src/Core/Activator.php"
    "src/Core/Deactivator.php"
    "src/Core/Plugin.php"
    "src/Core/Validator.php"
    "src/Admin/Settings.php"
    "src/Admin/Health_Check.php"
)

FILES_MISSING=0
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$BUILD_SCRIPT_DIR/$file" ]; then
        log_error "Missing required file: $file"
        FILES_MISSING=$((FILES_MISSING + 1))
    fi
done

if [ "$FILES_MISSING" -eq 0 ]; then
    log_success "All required files are present"
else
    log_error "$FILES_MISSING required files are missing"
    exit 1
fi

# 3. Check Directory Structure
echo ""
echo "======================================"
echo "3. Directory Structure Check"
echo "======================================"
REQUIRED_DIRS=(
    "src"
    "src/Core"
    "src/Admin"
    "src/Database"
    "src/Emails"
    "src/Frontend"
    "src/Google"
    "src/WooCommerce"
    "templates"
    "templates/admin"
    "templates/frontend"
    "assets"
    "assets/css"
    "assets/js"
    "tests"
    "tests/Unit"
    "tests/Integration"
)

DIRS_MISSING=0
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$BUILD_SCRIPT_DIR/$dir" ]; then
        log_error "Missing required directory: $dir"
        DIRS_MISSING=$((DIRS_MISSING + 1))
    fi
done

if [ "$DIRS_MISSING" -eq 0 ]; then
    log_success "All required directories are present"
else
    log_error "$DIRS_MISSING required directories are missing"
    exit 1
fi

# 4. Run Unit Tests
echo ""
echo "======================================"
echo "4. Running Unit Tests"
echo "======================================"
if [ -f "$BUILD_SCRIPT_DIR/tests/Unit/ValidatorSimpleTest.php" ]; then
    cd "$BUILD_SCRIPT_DIR/tests/Unit"
    if php ValidatorSimpleTest.php > /dev/null 2>&1; then
        log_success "All unit tests passed"
        count_test_results 1 1 0
    else
        log_error "Unit tests failed"
        count_test_results 1 0 1
    fi
    cd "$BUILD_SCRIPT_DIR"
else
    log_error "Unit test file not found: $BUILD_SCRIPT_DIR/tests/Unit/ValidatorSimpleTest.php"
fi

# 5. Check for Critical Vulnerabilities
echo ""
echo "======================================"
echo "5. Security Check"
echo "======================================"
VULNERABILITY_FOUND=0

# Check for dangerous functions (eval is critical)
DANGEROUS_FUNCTIONS=$(grep -r "eval(" "$BUILD_SCRIPT_DIR/src" --include="*.php" || echo "0")
if [ -n "$DANGEROUS_FUNCTIONS" ] && [ "$DANGEROUS_FUNCTIONS" != "0" ]; then
    log_error "Found eval() usage which is dangerous"
    VULNERABILITY_FOUND=1
fi

# Check for dangerous shell commands (exec, shell_exec, system, passthru are critical)
DANGEROUS_OPS=$(grep -rE "\b(exec|shell_exec|system|passthru)\s*\(" "$BUILD_SCRIPT_DIR/src" --include="*.php" || echo "0")
if [ -n "$DANGEROUS_OPS" ] && [ "$DANGEROUS_OPS" != "0" ]; then
    log_error "Found potentially dangerous shell commands"
    VULNERABILITY_FOUND=1
fi

if [ "$VULNERABILITY_FOUND" -eq 0 ]; then
    log_success "No critical vulnerabilities detected"
else
    exit 1
fi

# 6. Check Code Quality
echo ""
echo "======================================"
echo "6. Code Quality Check"
echo "======================================"
CODE_ISSUES=0

# Check for strict type declarations in Activator.php
if [ -f "$BUILD_SCRIPT_DIR/src/Core/Activator.php" ]; then
    if ! grep -q "declare(strict_types=1);" "$BUILD_SCRIPT_DIR/src/Core/Activator.php"; then
        log_error "Activator.php missing strict type declarations"
        CODE_ISSUES=1
    fi
fi

# Check for strict type declarations in Deactivator.php
if [ -f "$BUILD_SCRIPT_DIR/src/Core/Deactivator.php" ]; then
    if ! grep -q "declare(strict_types=1);" "$BUILD_SCRIPT_DIR/src/Core/Deactivator.php"; then
        log_error "Deactivator.php missing strict type declarations"
        CODE_ISSUES=1
    fi
fi

# Check for strict type declarations in Plugin.php
if [ -f "$BUILD_SCRIPT_DIR/src/Core/Plugin.php" ]; then
    if ! grep -q "declare(strict_types=1);" "$BUILD_SCRIPT_DIR/src/Core/Plugin.php"; then
        log_error "Plugin.php missing strict type declarations"
        CODE_ISSUES=1
    fi
fi

# Check for strict type declarations in Validator.php
if [ -f "$BUILD_SCRIPT_DIR/src/Core/Validator.php" ]; then
    if ! grep -q "declare(strict_types=1);" "$BUILD_SCRIPT_DIR/src/Core/Validator.php"; then
        log_error "Validator.php missing strict type declarations"
        CODE_ISSUES=1
    fi
fi

if [ "$CODE_ISSUES" -eq 0 ]; then
    log_success "Code quality standards met"
else
    exit 1
fi

# 7. Check Version Consistency
echo ""
echo "======================================"
echo "7. Version Consistency Check"
echo "======================================"
MAIN_VERSION=$(grep "CART_QUOTE_WC_VERSION" "$BUILD_SCRIPT_DIR/cart-quote-woocommerce-email.php" | head -1 | grep -oP "'\d+\.\d+\.\d+'")

if [ -n "$MAIN_VERSION" ]; then
    # Clean up version string
    CLEAN_VERSION=$(echo "$MAIN_VERSION" | tr -d "'")
    log_success "Version numbers are consistent"
else
    log_error "Version consistency check failed"
fi

# 9. Final Summary
echo ""
echo "======================================"
echo "BUILD TEST SUMMARY"
echo "======================================"
echo "Total Tests: $TOTAL_TESTS"
echo -e "${GREEN}Passed: $PASSED_TESTS${NC}"
echo -e "${RED}Failed: $FAILED_TESTS${NC}"
echo "======================================"

if [ $FAILED_TESTS -eq 0 ]; then
    log_success "ALL BUILD TESTS PASSED ✓"
    exit 0
else
    log_error "SOME BUILD TESTS FAILED ✗"
    exit 1
fi
