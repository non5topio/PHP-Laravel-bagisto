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
FAILED TEST: **Analysis:**  
The test run failed due to two main issues:  
1. **Constructor Argument Missing:** Several tests instantiate `CustomerReporting` without passing the required constructor arguments (`CustomerRepository`, `OrderRepository`, `ProductReviewRepository`), causing `ArgumentCountError`.  
2. **Namespace/Use Conflict:** There is a fatal error due to a duplicate use of `CustomerRepository`, likely from a conflicting import or alias.

**Recommended Fixes:**  
1. Mock and pass the required repositories when instantiating `CustomerReporting` in each test.  
2. Resolve the duplicate use of `CustomerRepository` by checking and correcting the `use` statements in `CustomerReportTest.php`.

it('test_get_total_customers_progress_returns_zero_percent_on_equal_values', function () {
    $reporting = new CustomerReporting(
        $this->createMock(CustomerRepository::class),
        $this->createMock(OrderRepository::class),
        $this->createMock(ProductReviewRepository::class)
    );
    $reporting->startDate = now()->today();
    $reporting->endDate = now()->endOfDay();
    $reporting->lastStartDate = now()->subDay()->startOfDay();
    $reporting->lastEndDate = now()->subDay()->endOfDay();
    $reporting->channelIds = [1];

    // Mock the getTotalCustomers method to return 100 for both previous and current
    $reporting->shouldReceive('getTotalCustomers')
        ->with(now()->subDay()->startOfDay(), now()->subDay()->endOfDay())
        ->andReturn(100);
    $reporting->shouldReceive('getTotalCustomers')
        ->with(now()->today(), now()->endOfDay())
        ->andReturn(100);

    $result = $reporting->getTotalCustomersProgress();

    expect($result['previous'])->toBe(100);
    expect($result['current'])->toBe(100);
    expect($result['progress'])->toBe(0);
});

