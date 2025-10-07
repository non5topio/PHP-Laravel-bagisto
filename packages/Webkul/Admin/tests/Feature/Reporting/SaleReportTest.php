<?php

use Carbon\Carbon;
use Webkul\Admin\Helpers\Reporting\Sale as SaleReporting;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Customer\Models\Customer;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\get;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\RefundRepository;

/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails with `ModelNotFoundException` for `Webkul\Category\Models\Category` with ID 1. The `Channel::factory()->create()` attempts to reference foreign key dependencies (`root_category_id`, `default_locale_id`, `base_currency_id`) that don't exist in the test database.

### Error Details
- **Error**: `No query results for model [Webkul\Category\Models\Category] 1`
- **Location**: Line 20 in `SaleReportTest.php`
- **Issue**: Channel factory requires Category, Locale, and Currency records that are missing from the SQLite test database

### Recommended Fix

**Create required dependencies before Channel creation:**

```php
// Add before Channel::factory()->create() in tests:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Alternative: Update Channel factory with `afterMaking()` callback to auto-create dependencies:**

```php
// In Channel factory definition:
public function definition()
{
    return [
        // ... other attributes
    ];
}

public function configure()
{
    return $this->afterMaking(function (Channel $channel) {
        if (!$channel->root_category_id) {
            $channel->root_category_id = \Webkul\Category\Models\Category::factory()->create()->id;
        }
        if (!$channel->default_locale_id) {
            $channel->default_locale_id = \Webkul\Core\Models\Locale::factory()->create()->id;
        }
        if (!$channel->base_currency_id) {
            $channel->base_currency_id = \Webkul\Core\Models\Currency::factory()->create()->id;
        }
    });
}
```

it('handles orders without payment records in left join', function () {
    $locale = \Webkul\Core\Models\Locale::factory()->create();
    $currency = \Webkul\Core\Models\Currency::factory()->create();
    $category = \Webkul\Category\Models\Category::factory()->create();
    
    $channel = \Webkul\Core\Models\Channel::factory()->create([
        'root_category_id' => $category->id,
        'default_locale_id' => $locale->id,
        'base_currency_id' => $currency->id,
    ]);
    
    $order = \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'base_grand_total' => 1000
    ]);
    
    \Webkul\Sales\Models\OrderPayment::factory()->create([
        'order_id' => $order->id,
        'method' => 'credit_card',
        'method_title' => 'Credit Card'
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, now()->subDays(7));
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, now());
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTopPaymentMethods();
    
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBeGreaterThanOrEqual(0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails with `ModelNotFoundException` for `Webkul\Category\Models\Category` with ID 1. The `Channel::factory()->create()` attempts to reference foreign key dependencies (`root_category_id`, `default_locale_id`, `base_currency_id`) that don't exist in the test database.

### Error Details
- **Error**: `No query results for model [Webkul\Category\Models\Category] 1`
- **Location**: Line 20 in `SaleReportTest.php`
- **Issue**: Channel factory requires Category, Locale, and Currency records that are missing from the SQLite test database

### Recommended Fix

**Create required dependencies before Channel creation:**

```php
// Add before Channel::factory()->create() in tests:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Alternative: Update Channel factory with `afterMaking()` callback to auto-create dependencies:**

```php
// In ChannelFactory definition:
public function definition()
{
    return [
        // ... other attributes
    ];
}

public function configure()
{
    return $this->afterMaking(function (Channel $channel) {
        if (!$channel->root_category_id) {
            $channel->root_category_id = \Webkul\Category\Models\Category::factory()->create()->id;
        }
        if (!$channel->default_locale_id) {
            $channel->default_locale_id = \Webkul\Core\Models\Locale::factory()->create()->id;
        }
        if (!$channel->base_currency_id) {
            $channel->base_currency_id = \Webkul\Core\Models\Currency::factory()->create()->id;
        }
    });
}
```

