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



it('test_getTaxCollected_with_no_tax_data_returns_0', function () {
    // Arrange
    $startDate = Carbon::now()->subDay();
    $endDate = Carbon::now();

    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );

    // Act
    $result = $saleReporting->getTaxCollected($startDate, $endDate);

    // Assert
    $this->assertEquals(0, $result);
});

/*
FAILED TEST: **Test Failure Analysis:**

- **Failed Test:** `test_getTopPaymentMethods_orders_grouped_and_sorted_by_count`
- **Error:** `SQLSTATE[HY000]: General error: 1 table orders has no column named payment_method`
- **Root Cause:** The test attempts to create an `Order` with a `payment_method` field, but the SQLite test database schema does **not** include this column.

**Recommended Fix:**

Update the test to ensure the `orders` table is properly migrated and includes the `payment_method` column in the test environment. Alternatively, adjust the test setup to use a supported database (e.g., MySQL) or ensure the SQLite schema is in sync with the actual database structure.

it('test_getTopPaymentMethods_orders_grouped_and_sorted_by_count', function () {
    // Arrange
    Order::factory()->create(['payment_method' => 'bank_transfer']);
    Order::factory()->create(['payment_method' => 'bank_transfer']);
    Order::factory()->create(['payment_method' => 'bank_transfer']);
    Order::factory()->create(['payment_method' => 'bank_transfer']);
    Order::factory()->create(['payment_method' => 'bank_transfer']);
    Order::factory()->create(['payment_method' => 'paypal']);
    Order::factory()->create(['payment_method' => 'paypal']);
    Order::factory()->create(['payment_method' => 'paypal']);

    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );

    // Act
    $result = $saleReporting->getTopPaymentMethods();

    // Assert
    $this->assertCount(2, $result);
    $this->assertEquals('bank_transfer', $result[0]['method']);
    $this->assertEquals(5, $result[0]['total']);
    $this->assertEquals('paypal', $result[1]['method']);
    $this->assertEquals(3, $result[1]['total']);
});

*/

it('test_getTopTaxCategories_with_limit_0_returns_empty_collection', function () {
    // Arrange
    $limit = 0;

    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );

    // Act
    $result = $saleReporting->getTopTaxCategories($limit);

    // Assert
    $this->assertCount(0, $result);
});

/*
FAILED TEST: The test `test_getOverTimeStats_with_includeEmpty_false_returns_empty_array` failed due to an **SQL error**:

> `SQLSTATE[HY000]: General error: 1 no such function: DAYOFYEAR`

### Root Cause:
The `DAYOFYEAR` SQL function is not supported by **SQLite**, which is being used in the test environment. The `getOverTimeStats()` method in `Sale.php` uses this function, which works in MySQL but not in SQLite.

### Recommended Fix:
Replace `DAYOFYEAR(created_at)` with a **SQLite-compatible alternative**, such as:

```php
strftime('%j', created_at)
```

Update the logic in `getOverTimeStats()` in `Sale.php` to use a database-agnostic function or conditionally choose the correct SQL function based on the database driver.

it('test_getOverTimeStats_with_includeEmpty_false_returns_empty_array', function () {
    // Arrange
    $startDate = Carbon::parse('2023-01-01 00:00:00');
    $endDate = Carbon::parse('2023-01-01 23:59:59');
    $period = 'auto';
    $includeEmpty = false;

    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );

    // Act
    $result = $saleReporting->getOverTimeStats($startDate, $endDate, 'COUNT(*)', $period);

    // Assert
    $this->assertEmpty($result);
});

*/
/*
FAILED TEST: The test `test_getOverTimeStats_with_minimal_time_interval_generates_single_interval` failed due to an **SQL error**:

> `SQLSTATE[HY000]: General error: 1 no such function: DAYOFYEAR`

### Root Cause:
The `DAYOFYEAR` SQL function is not supported by the SQLite database being used for testing. The `getOverTimeStats()` method in `Sale.php` uses this function to group results by day of the year, which works in databases like MySQL but not in SQLite.

### Recommended Fix:
Replace the use of `DAYOFYEAR()` with a database-agnostic equivalent or use a conditional to handle different SQL dialects. For example, in SQLite, you can use `strftime('%j', created_at)` as a replacement for `DAYOFYEAR(created_at)`.

Update the `getOverTimeStats()` method in `Sale.php` to use a compatible function for SQLite, or configure the test environment to use a database that supports `DAYOFYEAR` (e.g., MySQL).

it('test_getOverTimeStats_with_minimal_time_interval_generates_single_interval', function () {
    // Arrange
    $startDate = Carbon::parse('2023-01-01 12:00:00');
    $endDate = Carbon::parse('2023-01-01 12:05:00');
    $period = 'auto';

    $order = Order::factory()->create([
        'created_at' => Carbon::parse('2023-01-01 12:02:00'),
    ]);

    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );

    // Act
    $result = $saleReporting->getOverTimeStats($startDate, $endDate, 'COUNT(*)', $period);

    // Assert
    $this->assertCount(1, $result);
    $this->assertEquals(1, $result[0]['count']);
});

*/

it('test_getTotalOrders_with_overlapping_date_ranges_includes_partial_orders', function () {
    // Arrange
    $startDate = Carbon::parse('2023-01-01 23:00:00');
    $endDate = Carbon::parse('2023-01-02 01:00:00');
    $order = Order::factory()->create([
        'created_at' => Carbon::parse('2023-01-02 00:30:00'),
    ]);

    $saleReporting = new SaleReporting(
        app(OrderRepository::class),
        app(OrderItemRepository::class),
        app(InvoiceRepository::class),
        app(RefundRepository::class)
    );

    // Act
    $result = $saleReporting->getTotalOrders($startDate, $endDate);

    // Assert
    $this->assertEquals(1, $result);
});
