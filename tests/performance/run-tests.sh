#!/bin/bash

# Main test runner script
# Runs all performance tests in sequence

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RESULTS_DIR="$SCRIPT_DIR/results"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Create results directory
mkdir -p "$RESULTS_DIR"

echo -e "${BLUE}"
echo "========================================="
echo "  ARAGON RSPS - Performance Test Suite  "
echo "========================================="
echo -e "${NC}"
echo ""

# Check for k6
if command -v k6 &> /dev/null; then
    K6_AVAILABLE=true
    echo -e "${GREEN}✓ k6 is installed${NC}"
else
    K6_AVAILABLE=false
    echo -e "${YELLOW}⚠ k6 is not installed - will use simple tests only${NC}"
    echo -e "${YELLOW}  Install k6 from: https://k6.io/docs/get-started/installation/${NC}"
fi

echo ""
echo "Test Options:"
echo "  1) Baseline Test (Quick, 1 user, 10 iterations)"
echo "  2) Store Flow Load Test (10-50 users, ~12 min)"
echo "  3) Vote Rush Spike Test (10-100 users spike)"
echo "  4) Admin Panel Load Test (10-50 users, ~12 min)"
echo "  5) Client Downloads Test (5-10 users, ~8 min)"
echo "  6) Cache Downloads Test (5 users, ~5 min)"
echo "  7) Simple curl-based load test (No k6 required)"
echo "  8) Run ALL tests (Full suite)"
echo "  9) View/Analyze existing results"
echo "  0) Exit"
echo ""

read -p "Select test to run [0-9]: " choice

run_k6_test() {
    local test_file=$1
    local test_name=$2
    
    echo ""
    echo -e "${BLUE}Running: $test_name${NC}"
    echo "========================================="
    
    cd "$SCRIPT_DIR"
    k6 run "scenarios/$test_file"
    
    echo -e "${GREEN}✓ $test_name complete${NC}"
}

run_simple_test() {
    echo ""
    echo -e "${BLUE}Running: Simple Load Test${NC}"
    echo "========================================="
    
    bash "$SCRIPT_DIR/utils/simple-load-test.sh"
    
    echo -e "${GREEN}✓ Simple test complete${NC}"
}

analyze_results() {
    echo ""
    echo -e "${BLUE}Analyzing Results...${NC}"
    echo "========================================="
    
    bash "$SCRIPT_DIR/utils/analyze-results.sh"
}

case $choice in
    1)
        if [ "$K6_AVAILABLE" = true ]; then
            run_k6_test "baseline.js" "Baseline Test"
        else
            echo -e "${RED}k6 is required for this test${NC}"
            exit 1
        fi
        ;;
    2)
        if [ "$K6_AVAILABLE" = true ]; then
            run_k6_test "store-flow.js" "Store Flow Load Test"
        else
            echo -e "${RED}k6 is required for this test${NC}"
            exit 1
        fi
        ;;
    3)
        if [ "$K6_AVAILABLE" = true ]; then
            run_k6_test "vote-rush.js" "Vote Rush Spike Test"
        else
            echo -e "${RED}k6 is required for this test${NC}"
            exit 1
        fi
        ;;
    4)
        if [ "$K6_AVAILABLE" = true ]; then
            run_k6_test "admin-panel.js" "Admin Panel Load Test"
        else
            echo -e "${RED}k6 is required for this test${NC}"
            exit 1
        fi
        ;;
    5)
        if [ "$K6_AVAILABLE" = true ]; then
            run_k6_test "client-downloads.js" "Client Downloads Test"
        else
            echo -e "${RED}k6 is required for this test${NC}"
            exit 1
        fi
        ;;
    6)
        if [ "$K6_AVAILABLE" = true ]; then
            run_k6_test "cache-downloads.js" "Cache Downloads Test"
        else
            echo -e "${RED}k6 is required for this test${NC}"
            exit 1
        fi
        ;;
    7)
        run_simple_test
        ;;
    8)
        if [ "$K6_AVAILABLE" = true ]; then
            echo ""
            echo -e "${YELLOW}Running FULL test suite - this will take ~40 minutes${NC}"
            read -p "Continue? (y/n): " confirm
            
            if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
                run_k6_test "baseline.js" "Baseline Test"
                sleep 10
                run_k6_test "store-flow.js" "Store Flow Load Test"
                sleep 10
                run_k6_test "vote-rush.js" "Vote Rush Spike Test"
                sleep 10
                run_k6_test "admin-panel.js" "Admin Panel Load Test"
                sleep 10
                run_k6_test "client-downloads.js" "Client Downloads Test"
                sleep 10
                run_k6_test "cache-downloads.js" "Cache Downloads Test"
                
                echo ""
                echo -e "${GREEN}=========================================${NC}"
                echo -e "${GREEN}  All tests complete!${NC}"
                echo -e "${GREEN}=========================================${NC}"
                
                analyze_results
            fi
        else
            run_simple_test
            analyze_results
        fi
        ;;
    9)
        analyze_results
        ;;
    0)
        echo "Exiting..."
        exit 0
        ;;
    *)
        echo -e "${RED}Invalid option${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${BLUE}=========================================${NC}"
echo -e "${BLUE}Results saved to: $RESULTS_DIR${NC}"
echo -e "${BLUE}=========================================${NC}"
echo ""
echo "Next steps:"
echo "  • Check the performance monitor: /admin/performance"
echo "  • Review results: ./tests/performance/utils/analyze-results.sh"
echo "  • View detailed logs in: $RESULTS_DIR"
echo ""
