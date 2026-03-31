#!/bin/bash

echo "======================================="
echo " Beulah Verification Suite DB Installer"
echo "======================================="

if [ ! -f "artisan" ]; then
    echo "Error: 'artisan' not found. Please run this script from the project root."
    exit 1
fi

if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in your system PATH."
    exit 1
fi

php artisan db:import-schema "$@"

if [ $? -eq 0 ]; then
    echo -e "\nDatabase schema imported successfully!"
else
    echo -e "\nFailed to import database schema."
    exit 1
fi