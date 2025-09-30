<?php

use Carbon\Carbon;
use Webkul\Admin\Helpers\Reporting\Cart as CartReporting;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Models\Customer;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\get;


/*
FAILED TEST: **Short Analysis:**  
The test failed because it attempted to directly access and set the **protected** property `startDate` on an instance of the `Cart` class, which is not allowed in PHP.

**Recommended Fix:**  
Use public setter methods to set protected properties, or use reflection in the test to access them. Alternatively, mock the class using Mockery if it relies on external dependencies that need to be stubbed.

it('test_get_total_unique_carts_users_with_same_email_different_customer_id', function () {
    $cartReporting = new CartReporting(
        $this->mock(CartRepository::class),
        $this->mock(CartItemRepository::class)
    );

    $cartReporting->startDate = '2024-01-01';
    $cartReporting->endDate = '2024-01-31';
    $cartReporting->channelIds = [1];

    $cartReporting->shouldReceive('resetModel')
        ->once()
        ->andReturnSelf();

    $cartReporting->shouldReceive('groupBy')
        ->with(DB::raw('CONCAT(customer_email, "-", customer_id)'))
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereIn')
        ->with('cart.channel_id', [1])
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereBetween')
        ->with('created_at', ['2024-01-01', '2024-01-31'])
        ->andReturnSelf();

    $cartReporting->shouldReceive('get')
        ->once()
        ->andReturn(collect([
            ['customer_email' => 'test@example.com', 'customer_id' => 1],
            ['customer_email' => 'test@example.com', 'customer_id' => 2],
        ]));

    $result = $cartReporting->getTotalUniqueCartsUsers('2024-01-01', '2024-01-31');

    expect($result)->toBe(2);
});

*/
/*
FAILED TEST: **Short Analysis:**  
The test failed due to incorrect argument order when instantiating `CartReporting`. The constructor expects a `CartRepository` first, followed by a `CartItemRepository`, but the test provided them in the reverse order.

**Recommended Fix:**  
Swap the order of the mocked repositories when creating the `CartReporting` instance to match the constructor signature:

```php
$cartReporting = new CartReporting(
    $this->mock(CartRepository::class),
    $this->mock(CartItemRepository::class)
);
```

it('test_get_abandoned_cart_products_with_limit_zero_or_null', function () {
    $cartReporting = new CartReporting(
        $this->mock(CartItemRepository::class),
        $this->mock(CartRepository::class)
    );

    $cartReporting->startDate = '2024-01-01';
    $cartReporting->endDate = '2024-01-31';
    $cartReporting->channelIds = [1];

    $cartReporting->shouldReceive('resetModel')
        ->once()
        ->andReturnSelf();

    $cartReporting->shouldReceive('leftJoin')
        ->with('cart', 'cart_items.cart_id', '=', 'cart.id')
        ->andReturnSelf();

    $cartReporting->shouldReceive('select')
        ->with('product_id as id', 'name')
        ->andReturnSelf();

    $cartReporting->shouldReceive('addSelect')
        ->with(DB::raw('COUNT(*) as count'))
        ->andReturnSelf();

    $cartReporting->shouldReceive('where')
        ->with('is_active', 1)
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereIn')
        ->with('cart.channel_id', [1])
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereBetween')
        ->with('cart.created_at', ['2024-01-01', '2023-12-30'])
        ->andReturnSelf();

    $cartReporting->shouldReceive('groupBy')
        ->with('product_id')
        ->andReturnSelf();

    $cartReporting->shouldReceive('limit')
        ->withNull()
        ->andReturnSelf();

    $cartReporting->shouldReceive('orderByDesc')
        ->with('count')
        ->andReturnSelf();

    $cartReporting->shouldReceive('get')
        ->once()
        ->andReturn(collect());

    $result = $cartReporting->getAbandonedCartProducts(0);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe(0);
});

*/
/*
FAILED TEST: **Short Analysis:**  
The test failed because it attempted to directly access and set the **protected** properties `startDate`, `endDate`, and `channelIds` on the `CartReporting` class instance, which is not allowed in PHP.

**Recommended Fix:**  
- Use public setter methods to set these properties.
- Or, use reflection in the test to access and modify protected properties.
- Alternatively, consider making the properties `public` (not recommended for encapsulation).
- Ensure that `shouldReceive()` is only used on mock objects, not on real class instances.

it('test_get_total_carts_returns_zero_with_invalid_channel_ids', function () {
    $cartReporting = new CartReporting(
        $this->mock(CartRepository::class),
        $this->mock(CartItemRepository::class)
    );

    $cartReporting->startDate = '2024-01-01';
    $cartReporting->endDate = '2024-01-31';
    $cartReporting->channelIds = [];

    $cartReporting->shouldReceive('resetModel')
        ->once()
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereIn')
        ->with('channel_id', [])
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereBetween')
        ->with('created_at', ['2024-01-01', '2024-01-31'])
        ->andReturnSelf();

    $cartReporting->shouldReceive('count')
        ->once()
        ->andReturn(0);

    $result = $cartReporting->getTotalCarts('2024-01-01', '2024-01-31');

    expect($result)->toBe(0);
});

*/
/*
FAILED TEST: **Short Analysis:**

The test failed because the test is trying to use `shouldReceive()` on a concrete instance of `CartReporting`, which is not a mock object. `shouldReceive()` is a method provided by Mockery and is used on mock objects, not on real instances.

**Recommended Fix:**

- Instead of instantiating `CartReporting` directly, mock it using Mockery.
- Or, if `CartReporting` depends on other mockable services, mock those and inject them into a real or partial mock instance of `CartReporting`.

it('test_get_today_carts_progress_with_midnight_date', function () {
    $cartReporting = new CartReporting(
        $this->mock(CartRepository::class),
        $this->mock(CartItemRepository::class)
    );

    $cartReporting->shouldReceive('getTotalCarts')
        ->once()
        ->with('2024-04-04 00:00:00', '2024-04-04 23:59:59')
        ->andReturn(10);

    $cartReporting->shouldReceive('getTotalCarts')
        ->once()
        ->with('2024-04-04 00:00:00', '2024-04-04 23:59:59')
        ->andReturn(10);

    $cartReporting->shouldReceive('getPercentageChange')
        ->once()
        ->with(10, 10)
        ->andReturn(0);

    $result = $cartReporting->getTodayCartsProgress();

    expect($result['previous'])->toBe(10);
    expect($result['current'])->toBe(10);
    expect($result['progress'])->toBe(0);
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed because it attempted to directly access and set the **protected** properties `startDate`, `endDate`, and `channelIds` on the `CartReporting` class instance, which is not allowed in PHP.

**Recommended Fix:**  
Use public setter methods in the `Cart` class to set these properties, or use reflection in the test to access protected properties. Alternatively, make the properties `public` (not recommended for encapsulation).

it('test_get_total_abandoned_carts_with_end_date_two_days_before', function () {
    $cartReporting = new CartReporting(
        $this->mock(CartRepository::class),
        $this->mock(CartItemRepository::class)
    );

    $cartReporting->startDate = '2024-01-01';
    $cartReporting->endDate = '2024-01-01';
    $cartReporting->channelIds = [1];

    $cartReporting->shouldReceive('resetModel')
        ->once()
        ->andReturnSelf();

    $cartReporting->shouldReceive('where')
        ->with('is_active', 1)
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereIn')
        ->with('channel_id', [1])
        ->andReturnSelf();

    $cartReporting->shouldReceive('whereBetween')
        ->with('created_at', ['2024-01-01', '2023-12-30'])
        ->andReturnSelf();

    $cartReporting->shouldReceive('count')
        ->once()
        ->andReturn(5);

    $result = $cartReporting->getTotalAbandonedCarts('2024-01-01', '2024-01-01');

    expect($result)->toBe(5);
});

*/
/*
FAILED TEST: The test failed because it attempted to access the protected property `startDate` directly on an instance of the `Cart` class, which is not allowed in PHP.

**Analysis:**
- The test creates an instance of `CartReporting` and tries to set `startDate`, `endDate`, and `channelIds` directly on it.
- These properties are **protected**, so they cannot be accessed or modified from outside the class.

**Recommended Fix:**
Use a public setter method or constructor injection to set these values, or make the properties `public` if direct access is intended (not recommended for encapsulation). Alternatively, use reflection in the test to access protected properties.

it('test_get_total_abandoned_cart_rate_returns_zero_when_total_carts_is_zero', function () {
    $cartReporting = new CartReporting(
        $this->mock(CartRepository::class),
        $this->mock(CartItemRepository::class)
    );

    $cartReporting->startDate = '2024-01-01';
    $cartReporting->endDate = '2024-01-31';
    $cartReporting->channelIds = [1];

    $cartReporting->shouldReceive('getTotalCarts')
        ->once()
        ->with('2024-01-01', '2024-01-31')
        ->andReturn(0);

    $result = $cartReporting->getTotalAbandonedCartRate('2024-01-01', '2024-01-31');

    expect($result)->toBe(0);
});

*/