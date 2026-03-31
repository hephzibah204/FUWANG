# Database Schema Auto-Importer

This project includes an automated mechanism to detect, validate, and import database schema files (`.sql`) during the installation process.

## Expected Directory Structure & Naming Convention

The auto-importer scans the following directories relative to the project root:
- `/Database`
- `/sql`
- `/database/schema`
- `/scripts`

### Naming Convention
You can name your SQL file anything ending in `.sql` (e.g., `gsoft_db.sql`, `schema.sql`, `init.sql`). 
If multiple files are found, the installer will prompt you to choose one. To avoid prompts in silent mode, it is recommended to keep only the primary schema file in these directories.

## How It Works

1. **Detection**: Scans the predefined directories for `.sql` files.
2. **Validation**: Checks the file for empty content and alerts you to engine-specific commands (like `ENGINE=InnoDB`) if you are importing into an incompatible engine (e.g., SQLite).
3. **Execution**: The script executes the SQL file inside a **database transaction**.
4. **Rollback**: If any syntax error or failure occurs during the import, all partial changes are rolled back automatically, preventing database corruption.
5. **Credentials**: The importer automatically uses the username and password defined in your `.env` configuration (detected database), meaning you do not need to re-enter them for the script.

## Usage

You can run the importer using the provided cross-platform scripts. 

### Windows (PowerShell)
```powershell
.\import_schema.ps1
```

### Linux / macOS (Bash)
```bash
./import_schema.sh
```

### Cross-Platform (Python)
```bash
python import_schema.py
```

### Direct Artisan Command
```bash
php artisan db:import-schema
```

### Options
- `--file=/path/to/file.sql` : Explicitly specify a file to bypass directory scanning.
- `--silent` : Run the import without prompting for confirmation (useful for CI/CD pipelines).

## Testing
The importer comes with comprehensive unit tests that mock the file system, simulate syntax errors, and test rollbacks against a temporary database.
You can run these tests via:
```bash
php artisan test --filter ImportDatabaseSchemaTest
```
