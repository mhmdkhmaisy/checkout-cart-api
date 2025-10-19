#!/bin/bash

RESULTS_DIR="results/"

echo "========================================="
echo "Performance Test Results Analysis"
echo "========================================="
echo ""

# Check if results directory exists
if [ ! -d "$RESULTS_DIR" ]; then
    echo "No results found in $RESULTS_DIR"
    pause=true
else
    # Analyze simple test logs
    if ls $RESULTS_DIR/simple-test-*.log 1> /dev/null 2>&1; then
        echo "Simple Load Test Results:"
        echo "----------------------------------------"
        
        cat $RESULTS_DIR/simple-test-*.log > /tmp/combined-results.log
        
        total_requests=$(wc -l < /tmp/combined-results.log)
        successful=$(grep "|200|" /tmp/combined-results.log | wc -l)
        failed=$((total_requests - successful))
        success_rate=$(awk "BEGIN {printf \"%.2f\", ($successful / $total_requests) * 100}")
        
        avg_time=$(awk -F'|' '{sum+=$3; count++} END {printf "%.2f", sum/count}' /tmp/combined-results.log)
        min_time=$(awk -F'|' '{print $3}' /tmp/combined-results.log | sort -n | head -1)
        max_time=$(awk -F'|' '{print $3}' /tmp/combined-results.log | sort -n | tail -1)
        
        p95_line=$(awk "BEGIN {printf \"%.0f\", $total_requests * 0.95}")
        p95_time=$(awk -F'|' '{print $3}' /tmp/combined-results.log | sort -n | sed -n "${p95_line}p")
        
        echo "Total Requests: $total_requests"
        echo "Successful: $successful (${success_rate}%)"
        echo "Failed: $failed"
        echo ""
        echo "Response Times:"
        echo "  Average: ${avg_time}ms"
        echo "  Min: ${min_time}ms"
        echo "  Max: ${max_time}ms"
        echo "  P95: ${p95_time}ms"
        echo ""
        
        echo "Per-Route Statistics:"
        echo "----------------------------------------"
        awk -F'|' '{
            route=$1;
            time=$3;
            sum[route]+=$3;
            count[route]++;
            if ($2 == "200") success[route]++;
        }
        END {
            for (r in sum) {
                avg = sum[r]/count[r];
                succ = success[r] ? success[r] : 0;
                rate = (succ / count[r]) * 100;
                printf "%-30s | Avg: %6.0fms | Requests: %4d | Success: %5.1f%%\n", 
                    r, avg, count[r], rate;
            }
        }' /tmp/combined-results.log | sort -k4 -nr
        
        echo ""
    fi

    # Analyze k6 JSON results
    if ls $RESULTS_DIR/*-summary.json 1> /dev/null 2>&1; then
        echo ""
        echo "k6 Test Results:"
        echo "----------------------------------------"
        
        for file in $RESULTS_DIR/*-summary.json; do
            if [ -f "$file" ]; then
                echo ""
                echo "File: $(basename $file)"
                if command -v jq &> /dev/null; then
                    echo "  Duration: $(jq -r '.state.testRunDurationMs / 1000' $file)s"
                    echo "  VUs Max: $(jq -r '.metrics.vus_max.values.max' $file)"
                    echo "  Requests: $(jq -r '.metrics.http_reqs.values.count' $file)"
                    echo "  Failed: $(jq -r '.metrics.http_req_failed.values.rate * 100' $file)%"
                    echo "  Avg Duration: $(jq -r '.metrics.http_req_duration.values.avg' $file)ms"
                    echo "  P95 Duration: $(jq -r '.metrics.http_req_duration.values["p(95)"]' $file)ms"
                else
                    echo "  (Install jq for detailed analysis)"
                    grep -E '"count"|"rate"|"avg"|"p\(95\)"' "$file" | head -10
                fi
            fi
        done
        echo ""
    fi
fi

echo "========================================="
echo "Analysis complete!"
echo "========================================="
echo ""

# Cross-shell pause behavior
pause=false
if [ "${BASH_SOURCE[0]}" -ef "$0" ]; then
    pause=true
fi

# Detect PowerShell or CMD environments
if [ -n "$PSModulePath" ] || [[ "$SHELL" == *"powershell"* ]] || [[ "$SHELL" == *"cmd.exe"* ]]; then
    pause=true
fi

if [ "$pause" = true ]; then
    echo ""
    echo "Press ENTER to exit..."
    # Works in all environments (PowerShell, Bash, WSL)
    read _
fi
