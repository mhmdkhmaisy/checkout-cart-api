#!/bin/bash

# k6 Installation Script
# Automatically detects OS and installs k6

set -e

echo "========================================="
echo "  k6 Load Testing Tool - Installation   "
echo "========================================="
echo ""

# Detect OS
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    OS="linux"
elif [[ "$OSTYPE" == "darwin"* ]]; then
    OS="mac"
elif [[ "$OSTYPE" == "msys" || "$OSTYPE" == "win32" ]]; then
    OS="windows"
else
    OS="unknown"
fi

echo "Detected OS: $OS"
echo ""

case $OS in
    linux)
        echo "Installing k6 for Linux..."
        
        # Check if running as root
        if [ "$EUID" -ne 0 ]; then
            echo "Please run with sudo or as root"
            exit 1
        fi
        
        # Import GPG key
        gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg \
            --keyserver hkp://keyserver.ubuntu.com:80 \
            --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
        
        # Add repository
        echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | \
            tee /etc/apt/sources.list.d/k6.list
        
        # Update and install
        apt-get update
        apt-get install k6 -y
        
        echo ""
        echo "✓ k6 installed successfully!"
        ;;
        
    mac)
        echo "Installing k6 for macOS..."
        
        # Check if Homebrew is installed
        if ! command -v brew &> /dev/null; then
            echo "Error: Homebrew is not installed"
            echo "Install Homebrew first: https://brew.sh"
            exit 1
        fi
        
        brew install k6
        
        echo ""
        echo "✓ k6 installed successfully!"
        ;;
        
    windows)
        echo "Installing k6 for Windows..."
        
        # Check if Chocolatey is installed
        if ! command -v choco &> /dev/null; then
            echo "Error: Chocolatey is not installed"
            echo "Install Chocolatey first: https://chocolatey.org/install"
            echo ""
            echo "Alternatively, download k6 manually from:"
            echo "https://dl.k6.io/msi/k6-latest-amd64.msi"
            exit 1
        fi
        
        choco install k6 -y
        
        echo ""
        echo "✓ k6 installed successfully!"
        ;;
        
    *)
        echo "Error: Unsupported operating system"
        echo ""
        echo "Please install k6 manually from:"
        echo "https://k6.io/docs/get-started/installation/"
        exit 1
        ;;
esac

# Verify installation
echo ""
echo "Verifying installation..."
if command -v k6 &> /dev/null; then
    k6 version
    echo ""
    echo "========================================="
    echo "Installation successful!"
    echo "========================================="
    echo ""
    echo "Next steps:"
    echo "  1. Run: ./tests/performance/run-tests.sh"
    echo "  2. Or run a specific test:"
    echo "     k6 run tests/performance/scenarios/baseline.js"
else
    echo "Error: k6 installation failed"
    echo "Please install manually: https://k6.io/docs/get-started/installation/"
    exit 1
fi