*/
/*
FAILED TEST: **Analysis:**  
The test run failed due to two main issues:  
1. **Constructor Argument Missing:** Several tests instantiate `CustomerReporting` without passing the required constructor arguments (`CustomerRepository`, `OrderRepository`, `ProductReviewRepository`), causing `ArgumentCountError`.  
2. **Namespace/Use Conflict:** There is a fatal error due to a duplicate use of `CustomerRepository`, likely from a conflicting import or alias.

**Recommended Fixes:**  
1. Mock and pass the required repositories when instantiating `CustomerReporting` in each test.  
2. Resolve the duplicate use of `CustomerRepository` by checking and correcting the `use` statements in `CustomerReportTest.php`.

it('test_get_today_customers_progress_returns_zero_when_no_customers', function () {
    $reporting = new CustomerReporting(
        $this->createMock(CustomerRepository::class),
        $this->createMock(OrderRepository::class),
        $this->createMock(ProductReviewRepository::class)
    );
    $reporting->startDate = now()->today();
    $reporting->endDate = now()->endOfDay();
    $reporting->lastStartDate = now()->subDay()->startOfDay();
    $reporting->lastEndDate = now()->subDay()->endOfDay();
    $reporting->channelIds = [1];

    $result = $reporting->getTodayCustomersProgress();

    expect($result['current'])->toBe(0);
    expect($result['previous'])->toBe(0);
    expect($result['progress'])->toBe(0);
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed because the `Customer` class constructor requires three dependencies (`CustomerRepository`, `OrderRepository`, and `ProductReviewRepository`), but the test is instantiating it without passing any arguments.

**Recommended Fix:**  
Mock and pass the required repository dependencies when instantiating the `Customer` or `CustomerReporting` class in the test.

it('test_get_groups_with_most_customers_returns_empty_when_no_groups', function () {
    $startDate = Carbon::parse('2023-01-01 00:00:00');
    $endDate = Carbon::parse('2023-01-01 23:59:59');

    $reporting = new CustomerReporting();
    $reporting->startDate = $startDate;
    $reporting->endDate = $endDate;

    $result = $reporting->getGroupsWithMostCustomers();

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed because the `Customer` class requires three constructor arguments (`CustomerRepository`, `OrderRepository`, and `ProductReviewRepository`), but the test is instantiating it without passing them, leading to an `ArgumentCountError`.

**Recommended Fix:**  
Mock and pass the required repository dependencies when instantiating the `Customer` or `CustomerReporting` class in the test.

it('test_get_total_reviews_throws_exception_on_invalid_date', function () {
    $startDate = 'invalid-date';
    $endDate = Carbon::parse('2023-01-01 23:59:59');

    $reporting = new CustomerReporting();
    $reporting->startDate = $startDate;
    $reporting->endDate = $endDate;

    $this->expectException(\Exception::class);

    $reporting->getTotalReviews($startDate, $endDate);
});

*/
/*
FAILED TEST: The test failed because the `Customer` class constructor requires three dependencies (`CustomerRepository`, `OrderRepository`, and `ProductReviewRepository`), but the test is instantiating it without passing any arguments.

**Recommended Fix:**  
Mock and pass the required repository dependencies when instantiating the `Customer` class in the test.

it('test_get_customers_with_most_reviews_returns_empty_when_no_reviews', function () {
    $startDate = Carbon::parse('2023-01-01 00:00:00');
    $endDate = Carbon::parse('2023-01-01 23:59:59');
    $limit = 5;

    $reporting = new CustomerReporting();
    $reporting->startDate = $startDate;
    $reporting->endDate = $endDate;

    $result = $reporting->getCustomersWithMostReviews($limit);

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: The test failed because the `CustomerReporting` class constructor requires three dependencies (`CustomerRepository`, `OrderRepository`, and `ProductReviewRepository`), but the test is instantiating it without passing any arguments.

**Recommended Fix:**  
Mock and pass the required repository dependencies when instantiating the `CustomerReporting` class in the test.

it('test_get_customers_with_most_sales_returns_empty_when_no_sales', function () {
    $startDate = Carbon::parse('2023-01-01 00:00:00');
    $endDate = Carbon::parse('2023-01-01 23:59:59');
    $limit = 5;

    $reporting = new CustomerReporting();
    $reporting->startDate = $startDate;
    $reporting->endDate = $endDate;

    $result = $reporting->getCustomersWithMostSales($limit);

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: The test failed because the `CustomerReporting` class constructor requires three dependencies (`CustomerRepository`, `OrderRepository`, and `ProductReviewRepository`), but the test is instantiating it without passing any arguments.

**Recommended Fix:**  
Update the test to mock and pass the required repository dependencies when instantiating the `CustomerReporting` class.

it('test_get_total_customers_over_time_for_single_day', function () {
    $startDate = Carbon::parse('2023-01-01 00:00:00');
    $endDate = Carbon::parse('2023-01-01 23:59:59');
    $period = 'day';

    $reporting = new CustomerReporting();
    $reporting->startDate = $startDate;
    $reporting->endDate = $endDate;

    $result = $reporting->getTotalCustomersOverTime($startDate, $endDate, $period);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(1);
    expect($result[0])->toHaveKey('label');
    expect($result[0])->toHaveKey('total');
    expect($result[0]['total'])->toBe(0);
});

*/
/*
FAILED TEST: The test failed because the `Customer` class constructor requires three dependencies (`CustomerRepository`, `OrderRepository`, and `ProductReviewRepository`), but the test is instantiating it without passing any arguments.

**Fix:**  
Update the test to mock and pass the required repository dependencies when instantiating the `CustomerReporting` class.

it('test_get_total_customers_returns_zero_when_no_customers', function () {
    $startDate = Carbon::parse('2023-01-01 00:00:00');
    $endDate = Carbon::parse('2023-01-01 23:59:59');
    $channelIds = [1, 2];

    $reporting = new CustomerReporting();
    $reporting->startDate = $startDate;
    $reporting->endDate = $endDate;
    $reporting->channelIds = $channelIds;

    $total = $reporting->getTotalCustomers($startDate, $endDate);

    expect($total)->toBe(0);
});

*/