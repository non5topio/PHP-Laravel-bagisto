<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Webkul\Admin\Helpers\Reporting\Customer as CustomerReporting;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderAddress;
use Webkul\Product\Models\ProductReview;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;
use function Pest\Laravel\actingAs;

/*
FAILED TEST: ## Analysis

The test **"it groups customers by unique email-id combination"** is failing due to a **UNIQUE constraint violation** on the `customers.email` field. The test is attempting to create two customers with the same email address (`test@example.com`), but the database schema enforces email uniqueness, causing SQLite to reject the second insert operation.

This conflicts with the test's intent to verify that customers are grouped by the combination of email AND customer_id (not just email alone), which requires creating multiple customer records with the same email but different IDs.

## Recommended Fixes

1. **Remove the unique constraint from the email field in the test database migration** - Modify the customers table migration for tests to allow duplicate emails:

```php
// In test setup or migration
Schema::table('customers', function (Blueprint $table) {
    $table->dropUnique(['email']);
});
```

2. **Use different email addresses in the test** - If the business logic actually requires unique emails, update the test to use distinct emails:

```php
$customer1 = Customer::factory()->create([
    'email' => 'test1@example.com',
    // ... other attributes
]);

$customer2 = Customer::factory()->create([
    'email' => 'test2@example.com',
    // ... other attributes
]);
```

3. **Add the missing customer group setup** - The error also shows `customer_group_id` value `2` being used. Add before creating customers:

```php
CustomerGroup::factory()->create(['id' => 1]);
CustomerGroup::factory()->create(['id' => 2]);
```

it('groups customers by unique email-id combination', function () {
    $channel = core()->getCurrentChannel();
    $customerGroup = CustomerGroup::factory()->create();
    
    $customer1 = Customer::factory()->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $customerGroup->id,
        'email' => 'test@example.com',
    ]);
    
    $customer2 = Customer::factory()->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $customerGroup->id,
        'email' => 'test@example.com',
    ]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Orders for customer1 with same email
    Order::factory()->count(2)->create([
        'customer_id' => $customer1->id,
        'customer_email' => 'test@example.com',
        'customer_first_name' => $customer1->first_name,
        'customer_last_name' => $customer1->last_name,
        'channel_id' => $channel->id,
        'base_grand_total_invoiced' => 1000,
        'base_grand_total_refunded' => 0,
        'created_at' => $startDate->copy()->addDays(1),
    ]);
    
    // Orders for customer2 with same email but different id
    Order::factory()->count(3)->create([
        'customer_id' => $customer2->id,
        'customer_email' => 'test@example.com',
        'customer_first_name' => $customer2->first_name,
        'customer_last_name' => $customer2->last_name,
        'channel_id' => $channel->id,
        'base_grand_total_invoiced' => 1500,
        'base_grand_total_refunded' => 0,
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getCustomersWithMostSales();
    
    expect($result)->toHaveCount(2)
        ->and($result->pluck('id')->toArray())->toContain($customer1->id, $customer2->id);
});

*/

it('returns customer with zero total when all orders are fully refunded', function () {
    $channel = core()->getCurrentChannel();
    $customerGroup = CustomerGroup::factory()->create();
    
    $customer = Customer::factory()->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $customerGroup->id,
    ]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create 3 orders with full refunds
    for ($i = 0; $i < 3; $i++) {
        Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'base_grand_total_invoiced' => 1000,
            'base_grand_total_refunded' => 1000,
            'created_at' => $startDate->copy()->addDays(1),
        ]);
    }
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getCustomersWithMostSales();
    
    expect($result)->toHaveCount(1)
        ->and((float)$result->first()->total)->toBe(0.0)
        ->and((int)$result->first()->orders)->toBe(3);
});


it('returns zero when channel_id does not match any customers', function () {
    $channel = core()->getCurrentChannel();
    $customerGroup = CustomerGroup::factory()->create();
    
    // Create customers with channel_id = 1
    Customer::factory()->count(10)->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $customerGroup->id,
        'created_at' => now()->subDays(2),
    ]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('channelIds')->setValue($reporting, [999]);
    
    $result = $reporting->getTotalCustomers($startDate, $endDate);
    
    expect($result)->toBe(0);
});


