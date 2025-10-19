#!/bin/bash

# Simple Load Test using curl and parallel
# Lightweight alternative when k6 is not available

BASE_URL="${BASE_URL:-https://aragon-data.live}"
CONCURRENT_USERS="${CONCURRENT_USERS:-10}"
REQUESTS_PER_USER="${REQUESTS_PER_USER:-10}"
OUTPUT_DIR="tests/performance/results"

mkdir -p "$OUTPUT_DIR"

echo "========================================="
echo "Simple Load Test"
echo "========================================="
echo "Base URL: $BASE_URL"
echo "Concurrent Users: $CONCURRENT_USERS"
echo "Requests per User: $REQUESTS_PER_USER"
echo "Total Requests: $((CONCURRENT_USERS * REQUESTS_PER_USER))"
echo "========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Routes to test
declare -a routes=(
    "/"
    "/store"
    "/vote"
    "/events"
    "/updates"
    "/admin"
    "/admin/performance"
    "/admin/cache"
)

# Test function
test_route() {
    local route=$1
    local url="${BASE_URL}${route}"
    local output_file="${OUTPUT_DIR}/simple-test-$(date +%s).log"
    
    local start_time=$(date +%s%N)
    local response_code=$(curl -o /dev/null -s -w "%{http_code}" -m 10 "$url")
    local end_time=$(date +%s%N)
    
    local duration=$(( (end_time - start_time) / 1000000 ))
    
    echo "$route|$response_code|$duration" >> "$output_file"
    
    if [ "$response_code" == "200" ]; then
        echo -e "${GREEN}✓${NC} $route - ${duration}ms"
    else
        echo -e "${RED}✗${NC} $route - $response_code - ${duration}ms"
    fi
}

export -f test_route
export BASE_URL OUTPUT_DIR GREEN RED NC

# Run load test
echo "Starting load test..."
START=$(date +%s)

for route in "${routes[@]}"; do
    echo ""
    echo "Testing: $route"
    echo "----------------------------------------"
    
    seq 1 $CONCURRENT_USERS | \
        xargs -I {} -P $CONCURRENT_USERS bash -c \
        "for i in {1..$REQUESTS_PER_USER}; do test_route '$route'; sleep 0.1; done"
done

END=$(date +%s)
DURATION=$((END - START))

echo ""
echo "========================================="
echo "Test completed in ${DURATION}s"
echo "========================================="
echo ""
echo "Results saved to: $OUTPUT_DIR"
echo ""
echo "To analyze results, run:"
echo "  ./tests/performance/utils/analyze-results.sh"
echo ""

# Only pause if run directly (not from another script)
if [ "${BASH_SOURCE[0]}" -ef "$0" ]; then
    echo "Press any key to exit..."
    read -n 1 -s
fi