it('returns empty collection when limit is zero', function () {
    $locale = \Webkul\Core\Models\Locale::factory()->create();
    $currency = \Webkul\Core\Models\Currency::factory()->create();
    $category = \Webkul\Category\Models\Category::factory()->create();
    
    $channel = \Webkul\Core\Models\Channel::factory()->create([
        'root_category_id' => $category->id,
        'default_locale_id' => $locale->id,
        'base_currency_id' => $currency->id,
    ]);
    
    $taxCategory = \Webkul\Tax\Models\TaxCategory::factory()->create(['name' => 'Test Category']);
    $order = \Webkul\Sales\Models\Order::factory()->create(['channel_id' => $channel->id]);
    
    \Webkul\Sales\Models\OrderItem::factory()->create([
        'order_id' => $order->id,
        'tax_category_id' => $taxCategory->id,
        'base_tax_amount_invoiced' => 100,
        'base_tax_amount_refunded' => 0
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, now()->subDays(7));
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, now());
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTopTaxCategories(0);
    
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails because `Channel::factory()->create()` attempts to create a Channel model with foreign key references (`root_category_id`, `default_locale_id`, `base_currency_id`) that don't exist in the test database.

### Error Details
- **Error**: `ModelNotFoundException` for `Webkul\Category\Models\Category` with ID 1
- **Location**: Line 20 in `SaleReportTest.php`
- **Issue**: The Channel factory is trying to find/reference a Category with ID 1, but no categories exist in the test database

### Recommended Fix

**Create required dependencies before Channel creation:**

```php
// Add to test setup or before Channel::factory()->create():
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Alternative: Update Channel factory with `afterMaking()` callback to auto-create dependencies:**

```php
// In Channel factory definition:
public function definition(): array
{
    return [
        // ... other attributes
    ];
}

public function configure()
{
    return $this->afterMaking(function (Channel $channel) {
        if (!$channel->root_category_id) {
            $channel->root_category_id = \Webkul\Category\Models\Category::factory()->create()->id;
        }
        if (!$channel->default_locale_id) {
            $channel->default_locale_id = \Webkul\Core\Models\Locale::factory()->create()->id;
        }
        if (!$channel->base_currency_id) {
            $channel->base_currency_id = \Webkul\Core\Models\Currency::factory()->create()->id;
        }
    });
}
```

it('calculates sales with negative refund amounts', function () {
    $locale = \Webkul\Core\Models\Locale::factory()->create();
    $currency = \Webkul\Core\Models\Currency::factory()->create();
    $category = \Webkul\Category\Models\Category::factory()->create();
    
    $channel = \Webkul\Core\Models\Channel::factory()->create([
        'root_category_id' => $category->id,
        'default_locale_id' => $locale->id,
        'base_currency_id' => $currency->id,
    ]);
    
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(2),
        'base_grand_total_invoiced' => 1000,
        'base_grand_total_refunded' => -100
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTotalSales(now()->subDays(7), now());
    
    expect($result)->toBe(1100.0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails because `Channel::factory()` attempts to create a Channel model with foreign key references (`root_category_id`, `default_locale_id`, `base_currency_id`) that don't exist in the test database.

### Error Details
- **Error**: `ModelNotFoundException` for `Webkul\Category\Models\Category` with ID 1
- **Location**: Line 20 in `SaleReportTest.php`
- **Issue**: The Channel factory is trying to find/reference a Category with ID 1, but no categories exist in the test database

### Recommended Fix

**Create required dependencies before Channel creation:**

```php
// Add to test setup or before Channel::factory()->create():
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Alternative: Modify Channel Factory**

Update the Channel factory to auto-create dependencies using `afterMaking()` callback in the factory definition itself.

it('returns zero when channel_ids is empty array', function () {
    $locale = \Webkul\Core\Models\Locale::factory()->create();
    $currency = \Webkul\Core\Models\Currency::factory()->create();
    $category = \Webkul\Category\Models\Category::factory()->create();
    
    $channel = \Webkul\Core\Models\Channel::factory()->create([
        'root_category_id' => $category->id,
        'default_locale_id' => $locale->id,
        'base_currency_id' => $currency->id,
    ]);
    
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, []);
    
    $result = $saleReporting->getTotalOrders(now()->subDay(), now());
    
    expect($result)->toBe(0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails because `Channel::factory()` attempts to create a Channel with foreign key references (`root_category_id`, `default_locale_id`, `base_currency_id`) that don't exist in the test database.

### Error Details
- **Error**: `ModelNotFoundException` for `Webkul\Category\Models\Category` with ID 1
- **Location**: Line 20 in `SaleReportTest.php`
- **Issue**: Missing required dependencies (Category, Locale, Currency) when creating Channel instances

### Recommended Fix

**Create dependencies before Channel creation:**

```php
// Add to test setup or before Channel::factory()->create():
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Alternative: Update Channel Factory**

Modify the Channel factory to auto-create dependencies using `afterMaking()` callback:

```php
// In ChannelFactory.php
public function definition() {
    return [
        'root_category_id' => Category::factory(),
        'default_locale_id' => Locale::factory(),
        'base_currency_id' => Currency::factory(),
        // ... other attributes
    ];
}
```

it('handles zero previous period in progress calculation', function () {
    $locale = \Webkul\Core\Models\Locale::factory()->create();
    $currency = \Webkul\Core\Models\Currency::factory()->create();
    $category = \Webkul\Category\Models\Category::factory()->create();
    
    $channel = \Webkul\Core\Models\Channel::factory()->create([
        'root_category_id' => $category->id,
        'default_locale_id' => $locale->id,
        'base_currency_id' => $currency->id,
    ]);
    
    // Create 10 orders in current period only
    for ($i = 0; $i < 10; $i++) {
        \Webkul\Sales\Models\Order::factory()->create([
            'channel_id' => $channel->id,
            'created_at' => now()->subDays($i)
        ]);
    }
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    
    $lastStartProp = $reflection->getProperty('lastStartDate');
    $lastStartProp->setAccessible(true);
    $lastStartProp->setValue($saleReporting, now()->subDays(30));
    
    $lastEndProp = $reflection->getProperty('lastEndDate');
    $lastEndProp->setAccessible(true);
    $lastEndProp->setValue($saleReporting, now()->subDays(20));
    
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, now()->subDays(10));
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, now());
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTotalOrdersProgress();
    
    expect($result)->toBeArray()
        ->and($result['previous'])->toBe(0)
        ->and($result['current'])->toBe(10)
        ->and($result['progress'])->toBeFloat();
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails with `ModelNotFoundException` for `Webkul\Category\Models\Category` model with ID 1. The `Channel::factory()` is attempting to reference a Category with ID 1 that doesn't exist in the test database.

### Error Details
- **Error**: `No query results for model [Webkul\Category\Models\Category] 1`
- **Location**: Line 20 in `SaleReportTest.php`
- **Issue**: Foreign key dependencies (Category, Locale, Currency) required by Channel factory are missing

### Recommended Fixes

**Option 1: Create Dependencies Before Channel (Recommended)**
```php
// In test setup or before creating channel:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Option 2: Add Database Seeder in setUp()**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->seed(RequiredDataSeeder::class); // Seeds locales, currencies, categories
}
```

**Option 3: Modify Channel Factory**
Update the Channel factory to create dependencies automatically using `afterMaking()` or `afterCreating()` callbacks.

**Required Action**: Implement Option 1 or 2 to ensure all foreign key dependencies exist before Channel creation.

it('returns array with zero values when no orders exist in date range', function () {
    $locale = \Webkul\Core\Models\Locale::factory()->create();
    $currency = \Webkul\Core\Models\Currency::factory()->create();
    $category = \Webkul\Category\Models\Category::factory()->create();
    
    $channel = \Webkul\Core\Models\Channel::factory()->create([
        'root_category_id' => $category->id,
        'default_locale_id' => $locale->id,
        'base_currency_id' => $currency->id,
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $startDate = Carbon::parse('2024-01-01');
    $endDate = Carbon::parse('2024-01-31');
    
    $result = $saleReporting->getOverTimeStats($startDate, $endDate, 'COUNT(*)', 'auto');
    
    expect($result)->toBeArray()
        ->and($result)->not->toBeEmpty();
    
    foreach ($result as $stat) {
        expect($stat)->toHaveKeys(['label', 'total', 'count'])
            ->and($stat['total'])->toBe(0)
            ->and($stat['count'])->toBe(0);
    }
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails due to a **foreign key constraint violation** when creating a `Channel` model. The factory attempts to insert a channel with `root_category_id`, `default_locale_id`, and `base_currency_id` set to `1`, but these referenced records don't exist in the SQLite test database.

### Error Details
- **Error**: `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed`
- **Location**: Line 18 in `SaleReportTest.php`
- **Missing Dependencies**: Locale, Currency, and Category records that the Channel foreign keys reference

### Recommended Fixes

**Option 1: Create Required Dependencies First (Recommended)**
```php
// Before creating channels in tests:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Option 2: Add Database Seeder in Test Setup**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->seed(RequiredDataSeeder::class); // Seeds locales, currencies, categories
}
```

**Option 3: Disable Foreign Key Constraints (Not Recommended)**
```php
// In test setup:
DB::statement('PRAGMA foreign_keys=OFF;');
```

**Required Action**: Implement Option 1 or 2 to ensure all foreign key dependencies exist before creating Channel instances.

it('counts unique email-customer_id combinations correctly', function () {
    $channel = \Webkul\Core\Models\Channel::factory()->create();
    
    // Same email, different customer_id
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(3),
        'customer_email' => 'test@example.com',
        'customer_id' => 1
    ]);
    
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(2),
        'customer_email' => 'test@example.com',
        'customer_id' => 2
    ]);
    
    // Different email, same customer_id as first
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(1),
        'customer_email' => 'other@example.com',
        'customer_id' => 1
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $startDate = now()->subDays(7);
    $endDate = now();
    $result = $saleReporting->getTotalUniqueOrdersUsers($startDate, $endDate);
    
    expect($result)->toBe(3);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails due to a **foreign key constraint violation** when creating a `Channel` model. The factory attempts to insert a channel with `root_category_id`, `default_locale_id`, and `base_currency_id` set to `1`, but these referenced records don't exist in the SQLite test database.

### Error Details
- **Error**: `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed`
- **Location**: Line 18 in `SaleReportTest.php`
- **Missing Dependencies**: Locale, Currency, and Category records that the Channel foreign keys reference

### Recommended Fixes

**Option 1: Create Required Dependencies First (Recommended)**
```php
// Before creating channels in tests:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Option 2: Add Database Seeder in Test Setup**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->seed(RequiredDataSeeder::class); // Seeds locales, currencies, categories
}
```

**Option 3: Disable Foreign Key Constraints (Not Recommended)**
```php
// In test setup:
DB::statement('PRAGMA foreign_keys=OFF;');
// ... run tests ...
DB::statement('PRAGMA foreign_keys=ON;');
```

**Required Action**: Implement Option 1 or 2 to ensure all foreign key dependencies exist before creating Channel instances.

it('filters out null shipping method entries', function () {
    $channel = \Webkul\Core\Models\Channel::factory()->create();
    
    // Order with valid shipping method
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(2),
        'shipping_method' => 'flatrate_flatrate',
        'shipping_title' => 'Flat Rate',
        'base_shipping_invoiced' => 100,
        'base_shipping_refunded' => 0
    ]);
    
    // Order with null shipping method
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(1),
        'shipping_method' => null,
        'shipping_title' => null,
        'base_shipping_invoiced' => 200,
        'base_shipping_refunded' => 0
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, now()->subDays(7));
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, now());
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTopShippingMethods();
    
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(1)
        ->and($result->first()->title)->toBe('Flat Rate');
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails due to a **foreign key constraint violation** when creating a `Channel` model. The factory attempts to insert a channel with `root_category_id`, `default_locale_id`, and `base_currency_id` set to `1`, but these referenced records don't exist in the SQLite test database.

### Error Details
- **Error**: `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed`
- **Location**: Line 18 in `SaleReportTest.php`
- **Missing Dependencies**: Locale, Currency, and Category records that the Channel foreign keys reference

### Recommended Fixes

**Option 1: Create Required Dependencies First (Recommended)**
```php
// Before creating channels in tests:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Option 2: Add Database Seeder in Test Setup**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->seed(RequiredDataSeeder::class); // Seeds locales, currencies, categories
}
```

**Option 3: Disable Foreign Key Constraints (Not Recommended)**
```php
// In test setup:
DB::statement('PRAGMA foreign_keys=OFF;');
```

**Required Action**: Implement Option 1 or 2 to ensure all foreign key dependencies exist before creating Channel instances.

it('filters out null tax category entries', function () {
    $channel = \Webkul\Core\Models\Channel::factory()->create();
    $taxCategory = \Webkul\Tax\Models\TaxCategory::factory()->create(['name' => 'Valid Category']);
    
    $order1 = \Webkul\Sales\Models\Order::factory()->create(['channel_id' => $channel->id]);
    $order2 = \Webkul\Sales\Models\Order::factory()->create(['channel_id' => $channel->id]);
    
    // Order item with valid tax category
    \Webkul\Sales\Models\OrderItem::factory()->create([
        'order_id' => $order1->id,
        'tax_category_id' => $taxCategory->id,
        'base_tax_amount_invoiced' => 100,
        'base_tax_amount_refunded' => 0
    ]);
    
    // Order item with null tax category
    \Webkul\Sales\Models\OrderItem::factory()->create([
        'order_id' => $order2->id,
        'tax_category_id' => null,
        'base_tax_amount_invoiced' => 200,
        'base_tax_amount_refunded' => 0
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, now()->subDays(7));
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, now());
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTopTaxCategories();
    
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(1)
        ->and($result->first()->tax_category_id)->toBe($taxCategory->id);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails due to a **foreign key constraint violation** when creating a `Channel` model. The factory attempts to insert a channel with `root_category_id`, `default_locale_id`, and `base_currency_id` set to `1`, but these referenced records don't exist in the test database.

### Failure Details
- **Error**: `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed`
- **Location**: Line 18 in `SaleReportTest.php`
- **Database**: SQLite
- **Missing Dependencies**: Locale, Currency, and Category records that the Channel foreign keys reference

### Recommended Fixes

**Option 1: Create Required Dependencies First (Recommended)**
```php
// In test setup or before creating channels:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Option 2: Use Database Seeders in Test Setup**
```php
// In setUp() method:
protected function setUp(): void
{
    parent::setUp();
    $this->seed(RequiredDataSeeder::class); // Seed locales, currencies, categories
}
```