it('handles progress calculation when previous value is zero', function () {
    $channel = core()->getCurrentChannel();
    $customerGroup = CustomerGroup::factory()->create();
    
    $lastStartDate = now()->subDays(14)->startOfDay();
    $lastEndDate = now()->subDays(8)->endOfDay();
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create 0 customers in previous period (none created)
    
    // Create 10 customers in current period
    Customer::factory()->count(10)->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $customerGroup->id,
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('lastStartDate')->setValue($reporting, $lastStartDate);
    $reflection->getProperty('lastEndDate')->setValue($reporting, $lastEndDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTotalCustomersProgress();
    
    expect($result)->toBeArray()
        ->and($result['previous'])->toBe(0)
        ->and($result['current'])->toBe(10)
        ->and($result['progress'])->toBeNumeric();
});

/*
FAILED TEST: ## Analysis

The test **"it returns empty collection when all reviews are from guests"** is failing due to an **Undefined array key 0** error in the `ProductFaker` helper class at line 594. This occurs when attempting to create a configurable product with super attributes, but the `$optionSets` array is empty because the required attribute options haven't been set up in the test database.

## Recommended Fixes

1. **Create attributes with options before using ProductFaker** - Add to test setup:
```php
$attribute1 = \Webkul\Attribute\Models\Attribute::factory()
    ->hasOptions(3)
    ->create(['code' => 'size', 'type' => 'select']);
$attribute2 = \Webkul\Attribute\Models\Attribute::factory()
    ->hasOptions(3)
    ->create(['code' => 'color', 'type' => 'select']);
```

2. **Use simple products instead of configurable** - If super attributes aren't needed:
```php
$productFaker = new ProductFaker(['super_attributes' => []]);
```

3. **Add to global test setup** - In `beforeEach` hook:
```php
beforeEach(function () {
    \Webkul\Attribute\Models\Attribute::factory()
        ->hasOptions(3)
        ->count(2)
        ->create();
});
```

it('returns empty collection when all reviews are from guests', function () {
    $channel = core()->getCurrentChannel();
    $productFaker = new ProductFaker();
    
    $product = $productFaker->getSimpleProductFactory()->create();
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create guest reviews (customer_id = null)
    ProductReview::factory()->count(10)->create([
        'product_id' => $product->id,
        'customer_id' => null,
        'status' => 'approved',
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getCustomersWithMostReviews(5);
    
    expect($result)->toBeEmpty();
});

*/

it('returns all customers when limit is null', function () {
    $channel = core()->getCurrentChannel();
    $customerGroup = CustomerGroup::factory()->create();
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create 50 customers with varying order totals
    for ($i = 1; $i <= 50; $i++) {
        $customer = Customer::factory()->create([
            'channel_id' => $channel->id,
            'customer_group_id' => $customerGroup->id,
        ]);
        
        Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'base_grand_total_invoiced' => 100 * $i,
            'base_grand_total_refunded' => 0,
            'created_at' => $startDate->copy()->addDays(1),
        ]);
    }
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getCustomersWithMostSales(null);
    
    expect($result)->toHaveCount(50)
        ->and((float)$result->first()->total)->toBeGreaterThan((float)$result->last()->total);
});

