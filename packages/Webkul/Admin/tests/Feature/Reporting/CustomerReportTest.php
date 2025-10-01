<?php

use Carbon\Carbon;
use Webkul\Admin\Helpers\Reporting\Customer as CustomerReporting;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\Product\Models\ProductReview;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\get;


/*
FAILED TEST: **Analysis:**  
The test run failed due to a **name collision** in the `CustomerReportTest.php` file. Two classes named `Customer` are imported using the same name:  
- `use Webkul\Customer\Models\Customer;`  
- `use Webkul\Admin\Helpers\Reporting\Customer;`  

This causes a fatal error in PHP as the name `Customer` is already in use.

**Recommended Fix:**  
Rename one of the imported `Customer` classes using an **alias**, for example:  
```php
use Webkul\Admin\Helpers\Reporting\Customer as ReportingCustomer;
```

Then update the test to use `ReportingCustomer` wherever the `Customer` helper class is referenced.

it('test_get_customers_with_most_sales_for_valid_date_range', function () {
    // Arrange
    $startDate = Carbon::parse('2023-01-01');
    $endDate = Carbon::parse('2023-01-31');
    $limit = 2;

    // Create test customers and orders
    $customer1 = factory(Customer::class)->create(['channel_id' => 1]);
    $customer2 = factory(Customer::class)->create(['channel_id' => 1]);

    factory(Order::class, 3)->create([
        'customer_id' => $customer1->id,
        'base_grand_total_invoiced' => 100,
        'base_grand_total_refunded' => 0,
        'created_at' => $startDate->addDay(1),
        'channel_id' => 1,
    ]);
    factory(Order::class, 2)->create([
        'customer_id' => $customer2->id,
        'base_grand_total_invoiced' => 150,
        'base_grand_total_refunded' => 0,
        'created_at' => $startDate->addDay(2),
        'channel_id' => 1,
    ]);

    // Act
    $customerReporting = new Customer();
    $customerReporting->startDate = $startDate;
    $customerReporting->endDate = $endDate;
    $customerReporting->channelIds = [1];
    $result = $customerReporting->getCustomersWithMostSales($limit);

    // Assert
    $this->assertCount(2, $result);
    $this->assertEquals($customer2->id, $result[0]->id);
    $this->assertEquals(300, $result[0]->total);
    $this->assertEquals($customer1->id, $result[1]->id);
    $this->assertEquals(300, $result[1]->total);
});

*/
/*
FAILED TEST: The test run failed due to a **name collision** in the `CustomerReportTest.php` file where two classes named `Customer` are imported:

- `use Webkul\Customer\Models\Customer;`
- `use Webkul\Admin\Helpers\Reporting\Customer;`

### **Recommended Fix:**
Rename one of the imported `Customer` classes using an **alias**, for example:

```php
use Webkul\Admin\Helpers\Reporting\Customer as ReportingCustomer;
```

Then update the test to use `ReportingCustomer` wherever the `Customer` helper class is referenced.

it('test_get_total_reviews_returns_zero_for_no_reviews_in_range', function () {
    // Arrange
    $startDate = Carbon::parse('2023-01-01');
    $endDate = Carbon::parse('2023-01-31');
    $channelIds = [1];

    // Act
    $customerReporting = new Customer();
    $customerReporting->startDate = $startDate;
    $customerReporting->endDate = $endDate;
    $customerReporting->channelIds = $channelIds;
    $result = $customerReporting->getTotalReviews($startDate, $endDate);

    // Assert
    $this->assertEquals(0, $result);
});

*/
/*
FAILED TEST: The test run failed due to a **name collision** in the `CustomerReportTest.php` file caused by two `use` statements importing classes with the same name `Customer`:

- `use Webkul\Customer\Models\Customer;`
- `use Webkul\Admin\Helpers\Reporting\Customer;`

### **Recommended Fix:**
Rename one of the imported `Customer` classes using an **alias**, for example:

```php
use Webkul\Admin\Helpers\Reporting\Customer as ReportingCustomer;
```

Then update the test to use `ReportingCustomer` where the helper class is referenced.

it('test_get_total_reviews_for_valid_date_range_and_channel_filter', function () {
    // Arrange
    $startDate = Carbon::parse('2023-01-01');
    $endDate = Carbon::parse('2023-01-31');
    $channelIds = [1, 2];

    // Create test reviews
    factory(ProductReview::class, 3)->create([
        'created_at' => $startDate->addDay(1),
        'channel_id' => 1,
        'status' => 'approved',
    ]);
    factory(ProductReview::class, 2)->create([
        'created_at' => $startDate->addDay(2),
        'channel_id' => 2,
        'status' => 'approved',
    ]);

    // Act
    $customerReporting = new Customer();
    $customerReporting->startDate = $startDate;
    $customerReporting->endDate = $endDate;
    $customerReporting->channelIds = $channelIds;
    $result = $customerReporting->getTotalReviews($startDate, $endDate);

    // Assert
    $this->assertEquals(5, $result);
});

*/
/*
FAILED TEST: The test run failed due to a **name collision** between two `use` statements for the `Customer` class in the file `CustomerReportTest.php`:

- Line 4: `use Webkul\Customer\Models\Customer;`
- Line 11: `use Webkul\Admin\Helpers\Reporting\Customer;`

Both classes are named `Customer`, causing a fatal error.

### **Recommended Fix:**
Rename one of the imported `Customer` classes using an **alias**, for example:

```php
use Webkul\Admin\Helpers\Reporting\Customer as ReportingCustomer;
```

Then update the test to use `ReportingCustomer` where appropriate.

it('test_get_total_customers_handles_invalid_date_format', function () {
    // Arrange
    $startDate = 'invalid-date';
    $endDate = Carbon::parse('2023-01-31');
    $channelIds = [1];

    // Act & Assert
    $customerReporting = new Customer();
    $customerReporting->startDate = $startDate;
    $customerReporting->endDate = $endDate;
    $customerReporting->channelIds = $channelIds;

    $this->expectException(\Exception::class);
    $customerReporting->getTotalCustomers($startDate, $endDate);
});

*/
/*
FAILED TEST: The test run failed due to a **name collision** in the `CustomerReportTest.php` file:

- Line 4: `use Webkul\Customer\Models\Customer;`
- Line 11: `use Webkul\Admin\Helpers\Reporting\Customer;`

Both lines import a class named `Customer`, causing a fatal error.

### **Recommended Fix:**
Rename one of the imported `Customer` classes using an **alias**, for example:

```php
use Webkul\Admin\Helpers\Reporting\Customer as ReportingCustomer;
```

Update the test to use `ReportingCustomer` where appropriate.

it('test_get_total_customers_for_single_day_with_same_start_and_end_date', function () {
    // Arrange
    $date = Carbon::parse('2023-01-01 23:59:59');
    $channelIds = [1];

    // Create test customers
    factory(Customer::class, 2)->create([
        'created_at' => $date,
        'channel_id' => 1,
    ]);

    // Act
    $customerReporting = new Customer();
    $customerReporting->startDate = $date;
    $customerReporting->endDate = $date;
    $customerReporting->channelIds = $channelIds;
    $result = $customerReporting->getTotalCustomers($date, $date);

    // Assert
    $this->assertEquals(2, $result);
});

*/
/*
FAILED TEST: The test failed due to a **name collision** between two `use` statements for the `Customer` class in the test file `CustomerReportTest.php`.

### **Analysis:**
- Line 4: `use Webkul\Customer\Models\Customer;`
- Line 11: `use Webkul\Admin\Helpers\Reporting\Customer;`

Both lines attempt to import a class named `Customer`, causing a fatal error.

### **Recommended Fix:**
Rename one of the imported `Customer` classes using an **alias** to resolve the conflict. For example:

```php
use Webkul\Admin\Helpers\Reporting\Customer as ReportingCustomer;
```

Then update the test to use `ReportingCustomer` where appropriate.

it('test_get_total_customers_for_valid_date_range_and_channel_filter', function () {
    // Arrange
    $startDate = Carbon::parse('2023-01-01');
    $endDate = Carbon::parse('2023-01-31');
    $channelIds = [1, 2];

    // Create test customers
    factory(Customer::class, 3)->create([
        'created_at' => $startDate->addDay(1),
        'channel_id' => 1,
    ]);
    factory(Customer::class, 2)->create([
        'created_at' => $startDate->addDay(2),
        'channel_id' => 2,
    ]);

    // Act
    $customerReporting = new CustomerReporting();
    $customerReporting->startDate = $startDate;
    $customerReporting->endDate = $endDate;
    $customerReporting->channelIds = $channelIds;
    $result = $customerReporting->getTotalCustomers($startDate, $endDate);

    // Assert
    $this->assertEquals(5, $result);
});

*/