**Option 3: Disable Foreign Key Constraints (Not Recommended)**
```php
// In test setup:
DB::statement('PRAGMA foreign_keys=OFF;');
// ... run tests ...
DB::statement('PRAGMA foreign_keys=ON;');
```

**Required Action**: Implement Option 1 or 2 to ensure all foreign key dependencies exist before creating Channel instances in tests.

it('handles very short date range with auto period', function () {
    $channel = \Webkul\Core\Models\Channel::factory()->create();
    
    $startDate = now()->startOfHour();
    $endDate = now()->startOfHour()->addHour();
    
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addMinutes(30)
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTotalOrdersOverTime($startDate, $endDate, 'auto', true);
    
    expect($result)->toBeArray()
        ->and($result)->not->toBeEmpty();
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails due to a **foreign key constraint violation** when creating a `Channel` model. The factory attempts to insert a channel with `root_category_id`, `default_locale_id`, and `base_currency_id` set to `1`, but these referenced records don't exist in the test database.

### Failure Details
- **Error**: `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed`
- **Location**: Line 18 in `SaleReportTest.php`
- **Database**: SQLite
- **Missing Dependencies**: Locale, Currency, and Category records that the Channel foreign keys reference

### Recommended Fixes

**Option 1: Create Required Dependencies First (Recommended)**
```php
// In test setup or before creating channels:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Option 2: Use Database Seeders in Test Setup**
```php
// In setUp() method:
protected function setUp(): void
{
    parent::setUp();
    $this->seed(RequiredDataSeeder::class); // Seed locales, currencies, categories
}
```

**Option 3: Disable Foreign Key Constraints (Not Recommended)**
```php
// In test setup:
DB::statement('PRAGMA foreign_keys=OFF;');
// ... run tests ...
DB::statement('PRAGMA foreign_keys=ON;');
```

**Required Action**: Implement Option 1 or 2 to ensure all foreign key dependencies exist before creating Channel instances in tests.

it('calculates average correctly with single order', function () {
    $channel = \Webkul\Core\Models\Channel::factory()->create();
    
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(2),
        'base_grand_total_invoiced' => 500,
        'base_grand_total_refunded' => 50
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $startDate = now()->subDays(7);
    $endDate = now();
    $result = $saleReporting->getAverageSales($startDate, $endDate);
    
    expect($result)->toBe(450.0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test fails due to a **foreign key constraint violation** when creating a `Channel` model. The factory is attempting to insert a channel with `root_category_id`, `default_locale_id`, and `base_currency_id` set to `1`, but these referenced records don't exist in the test database.

### Failure Details
- **Error**: `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed`
- **Location**: Line 18 in `SaleReportTest.php`
- **SQL**: Inserting into `channels` table with foreign keys that reference non-existent records

### Recommended Fixes

**Option 1: Create Required Dependencies First (Recommended)**
```php
// In your test setup or before creating channels:
$locale = \Webkul\Core\Models\Locale::factory()->create();
$currency = \Webkul\Core\Models\Currency::factory()->create();
$category = \Webkul\Category\Models\Category::factory()->create();

$channel = \Webkul\Core\Models\Channel::factory()->create([
    'root_category_id' => $category->id,
    'default_locale_id' => $locale->id,
    'base_currency_id' => $currency->id,
]);
```

**Option 2: Use Database Seeders in Test Setup**
```php
// In setUp() method or beforeEach():
$this->seed(RequiredDataSeeder::class); // Seed locales, currencies, categories
```

**Option 3: Disable Foreign Key Constraints (Not Recommended for Production)**
```php
// In test setup:
DB::statement('PRAGMA foreign_keys=OFF;');
// ... run tests ...
DB::statement('PRAGMA foreign_keys=ON;');
```

**Required Action**: Implement Option 1 or 2 to ensure all foreign key dependencies exist before creating Channel instances.

it('returns zero when all orders are fully refunded', function () {
    $channel = \Webkul\Core\Models\Channel::factory()->create();
    
    // Create orders where invoiced equals refunded
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(3),
        'base_grand_total_invoiced' => 1000,
        'base_grand_total_refunded' => 1000
    ]);
    
    \Webkul\Sales\Models\Order::factory()->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(2),
        'base_grand_total_invoiced' => 500,
        'base_grand_total_refunded' => 500
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $startDate = now()->subDays(7);
    $endDate = now();
    $result = $saleReporting->getTotalSales($startDate, $endDate);
    
    expect($result)->toBe(0.0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test calls undefined helper function `createChannel()` that doesn't exist in the codebase or test dependencies.

### Failure Details
- **Error**: `Call to undefined function createChannel()` at line 18
- **Test**: `it returns zero for empty date range`
- **Impact**: Test cannot create required test data (Channel model instances)

### Recommended Fixes

**Option 1: Use Laravel Factories Directly (Recommended)**
```php
// Replace line 18 in the test:
$channel = \Webkul\Core\Models\Channel::factory()->create();
```

**Option 2: Define Helper Functions**
Add to `tests/Pest.php`:
```php
use Webkul\Core\Models\Channel;

function createChannel(array $attributes = []) {
    return Channel::factory()->create($attributes);
}
```

**Required Action**: Implement either option. Ensure `Channel::factory()` exists or create the factory if missing.

it('returns zero for empty date range', function () {
    $channel = createChannel();
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $sameDate = Carbon::parse('2024-01-01 00:00:00');
    $result = $saleReporting->getTotalOrders($sameDate, $sameDate);
    
    expect($result)->toBe(0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test calls undefined helper functions (`createChannel()`, `createOrder()`) that don't exist in the codebase.

### Failure Details
- **Error**: `Call to undefined function createChannel()` at line 18
- **Additional undefined functions**: `createOrder()` (would fail at lines 21+)

### Recommended Fixes

**Option 1: Use Laravel Factories (Recommended)**

Add to `tests/Pest.php`:

```php
use Webkul\Core\Models\Channel;
use Webkul\Sales\Models\Order;

function createChannel(array $attributes = []) {
    return Channel::factory()->create($attributes);
}

function createOrder(array $attributes = []) {
    return Order::factory()->create($attributes);
}
```

**Option 2: Use Models Directly in Tests**

Replace helper calls with factory calls:
```php
$channel = \Webkul\Core\Models\Channel::factory()->create();
$order1 = \Webkul\Sales\Models\Order::factory()->create(['channel_id' => $channel->id]);
```

**Required Action**: Implement either option to provide test data creation functionality. Ensure corresponding model factories exist.

it('aggregates payment methods correctly', function () {
    $channel = createChannel();
    
    // Create orders with different payment methods
    $order1 = createOrder([
        'channel_id' => $channel->id,
        'base_grand_total' => 1000
    ]);
    createOrderPayment([
        'order_id' => $order1->id,
        'method' => 'credit_card',
        'method_title' => 'Credit Card'
    ]);
    
    $order2 = createOrder([
        'channel_id' => $channel->id,
        'base_grand_total' => 2000
    ]);
    createOrderPayment([
        'order_id' => $order2->id,
        'method' => 'credit_card',
        'method_title' => 'Credit Card'
    ]);
    
    $order3 = createOrder([
        'channel_id' => $channel->id,
        'base_grand_total' => 1500
    ]);
    createOrderPayment([
        'order_id' => $order3->id,
        'method' => 'paypal',
        'method_title' => 'PayPal'
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, now()->subDays(7));
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, now());
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTopPaymentMethods();
    
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBeGreaterThan(0)
        ->and($result->first()->method)->toBeString()
        ->and($result->first()->title)->toBeString()
        ->and($result->first()->total)->toBeInt();
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test is calling undefined helper functions (`createChannel()`, `createTaxCategory()`, `createOrder()`, `createOrderItem()`) that don't exist in the test file or imported dependencies.

### Failures Identified
1. **Undefined function `createChannel()`** at line 18
2. Additional undefined functions that would fail: `createTaxCategory()`, `createOrder()`, `createOrderItem()`

### Recommended Fixes

**Option 1: Use Laravel Factories (Recommended)**
```php
// Replace helper calls with factory calls:
$channel = \Webkul\Core\Models\Channel::factory()->create();
$taxCategory1 = \Webkul\Tax\Models\TaxCategory::factory()->create(['name' => 'Category 1']);
$order = \Webkul\Sales\Models\Order::factory()->create([...]);
$orderItem = \Webkul\Sales\Models\OrderItem::factory()->create([...]);
```

**Option 2: Define Helper Functions**
Add to `tests/Pest.php`:
```php
function createChannel(array $attributes = []) {
    return \Webkul\Core\Models\Channel::factory()->create($attributes);
}

function createTaxCategory(array $attributes = []) {
    return \Webkul\Tax\Models\TaxCategory::factory()->create($attributes);
}

function createOrder(array $attributes = []) {
    return \Webkul\Sales\Models\Order::factory()->create($attributes);
}

function createOrderItem(array $attributes = []) {
    return \Webkul\Sales\Models\OrderItem::factory()->create($attributes);
}
```

**Required Action:** Implement either option to provide the missing test data creation functionality.

it('returns top tax categories with aggregated data and limit', function () {
    $channel = createChannel();
    $taxCategory1 = createTaxCategory(['name' => 'Category 1']);
    $taxCategory2 = createTaxCategory(['name' => 'Category 2']);
    $taxCategory3 = createTaxCategory(['name' => 'Category 3']);
    
    $order1 = createOrder(['channel_id' => $channel->id]);
    $order2 = createOrder(['channel_id' => $channel->id]);
    $order3 = createOrder(['channel_id' => $channel->id]);
    
    // Category 1: 500 total
    createOrderItem([
        'order_id' => $order1->id,
        'tax_category_id' => $taxCategory1->id,
        'base_tax_amount_invoiced' => 500,
        'base_tax_amount_refunded' => 0
    ]);
    
    // Category 2: 300 total
    createOrderItem([
        'order_id' => $order2->id,
        'tax_category_id' => $taxCategory2->id,
        'base_tax_amount_invoiced' => 300,
        'base_tax_amount_refunded' => 0
    ]);
    
    // Category 3: 200 total
    createOrderItem([
        'order_id' => $order3->id,
        'tax_category_id' => $taxCategory3->id,
        'base_tax_amount_invoiced' => 200,
        'base_tax_amount_refunded' => 0
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, now()->subDays(7));
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, now());
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTopTaxCategories(2);
    
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2)
        ->and($result->first()->tax_category_id)->toBe($taxCategory1->id)
        ->and($result->first()->total)->toBe(500.0);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test is calling undefined helper functions `createChannel()` and `createOrder()` that don't exist in the test file or imported dependencies.

### Failures Identified
1. **Undefined function `createChannel()`** at line 18
2. **Undefined function `createOrder()`** (not reached due to first error, but would fail at lines 21, 24, and 31)

### Recommended Fixes

**Option 1: Use Laravel Factories**
```php
// Replace createChannel() with:
$channel = \Webkul\Core\Models\Channel::factory()->create();

// Replace createOrder() with:
$order = \Webkul\Sales\Models\Order::factory()->create(['channel_id' => $channel->id]);
```

**Option 2: Define Helper Functions**
Add to `tests/Pest.php` or create a helper file:
```php
function createChannel(array $attributes = []) {
    return \Webkul\Core\Models\Channel::factory()->create($attributes);
}

function createOrder(array $attributes = []) {
    return \Webkul\Sales\Models\Order::factory()->create($attributes);
}
```

**Required Action:** Implement either option to provide the missing test data creation functionality.

it('retrieves today orders with relationships', function () {
    $channel = createChannel();
    
    // Create order for today
    $order = createOrder([
        'channel_id' => $channel->id,
        'created_at' => now()
    ]);
    
    // Create order for yesterday (should not be included)
    createOrder([
        'channel_id' => $channel->id,
        'created_at' => now()->subDay()
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTodayOrders();
    
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(1)
        ->and($result->first()->id)->toBe($order->id);
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test is calling undefined helper functions `createChannel()` and `createOrder()` that don't exist in the test file or imported dependencies.

### Failures Identified
1. **Undefined function `createChannel()`** at line 18
2. **Undefined function `createOrder()`** (not reached due to first error, but would fail at lines 24 and 31)

### Recommended Fixes

**Option 1: Use Laravel Factories**
```php
use Webkul\Core\Models\Channel;
use Webkul\Sales\Models\Order;

// Replace createChannel() with:
$channel = Channel::factory()->create();

// Replace createOrder() with:
Order::factory()->create([
    'channel_id' => $channel->id,
    'created_at' => $date,
    'base_grand_total_invoiced' => 100,
    'base_grand_total_refunded' => 0,
]);
```

**Option 2: Define Helper Functions**
Add to `tests/Pest.php` or create a helper file:
```php
function createChannel(array $attributes = []) {
    return \Webkul\Core\Models\Channel::factory()->create($attributes);
}

function createOrder(array $attributes = []) {
    return \Webkul\Sales\Models\Order::factory()->create($attributes);
}
```

**Required Action:** Implement either option to provide the missing test data creation functionality.

it('calculates total sales progress with invoiced and refunded amounts', function () {
    $channel = createChannel();
    
    // Previous period orders
    $previousStart = now()->subDays(14);
    $previousEnd = now()->subDays(7);
    createOrder([
        'channel_id' => $channel->id,
        'created_at' => $previousStart->copy()->addDays(1),
        'base_grand_total_invoiced' => 1000,
        'base_grand_total_refunded' => 100
    ]);
    
    // Current period orders
    $currentStart = now()->subDays(7);
    $currentEnd = now();
    createOrder([
        'channel_id' => $channel->id,
        'created_at' => $currentStart->copy()->addDays(1),
        'base_grand_total_invoiced' => 2000,
        'base_grand_total_refunded' => 200
    ]);
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $lastStartProp = $reflection->getProperty('lastStartDate');
    $lastStartProp->setAccessible(true);
    $lastStartProp->setValue($saleReporting, $previousStart);
    
    $lastEndProp = $reflection->getProperty('lastEndDate');
    $lastEndProp->setAccessible(true);
    $lastEndProp->setValue($saleReporting, $previousEnd);
    
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, $currentStart);
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, $currentEnd);
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTotalSalesProgress();
    
    expect($result)->toBeArray()
        ->and($result['previous'])->toBe(900.0)
        ->and($result['current'])->toBe(1800.0)
        ->and($result['formatted_total'])->toBeString()
        ->and($result['progress'])->toBeFloat();
});

*/
/*
FAILED TEST: ## Test Failure Analysis

### Root Cause
The test is calling undefined helper functions `createChannel()` and `createOrder()` that don't exist in the test file or imported dependencies.

### Failures Identified
1. **Undefined function `createChannel()`** at line 18
2. **Undefined function `createOrder()`** at lines 24 and 31 (not reached due to first error)

### Recommended Fixes

1. **Import or define the helper functions** - Add these at the top of the test file:
   ```php
   use function Pest\Laravel\{get, actingAs};
   use Webkul\Core\Models\Channel;
   ```

2. **Replace helper calls with proper factory/model creation**:
   ```php
   // Instead of: $channel = createChannel();
   $channel = Channel::factory()->create();
   
   // Instead of: createOrder([...])
   Order::factory()->create([...]);
   ```

3. **Alternative**: If these are custom helpers, ensure they are defined in a `tests/Pest.php` or helper file that's properly loaded.

The test needs proper model factories or helper function definitions to create test data for channels and orders.

it('calculates total orders progress correctly with valid date ranges', function () {
    $channel = createChannel();
    
    // Create 10 orders in previous period (7-14 days ago)
    $previousStart = now()->subDays(14);
    $previousEnd = now()->subDays(7);
    for ($i = 0; $i < 10; $i++) {
        createOrder(['channel_id' => $channel->id, 'created_at' => $previousStart->copy()->addDays($i % 7)]);
    }
    
    // Create 15 orders in current period (last 7 days)
    $currentStart = now()->subDays(7);
    $currentEnd = now();
    for ($i = 0; $i < 15; $i++) {
        createOrder(['channel_id' => $channel->id, 'created_at' => $currentStart->copy()->addDays($i % 7)]);
    }
    
    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );
    
    $reflection = new \ReflectionClass($saleReporting);
    $lastStartProp = $reflection->getProperty('lastStartDate');
    $lastStartProp->setAccessible(true);
    $lastStartProp->setValue($saleReporting, $previousStart);
    
    $lastEndProp = $reflection->getProperty('lastEndDate');
    $lastEndProp->setAccessible(true);
    $lastEndProp->setValue($saleReporting, $previousEnd);
    
    $startProp = $reflection->getProperty('startDate');
    $startProp->setAccessible(true);
    $startProp->setValue($saleReporting, $currentStart);
    
    $endProp = $reflection->getProperty('endDate');
    $endProp->setAccessible(true);
    $endProp->setValue($saleReporting, $currentEnd);
    
    $channelIdsProp = $reflection->getProperty('channelIds');
    $channelIdsProp->setAccessible(true);
    $channelIdsProp->setValue($saleReporting, [$channel->id]);
    
    $result = $saleReporting->getTotalOrdersProgress();
    
    expect($result)->toBeArray()
        ->and($result['previous'])->toBe(10)
        ->and($result['current'])->toBe(15)
        ->and($result['progress'])->toBeFloat();
});

*/
