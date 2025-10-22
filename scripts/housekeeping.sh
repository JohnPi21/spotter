#!/usr/bin/env bash

set -euo pipefail

echo "=== Running housekeeping ($(date)) ==="

# 1. Docker cleanup
echo "Cleaning Docker..."
docker system prune -af
docker volume prune -f

# 2. Apt updates
echo "Updating system..."
apt-get update && apt-get upgrade -y

# 3. Log cleanup
echo "Truncating oversized logs..."
find /var/lib/docker/containers/ -name "*.log" -size +100M -exec truncate -s 0 {} \;

# 4. Disk usage
echo "Disk usage:"
df -h

echo "=== Done ==="