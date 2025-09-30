<?php

use Carbon\Carbon;
use Webkul\Admin\Helpers\Reporting\Cart as CartReporting;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Models\Customer;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\get;

it('should calculate total carts progress correctly', function () {
    // Arrange.
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $customer = Customer::factory()->create();

    // Create carts for current period (today)
    $currentCart1 = Cart::factory()->create([
        'customer_id'         => $customer->id,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
        'customer_email'      => $customer->email,
        'channel_id'          => core()->getDefaultChannel()->id,
        'created_at'          => now(),
    ]);

    $currentCart2 = Cart::factory()->create([
        'customer_id'         => $customer->id,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
        'customer_email'      => $customer->email,
        'channel_id'          => core()->getDefaultChannel()->id,
        'created_at'          => now(),
    ]);

    // Create cart for previous period (yesterday)
    $previousCart = Cart::factory()->create([
        'customer_id'         => $customer->id,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
        'customer_email'      => $customer->email,
        'channel_id'          => core()->getDefaultChannel()->id,
        'created_at'          => now()->subDay(),
    ]);

    // Act.
    $cartReporting = app(CartReporting::class);
    
    // Set up reporting dates (today vs yesterday)
    $cartReporting->setStartDate(now()->startOfDay());
    $cartReporting->setEndDate(now()->endOfDay());
    
    $result = $cartReporting->getTotalCartsProgress();

    // Assert.
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['previous', 'current', 'progress'])
        ->and($result['current'])->toBe(2) // Two carts created today
        ->and($result['previous'])->toBe(0) // No carts in the "previous" period when comparing today vs yesterday
        ->and($result['progress'])->toBeFloat(); // Progress should be a percentage
});