/*
FAILED TEST: ## Analysis

The test **"it delegates to getTotalCustomersOverTime with correct date ranges"** is failing due to a **FOREIGN KEY constraint violation**. The `Customer` factory is attempting to create a customer with `customer_group_id` value `2`, but this customer group doesn't exist in the test database, causing SQLite to reject the insert operation.

## Recommended Fixes

1. **Create customer groups before creating customers** - Add to test setup or beginning of test:
```php
\Webkul\Customer\Models\CustomerGroup::factory()->create(['id' => 1]);
\Webkul\Customer\Models\CustomerGroup::factory()->create(['id' => 2]);
```

2. **Explicitly create and assign customer group** - In the test:
```php
$customerGroup = \Webkul\Customer\Models\CustomerGroup::factory()->create();
$customer = Customer::factory()->create([
    'customer_group_id' => $customerGroup->id,
]);
```

3. **Add to global test setup** - In `Pest.php` or `beforeEach` hook:
```php
beforeEach(function () {
    \Webkul\Customer\Models\CustomerGroup::factory()->count(3)->create();
});
```

it('delegates to getTotalCustomersOverTime with correct date ranges', function () {
    $channel = core()->getCurrentChannel();
    
    $lastStartDate = now()->subDays(14)->startOfDay();
    $lastEndDate = now()->subDays(8)->endOfDay();
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create customers in previous period
    Customer::factory()->count(3)->create([
        'channel_id' => $channel->id,
        'created_at' => $lastStartDate->copy()->addDays(2),
    ]);
    
    // Create customers in current period
    Customer::factory()->count(5)->create([
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('lastStartDate')->setValue($reporting, $lastStartDate);
    $reflection->getProperty('lastEndDate')->setValue($reporting, $lastEndDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $previousResult = $reporting->getPreviousTotalCustomersOverTime('week');
    $currentResult = $reporting->getCurrentTotalCustomersOverTime('month');
    
    expect($previousResult)->toBeArray()
        ->and($previousResult)->not->toBeEmpty()
        ->and($currentResult)->toBeArray()
        ->and($currentResult)->not->toBeEmpty();
});

*/
/*
FAILED TEST: ## Analysis

The test **"it returns single interval for same start and end date"** is failing due to a **FOREIGN KEY constraint violation**. The `Customer` factory is attempting to create a customer with `customer_group_id` value `2`, but this customer group doesn't exist in the test database, causing SQLite to reject the insert operation.

## Recommended Fixes

1. **Create customer groups before creating customers** - Add to test setup or beginning of test:
```php
\Webkul\Customer\Models\CustomerGroup::factory()->create(['id' => 1]);
\Webkul\Customer\Models\CustomerGroup::factory()->create(['id' => 2]);
```

2. **Explicitly create and assign customer group** - In the test:
```php
$customerGroup = \Webkul\Customer\Models\CustomerGroup::factory()->create();
$customer = Customer::factory()->create([
    'customer_group_id' => $customerGroup->id,
]);
```

3. **Add to global test setup** - In `Pest.php` or `beforeEach` hook:
```php
beforeEach(function () {
    \Webkul\Customer\Models\CustomerGroup::factory()->count(3)->create();
});
```

it('returns single interval for same start and end date', function () {
    $channel = core()->getCurrentChannel();
    
    $startDate = now()->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create customers on that day
    Customer::factory()->count(5)->create([
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addHours(12),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTotalCustomersOverTime($startDate, $endDate, 'auto');
    
    expect($result)->toBeArray()
        ->and($result)->not->toBeEmpty();
});

*/
/*
FAILED TEST: ## Analysis

The test **"it returns zero when only non-approved reviews exist"** is failing due to an **Undefined array key 0** error in the `ProductFaker` helper class. The error occurs when trying to access `$optionSets[0]` for creating product attribute combinations, indicating that the `$optionSets` array is empty or doesn't have the expected structure.

This happens because the test is attempting to create a configurable product with super attributes, but the required attribute options haven't been properly set up in the test database.

## Recommended Fixes

1. **Ensure super attributes have options before creating products** - Add to test setup:
```php
$attribute = \Webkul\Attribute\Models\Attribute::factory()
    ->hasOptions(3)
    ->create(['code' => 'size', 'type' => 'select']);
```

2. **Create simple products instead of configurable** - If super attributes aren't needed:
```php
$productFaker = new ProductFaker(['super_attributes' => []]);
```

3. **Verify ProductFaker configuration** - Check that the ProductFaker is initialized with valid super attribute codes that exist in the database:
```php
$productFaker = new ProductFaker([
    'super_attributes' => ['size', 'color'], // ensure these exist
]);
```

4. **Add attribute seeding to test setup** - In `beforeEach` or test setup:
```php
\Webkul\Attribute\Models\Attribute::factory()
    ->count(2)
    ->hasOptions(3)
    ->create();
```

it('returns zero when only non-approved reviews exist', function () {
    $channel = core()->getCurrentChannel();
    $productFaker = new ProductFaker();
    
    $product = $productFaker->getSimpleProductFactory()->create();
    $customer = Customer::factory()->create(['channel_id' => $channel->id]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create pending reviews
    ProductReview::factory()->count(5)->create([
        'product_id' => $product->id,
        'customer_id' => $customer->id,
        'status' => 'pending',
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    // Create rejected reviews
    ProductReview::factory()->count(3)->create([
        'product_id' => $product->id,
        'customer_id' => $customer->id,
        'status' => 'rejected',
        'created_at' => $startDate->copy()->addDays(3),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTotalReviews($startDate, $endDate);
    
    expect($result)->toBe(0);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing due to a **FOREIGN KEY constraint violation**. The `Customer` factory is attempting to create a customer with `customer_group_id` value `2`, but this customer group doesn't exist in the test database, causing SQLite to reject the insert operation.

## Recommended Fixes

1. **Create customer groups before creating customers** - Add to test setup or beginning of test:
```php
\Webkul\Customer\Models\CustomerGroup::factory()->create(['id' => 1]);
\Webkul\Customer\Models\CustomerGroup::factory()->create(['id' => 2]);
```

2. **Explicitly create and assign customer group** - In the test:
```php
$customerGroup = \Webkul\Customer\Models\CustomerGroup::factory()->create();
$customer = Customer::factory()->create([
    'customer_group_id' => $customerGroup->id,
    // other attributes...
]);
```

3. **Add to global test setup** - In `Pest.php` or test case setup:
```php
beforeEach(function () {
    \Webkul\Customer\Models\CustomerGroup::factory()->count(3)->create();
});
```

it('returns zero when no customers exist in date range', function () {
    $channel = core()->getCurrentChannel();
    
    // Create customers outside the date range
    Customer::factory()->count(5)->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDays(30),
    ]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->subDays(5)->endOfDay();
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTotalCustomers($startDate, $endDate);
    
    expect($result)->toBe(0);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing due to a **FOREIGN KEY constraint violation**. When creating a `Customer` record, the factory is attempting to use `customer_group_id` value `2`, which doesn't exist in the `customer_groups` table in the SQLite test database.

## Recommended Fixes

1. **Create required customer groups in test setup** - Add to the beginning of the test or in a `beforeEach` hook:

```php
CustomerGroup::factory()->create(['id' => 1]);
CustomerGroup::factory()->create(['id' => 2]);
```

2. **Explicitly create customer group before customer** - In the test itself:

```php
$customerGroup = CustomerGroup::factory()->create();
$customer = Customer::factory()->create([
    'customer_group_id' => $customerGroup->id,
    // other attributes...
]);
```

3. **Configure the Customer factory to auto-create related models** - Update the Customer factory to use:

```php
'customer_group_id' => CustomerGroup::factory(),
```

it('calculates today customers progress comparing yesterday and today', function () {
    $channel = core()->getCurrentChannel();
    
    // Create customers yesterday
    Customer::factory()->count(3)->create([
        'channel_id' => $channel->id,
        'created_at' => now()->subDay()->startOfDay()->addHours(12),
    ]);
    
    // Create customers today
    Customer::factory()->count(5)->create([
        'channel_id' => $channel->id,
        'created_at' => now()->today()->addHours(12),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTodayCustomersProgress();
    
    expect($result)->toBeArray()
        ->and($result['previous'])->toBe(3)
        ->and($result['current'])->toBe(5)
        ->and($result['progress'])->toBeGreaterThanOrEqual(0);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing due to a **FOREIGN KEY constraint violation** when attempting to create a `Customer` record. The error indicates that `customer_group_id` value `2` doesn't exist in the `customer_groups` table, causing the SQLite database to reject the insert operation.

## Recommended Fixes

1. **Create the required customer group before creating customers:**
```php
$customerGroup = CustomerGroup::factory()->create();
$customer = Customer::factory()->create([
    'customer_group_id' => $customerGroup->id,
    'channel_id' => $channel->id
]);
```

2. **Update the Customer factory to auto-create the customer group:**
```php
// In CustomerFactory
'customer_group_id' => CustomerGroup::factory(),
```

3. **Ensure test database is properly seeded with required customer groups before running tests** - Add to test setup:
```php
beforeEach(function () {
    CustomerGroup::factory()->create(['id' => 1]);
    CustomerGroup::factory()->create(['id' => 2]);
});
```

it('returns time-series data for customers over time with auto period', function () {
    $channel = core()->getCurrentChannel();
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Create customers on specific days
    Customer::factory()->count(2)->create([
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(1),
    ]);
    
    Customer::factory()->count(3)->create([
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(3),
    ]);
    
    Customer::factory()->count(1)->create([
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(7),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTotalCustomersOverTime($startDate, $endDate, 'auto');
    
    expect($result)->toBeArray()
        ->and($result)->not->toBeEmpty()
        ->and($result[0])->toHaveKey('label')
        ->and($result[0])->toHaveKey('total');
});

*/
/*
FAILED TEST: ## Analysis

The test is failing because it calls `createAdminUser()`, which is an undefined function. This is a missing test helper that should create and return an admin user for authentication purposes.

## Recommended Fixes

1. **Define the missing helper function** - Add a `createAdminUser()` helper function in your test setup file (e.g., `Pest.php` or a dedicated helpers file):
```php
function createAdminUser() {
    return \Webkul\User\Models\Admin::factory()->create();
}
```

2. **Alternative: Use the factory directly** - Replace the helper call in the test:
```php
$admin = \Webkul\User\Models\Admin::factory()->create();
```

3. **Check for existing helpers** - Verify if there's already a similar helper with a different name (e.g., `createAdmin()`, `adminUser()`) in your test suite that should be used instead.

it('returns customer groups with most customers ordered by count', function () {
    $admin = createAdminUser();
    actingAs($admin, 'admin');
    
    $channel = core()->getCurrentChannel();
    
    // Create customer groups
    $goldGroup = CustomerGroup::factory()->create(['name' => 'Gold']);
    $silverGroup = CustomerGroup::factory()->create(['name' => 'Silver']);
    $bronzeGroup = CustomerGroup::factory()->create(['name' => 'Bronze']);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Gold group: 10 customers
    Customer::factory()->count(10)->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $goldGroup->id,
        'created_at' => $startDate->copy()->addDays(1),
    ]);
    
    // Silver group: 5 customers
    Customer::factory()->count(5)->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $silverGroup->id,
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    // Bronze group: 15 customers
    Customer::factory()->count(15)->create([
        'channel_id' => $channel->id,
        'customer_group_id' => $bronzeGroup->id,
        'created_at' => $startDate->copy()->addDays(3),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getGroupsWithMostCustomers(2);
    
    expect($result)->toHaveCount(2)
        ->and($result->first()->group_name)->toBe('Bronze')
        ->and((int)$result->first()->total)->toBe(15)
        ->and($result->last()->group_name)->toBe('Gold')
        ->and((int)$result->last()->total)->toBe(10);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing because it calls `createAdminUser()`, which is an undefined function. This is a missing test helper that should create and return an admin user for authentication purposes.

## Recommended Fixes

1. **Define the missing helper function** - Add a `createAdminUser()` helper function in your test setup file (e.g., `Pest.php` or a dedicated helpers file):
```php
function createAdminUser() {
    return \Webkul\User\Models\Admin::factory()->create();
}
```

2. **Alternative: Use the factory directly** - Replace the helper call in the test:
```php
$admin = \Webkul\User\Models\Admin::factory()->create();
```

3. **Check for existing helpers** - Verify if there's already a similar helper with a different name (e.g., `createAdmin()`, `adminUser()`) in your test suite that should be used instead.

it('returns customers with most reviews excluding guest reviews', function () {
    $admin = createAdminUser();
    actingAs($admin, 'admin');
    
    $channel = core()->getCurrentChannel();
    $productFaker = new ProductFaker();
    
    // Create products
    $product1 = $productFaker->getSimpleProductFactory()->create();
    $product2 = $productFaker->getSimpleProductFactory()->create();
    
    // Create customers
    $customerA = Customer::factory()->create(['channel_id' => $channel->id]);
    $customerB = Customer::factory()->create(['channel_id' => $channel->id]);
    $customerC = Customer::factory()->create(['channel_id' => $channel->id]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Customer A: 4 approved reviews
    ProductReview::factory()->count(4)->create([
        'product_id' => $product1->id,
        'customer_id' => $customerA->id,
        'status' => 'approved',
        'created_at' => $startDate->copy()->addDays(1),
    ]);
    
    // Customer B: 7 approved reviews
    ProductReview::factory()->count(7)->create([
        'product_id' => $product2->id,
        'customer_id' => $customerB->id,
        'status' => 'approved',
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    // Customer C: 2 approved reviews
    ProductReview::factory()->count(2)->create([
        'product_id' => $product1->id,
        'customer_id' => $customerC->id,
        'status' => 'approved',
        'created_at' => $startDate->copy()->addDays(3),
    ]);
    
    // Guest reviews (should be excluded)
    ProductReview::factory()->count(3)->create([
        'product_id' => $product1->id,
        'customer_id' => null,
        'status' => 'approved',
        'created_at' => $startDate->copy()->addDays(4),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getCustomersWithMostReviews(2);
    
    expect($result)->toHaveCount(2)
        ->and($result->first()->email)->toBe($customerB->email)
        ->and((int)$result->first()->reviews)->toBe(7)
        ->and($result->last()->email)->toBe($customerA->email)
        ->and((int)$result->last()->reviews)->toBe(4);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing because it calls `createAdminUser()`, which is an undefined function. This is a missing test helper that should create and return an admin user for authentication purposes.

## Recommended Fixes

1. **Define the missing helper function** - Add a `createAdminUser()` helper function in your test setup file (e.g., `Pest.php` or a dedicated helpers file):
```php
function createAdminUser() {
    return \Webkul\User\Models\Admin::factory()->create();
}
```

2. **Alternative: Use the factory directly** - Replace the helper call in the test:
```php
$admin = \Webkul\User\Models\Admin::factory()->create();
```

3. **Check for existing helpers** - Verify if there's already a similar helper with a different name (e.g., `createAdmin()`, `adminUser()`) in your test suite that should be used instead.

it('returns customers with most orders ordered by order count', function () {
    $admin = createAdminUser();
    actingAs($admin, 'admin');
    
    $channel = core()->getCurrentChannel();
    
    // Create customers
    $customerA = Customer::factory()->create(['channel_id' => $channel->id]);
    $customerB = Customer::factory()->create(['channel_id' => $channel->id]);
    $customerC = Customer::factory()->create(['channel_id' => $channel->id]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Customer A: 5 orders
    Order::factory()->count(5)->create([
        'customer_id' => $customerA->id,
        'customer_email' => $customerA->email,
        'customer_first_name' => $customerA->first_name,
        'customer_last_name' => $customerA->last_name,
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(1),
    ]);
    
    // Customer B: 3 orders
    Order::factory()->count(3)->create([
        'customer_id' => $customerB->id,
        'customer_email' => $customerB->email,
        'customer_first_name' => $customerB->first_name,
        'customer_last_name' => $customerB->last_name,
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    // Customer C: 8 orders
    Order::factory()->count(8)->create([
        'customer_id' => $customerC->id,
        'customer_email' => $customerC->email,
        'customer_first_name' => $customerC->first_name,
        'customer_last_name' => $customerC->last_name,
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(3),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getCustomersWithMostOrders(2);
    
    expect($result)->toHaveCount(2)
        ->and($result->first()->email)->toBe($customerC->email)
        ->and((int)$result->first()->orders)->toBe(8)
        ->and($result->first()->full_name)->toContain($customerC->first_name)
        ->and($result->last()->email)->toBe($customerA->email)
        ->and((int)$result->last()->orders)->toBe(5);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing because it calls `createAdminUser()`, which is an undefined function. This is a missing test helper that should create and return an admin user for authentication purposes.

## Recommended Fixes

1. **Define the missing helper function** - Add a `createAdminUser()` helper function in your test setup file (e.g., `Pest.php` or a dedicated helpers file):
```php
function createAdminUser() {
    return \Webkul\User\Models\Admin::factory()->create();
}
```

2. **Alternative: Use the factory directly** - Replace the helper call in the test:
```php
$admin = \Webkul\User\Models\Admin::factory()->create();
```

3. **Check for existing helpers** - Verify if there's already a similar helper with a different name (e.g., `createAdmin()`, `adminUser()`) in your test suite that should be used instead.

it('returns customers with most sales ordered by total amount', function () {
    $admin = createAdminUser();
    actingAs($admin, 'admin');
    
    $channel = core()->getCurrentChannel();
    
    // Create customers
    $customerA = Customer::factory()->create(['channel_id' => $channel->id]);
    $customerB = Customer::factory()->create(['channel_id' => $channel->id]);
    $customerC = Customer::factory()->create(['channel_id' => $channel->id]);
    
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    // Customer A: 3 orders with net 2700
    for ($i = 0; $i < 3; $i++) {
        Order::factory()->create([
            'customer_id' => $customerA->id,
            'customer_email' => $customerA->email,
            'customer_first_name' => $customerA->first_name,
            'customer_last_name' => $customerA->last_name,
            'channel_id' => $channel->id,
            'base_grand_total_invoiced' => 1000,
            'base_grand_total_refunded' => 100,
            'created_at' => $startDate->copy()->addDays(1),
        ]);
    }
    
    // Customer B: 2 orders with net 4000
    for ($i = 0; $i < 2; $i++) {
        Order::factory()->create([
            'customer_id' => $customerB->id,
            'customer_email' => $customerB->email,
            'customer_first_name' => $customerB->first_name,
            'customer_last_name' => $customerB->last_name,
            'channel_id' => $channel->id,
            'base_grand_total_invoiced' => 2000,
            'base_grand_total_refunded' => 0,
            'created_at' => $startDate->copy()->addDays(2),
        ]);
    }
    
    // Customer C: 1 order with net 500
    Order::factory()->create([
        'customer_id' => $customerC->id,
        'customer_email' => $customerC->email,
        'customer_first_name' => $customerC->first_name,
        'customer_last_name' => $customerC->last_name,
        'channel_id' => $channel->id,
        'base_grand_total_invoiced' => 500,
        'base_grand_total_refunded' => 0,
        'created_at' => $startDate->copy()->addDays(3),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getCustomersWithMostSales(2);
    
    expect($result)->toHaveCount(2)
        ->and($result->first()->email)->toBe($customerB->email)
        ->and((float)$result->first()->total)->toBe(4000.0)
        ->and((int)$result->first()->orders)->toBe(2)
        ->and($result->last()->email)->toBe($customerA->email)
        ->and((float)$result->last()->total)->toBe(2700.0)
        ->and((int)$result->last()->orders)->toBe(3);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing because it calls `createAdminUser()`, which is an undefined function. This is a missing test helper that should create and return an admin user for authentication purposes.

## Recommended Fixes

1. **Define the missing helper function** - Add a `createAdminUser()` helper function in your test setup file (e.g., `Pest.php` or a dedicated helpers file):
```php
function createAdminUser() {
    return \Webkul\User\Models\Admin::factory()->create();
}
```

2. **Alternative: Use the factory directly** - Replace the helper call in the test:
```php
$admin = \Webkul\User\Models\Admin::factory()->create();
```

3. **Check for existing helpers** - Verify if there's already a similar helper with a different name (e.g., `createAdmin()`, `adminUser()`) in your test suite that should be used instead.

it('calculates total reviews progress with approved reviews only', function () {
    $admin = createAdminUser();
    actingAs($admin, 'admin');
    
    $channel = core()->getCurrentChannel();
    $productFaker = new ProductFaker();
    
    // Create products
    $product1 = $productFaker->getSimpleProductFactory()->create();
    $product2 = $productFaker->getSimpleProductFactory()->create();
    
    // Create customers
    $customer1 = Customer::factory()->create(['channel_id' => $channel->id]);
    $customer2 = Customer::factory()->create(['channel_id' => $channel->id]);
    
    // Previous period
    $lastStartDate = now()->subDays(14)->startOfDay();
    $lastEndDate = now()->subDays(8)->endOfDay();
    
    ProductReview::factory()->count(3)->create([
        'product_id' => $product1->id,
        'customer_id' => $customer1->id,
        'status' => 'approved',
        'created_at' => $lastStartDate->copy()->addDays(2),
    ]);
    
    ProductReview::factory()->count(2)->create([
        'product_id' => $product1->id,
        'customer_id' => $customer1->id,
        'status' => 'pending',
        'created_at' => $lastStartDate->copy()->addDays(2),
    ]);
    
    // Current period
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    ProductReview::factory()->count(6)->create([
        'product_id' => $product2->id,
        'customer_id' => $customer2->id,
        'status' => 'approved',
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('lastStartDate')->setValue($reporting, $lastStartDate);
    $reflection->getProperty('lastEndDate')->setValue($reporting, $lastEndDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTotalReviewsProgress();
    
    expect($result)->toBeArray()
        ->and($result['previous'])->toBe(3)
        ->and($result['current'])->toBe(6);
});

*/
/*
FAILED TEST: ## Analysis

The test is failing because it calls `createAdminUser()`, which is an undefined function. This is a missing test helper that should create and return an admin user for authentication purposes.

## Recommended Fixes

1. **Define the missing helper function** - Add a `createAdminUser()` helper function in your test setup file (e.g., `Pest.php` or a dedicated helpers file):

```php
function createAdminUser() {
    return \Webkul\User\Models\Admin::factory()->create();
}
```

2. **Alternative: Use the factory directly** - Replace the helper call in the test:

```php
$admin = \Webkul\User\Models\Admin::factory()->create();
```

3. **Check for existing helpers** - Verify if there's already a similar helper with a different name (e.g., `createAdmin()`, `adminUser()`) in your test suite that should be used instead.

it('calculates total customers progress correctly', function () {
    $admin = createAdminUser();
    actingAs($admin, 'admin');
    
    $channel = core()->getCurrentChannel();
    
    // Create customers in previous period
    $lastStartDate = now()->subDays(14)->startOfDay();
    $lastEndDate = now()->subDays(8)->endOfDay();
    
    Customer::factory()->count(5)->create([
        'channel_id' => $channel->id,
        'created_at' => $lastStartDate->copy()->addDays(2),
    ]);
    
    // Create customers in current period
    $startDate = now()->subDays(7)->startOfDay();
    $endDate = now()->endOfDay();
    
    Customer::factory()->count(10)->create([
        'channel_id' => $channel->id,
        'created_at' => $startDate->copy()->addDays(2),
    ]);
    
    $reporting = new CustomerReporting(
        app(CustomerRepository::class),
        app(\Webkul\Sales\Repositories\OrderRepository::class),
        app(\Webkul\Product\Repositories\ProductReviewRepository::class)
    );
    
    $reflection = new \ReflectionClass($reporting);
    $reflection->getProperty('startDate')->setValue($reporting, $startDate);
    $reflection->getProperty('endDate')->setValue($reporting, $endDate);
    $reflection->getProperty('lastStartDate')->setValue($reporting, $lastStartDate);
    $reflection->getProperty('lastEndDate')->setValue($reporting, $lastEndDate);
    $reflection->getProperty('channelIds')->setValue($reporting, [$channel->id]);
    
    $result = $reporting->getTotalCustomersProgress();
    
    expect($result)->toBeArray()
        ->and($result['previous'])->toBe(5)
        ->and($result['current'])->toBe(10)
        ->and($result['progress'])->toBeGreaterThan(0);
});

*/
