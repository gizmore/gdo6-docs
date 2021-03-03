#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")"

echo "Installing phpDocumentor via phar..."

# 1) GET IT
#echo "Downloading phar..."
#curl https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.0.0/phpDocumentor.phar

# 2) RUN IT
#echo "Installing phar..."
php phpDocumentor.phar
