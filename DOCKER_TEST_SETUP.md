# AI Unit Testing Agent - Docker Test Setup Guide

## Problem Summary
The AI Unit Testing Agent writes test cases but they were failing due to:
1. **Database not set up** - Migrations weren't run
2. **SQLite incompatibility** - One migration tried to drop foreign keys by name (not supported in SQLite)
3. **Missing seed data** - Tests require default channel/locale data

## Solution Implemented

### 1. **Fixed SQLite-Incompatible Migration**
**File**: `packages/Webkul/Product/src/Database/Migrations/2023_12_11_054614_add_channel_id_column_in_product_price_indices_table.php`

The migration now detects the database driver and skips SQLite-incompatible operations:
```php
$driver = DB::getDriverName();

if ($driver !== 'sqlite') {
    // Only run these for MySQL/PostgreSQL
    $table->dropForeign($tablePrefix.'product_price_indices_product_id_foreign');
    // ... other drops
}
```

### 2. **Docker Configuration**
**File**: `Dockerfile`
- Uses SQLite in-memory database (`:memory:`)
- Configured with `RefreshDatabase` trait for automatic migrations per test
- PCOV enabled for code coverage

### 3. **Test Runner Script**
**File**: `run-tests.sh`
- Simple script that runs tests with coverage
- Displays coverage reports
- Returns proper exit codes

### 4. **Test Base Class**
**File**: `tests/TestCase.php`
- Uses `RefreshDatabase` trait (auto-migrates for each test)
- Previously used `DatabaseTransactions` (requires existing DB)

## Current Issue & Solution

### Issue: Missing Seed Data
The test fails because `core()->getDefaultChannel()` returns null. The database needs to be seeded with default data (channels, locales, etc.).

### Solution Options:

#### Option A: Update TestCase to Auto-Seed (RECOMMENDED)
Add this to `tests/TestCase.php`:

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic data required for tests
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);
    }
}
```

#### Option B: Update Individual Tests
Add seeding at the start of each test:

```php
it('should calculate total carts progress correctly', function () {
    // Seed the database first
    $this->artisan('db:seed');
    
    // ... rest of test
});
```

#### Option C: Create Test-Specific Seeders
Create minimal seeders for tests in `database/seeders/TestSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\Locale;
use Webkul\Core\Models\Currency;

class TestSeeder extends Seeder
{
    public function run()
    {
        // Create default locale
        Locale::create([
            'code' => 'en',
            'name' => 'English',
        ]);
        
        // Create default currency
        $currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
        ]);
        
        // Create default channel
        Channel::create([
            'code' => 'default',
            'name' => 'Default',
            'hostname' => 'localhost',
            'default_locale_id' => 1,
            'base_currency_id' => $currency->id,
        ]);
    }
}
```

Then use it in TestCase:
```php
$this->artisan('db:seed', ['--class' => 'Database\\Seeders\\TestSeeder']);
```

## How to Use

### 1. Build Docker Image
```bash
sudo bash tool/build-test-env-docker-stage.sh
```

### 2. Run Tests with Coverage
```bash
# Run specific test file
sudo docker run --rm php-laravel-bagisto:test /app/run-tests.sh \
  php -d pcov.enabled=1 vendor/bin/pest \
  packages/Webkul/Admin/tests/Feature/Reporting/CartReportTest.php \
  --coverage --coverage-text \
  --coverage-filter=packages/Webkul/Admin/src/Helpers/Reporting/

# Run all tests
sudo docker run --rm php-laravel-bagisto:test
```

### 3. Extract Coverage Reports
```bash
# Copy coverage reports from container
sudo docker run --rm -v $(pwd)/coverage:/host-coverage php-laravel-bagisto:test \
  bash -c "cp -r /app/coverage/* /host-coverage/"

# View clover XML
cat coverage/clover.xml

# View text report
cat coverage/coverage.txt
```

## For Your AI Testing Agent

### Integration Steps:

1. **Before running tests**: Ensure Docker image is built
   ```bash
   sudo bash tool/build-test-env-docker-stage.sh
   ```

2. **After AI writes test cases**: Rebuild image with new tests
   ```bash
   sudo bash tool/build-test-env-docker-stage.sh
   ```

3. **Run tests and get results**:
   ```bash
   sudo docker run --rm -v $(pwd)/coverage:/app/coverage \
     php-laravel-bagisto:test /app/run-tests.sh \
     php -d pcov.enabled=1 vendor/bin/pest <test-file-path> \
     --coverage --coverage-clover=/app/coverage/clover.xml \
     --coverage-text
   ```

4. **Parse coverage from** `/app/coverage/clover.xml`

### Test Command Format
The command from `test-gen-config.json`:
```json
"test-command": "php -d pcov.enabled=1 vendor/bin/pest packages/Webkul/Admin/tests/Feature/Reporting/CartReportTest.php --coverage --coverage-text --coverage-filter=packages/Webkul/Admin/src/Helpers/Reporting/"
```

Should be run as:
```bash
sudo docker run --rm php-laravel-bagisto:test /app/run-tests.sh \
  php -d pcov.enabled=1 vendor/bin/pest \
  packages/Webkul/Admin/tests/Feature/Reporting/CartReportTest.php \
  --coverage --coverage-text \
  --coverage-filter=packages/Webkul/Admin/src/Helpers/Reporting/
```

## Next Steps

1. **Fix the seeding issue** - Choose one of the options above (A, B, or C)
2. **Rebuild Docker image** - `sudo bash tool/build-test-env-docker-stage.sh`
3. **Test again** - Run the test command
4. **Verify coverage** - Check `/app/coverage/clover.xml`

## Coverage Report Locations

Inside the container:
- Clover XML: `/app/coverage/clover.xml`
- Text report: `/app/coverage/coverage.txt`
- HTML report: `/app/coverage/html/` (if generated)

## Exit Codes

- `0` - All tests passed
- `1` - Tests failed
- `255` - PHP fatal error

## Troubleshooting

### If migrations fail:
- Check if new migrations have SQLite-incompatible operations
- Add driver detection like in the fixed migration

### If tests fail with "null channel":
- Database needs seeding - use one of the seeding solutions above

### If coverage is 0%:
- Ensure PCOV is enabled: `php -d pcov.enabled=1`
- Check that `--coverage-filter` points to the correct source directory
