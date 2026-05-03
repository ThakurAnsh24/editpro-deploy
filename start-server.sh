#!/bin/bash
# Quick Deploy Script for EditPro
# Double-click this file or run in terminal to start local server
# IMPORTANT: This uses PHP server to support .php files!

cd "$(dirname "$0")"

echo "🚀 Starting EditPro Local Server..."
echo ""
echo "Your site will open at: http://localhost:9000"
echo ""
echo "To share with friends:"
echo "1. Tell them your IP: ifconfig | grep 'inet '"
echo "2. Example: http://192.168.1.X:8000"
echo ""
echo "Press Ctrl+C to stop server"
echo ""

# Start PHP server with router (fixes PHP execution issues)
php -S 127.0.0.1:8000 router.php
