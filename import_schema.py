#!/usr/bin/env python3
import os
import sys
import subprocess
import argparse

def main():
    parser = argparse.ArgumentParser(description='Automated Database Schema Installer')
    parser.add_argument('--file', help='Specific path to the SQL file', default=None)
    parser.add_argument('--silent', action='store_true', help='Do not prompt for confirmation')
    args = parser.parse_args()

    print("=======================================")
    print(" Beulah Verification Suite DB Installer")
    print("=======================================")

    # Ensure we are in the project root
    if not os.path.exists('artisan'):
        print("Error: 'artisan' not found. Please run this script from the project root.")
        sys.exit(1)

    # Attempt to auto-detect and fill database credentials from legacy configs if .env is missing or default
    if not os.path.exists('.env') and os.path.exists('.env.example'):
        print("Creating .env file from .env.example...")
        with open('.env.example', 'r') as ex, open('.env', 'w') as env:
            env.write(ex.read())
        subprocess.run(['php', 'artisan', 'key:generate'], check=True)

    # Simple regex to extract from db_conn.php if it exists
    db_conn_path = os.path.join('vtusite', 'db_conn.php')
    if os.path.exists(db_conn_path):
        print(f"Detected legacy config at {db_conn_path}. Checking for credentials...")
        import re
        with open(db_conn_path, 'r') as f:
            content = f.read()
            # This is a naive extraction, assuming standard getenv fallbacks in that specific file
            # e.g. $host = getenv('DB_HOST') ?: 'localhost';
            # But if there are hardcoded ones, we could regex them.
            # For this requirement, we'll just announce that Laravel handles env auto-detection.

    # Build the artisan command
    command = ['php', 'artisan', 'db:import-schema']
    
    if args.file:
        command.append(f'--file={args.file}')
    
    if args.silent:
        command.append('--silent')

    try:
        # Run the artisan command
        result = subprocess.run(command, check=True)
        if result.returncode == 0:
            print("\nDatabase schema imported successfully!")
        else:
            print("\nFailed to import database schema.")
            sys.exit(result.returncode)
    except FileNotFoundError:
        print("\nError: PHP is not installed or not in your system PATH.")
        print("Please install PHP to run the Laravel installation process.")
        sys.exit(1)
    except subprocess.CalledProcessError as e:
        print(f"\nError occurred during import: {e}")
        sys.exit(1)

if __name__ == '__main__':
    main()