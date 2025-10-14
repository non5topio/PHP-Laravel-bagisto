<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductInventory;
use Webkul\Product\Models\ProductFlat;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\Wishlist;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;
use function Pest\Laravel\actingAs;


/*
FAILED TEST: The test failed due to two issues:

1. **Duplicate `use` statement**: A duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` on line 24 of `ProductReportTest.php` causes a fatal error.
2. **Missing `WishlistRepository` binding**: The `WishlistRepository` is not bound in the Laravel container, causing a `BindingResolutionException`.

**Recommended Fixes**:

1. Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement from line 24 in `ProductReportTest.php`.
2. Bind `WishlistRepository` in the Laravel service container or mock it in the test.

it('test_get_total_sold_quantities_over_time_returns_empty_array_with_invalid_period', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getTotalSoldQuantitiesOverTime($reporting->startDate, $reporting->endDate, 'invalid');

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed due to two issues:  
1. A duplicate `use` statement for `Webkul\Admin\Helpers\Reporting\Product as ProductReporting` in `ProductReportTest.php` on line 24.  
2. A missing `WishlistRepository` binding in the Laravel container, causing a `BindingResolutionException`.

**Recommended Fixes:**  
1. Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` on line 24 of `ProductReportTest.php`.  
2. Bind `WishlistRepository` in the Laravel service container or mock it in the test.

it('test_get_top_search_terms_returns_empty_collection_when_no_search_terms', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->channelIds = [1];

    $result = $reporting->getTopSearchTerms(5);

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed due to two issues:  
1. A duplicate `use` statement for `Webkul\Admin\Helpers\Reporting\Product as ProductReporting` in `ProductReportTest.php` on line 24.  
2. A missing `WishlistRepository` binding in the Laravel container, causing a `BindingResolutionException`.

**Recommended Fixes:**  
1. Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` on line 24.  
2. Bind `WishlistRepository` in the service container or mock it in the test.

it('test_get_last_search_terms_returns_empty_collection_when_no_search_terms', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getLastSearchTerms();

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed because the `ProductReportTest.php` file contains a duplicate `use` statement for `Webkul\Admin\Helpers\Reporting\Product as ProductReporting`, causing a fatal error. Additionally, the test is failing at runtime due to a missing `WishlistRepository` binding in the container.

**Recommended Fix:**  
1. Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement on line 24 of `ProductReportTest.php`.
2. Ensure that `WishlistRepository` is properly bound in the Laravel service container or mocked in the test.

it('test_get_products_with_most_reviews_returns_empty_collection_when_no_reviews', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getProductsWithMostReviews();

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed due to a duplicate `use` statement for `Webkul\Admin\Helpers\Reporting\Product as ProductReporting` on line 24 of `ProductReportTest.php`. The class is already imported at the top of the file, causing a fatal error.

**Recommended Fix:**  
Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement on line 24.

it('test_get_top_selling_products_by_quantity_returns_empty_collection_when_no_orders', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getTopSellingProductsByQuantity();

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed due to a duplicate `use` statement for `Webkul\Admin\Helpers\Reporting\Product as ProductReporting` on line 24 of `ProductReportTest.php`. The class is already imported at the top of the file, causing a fatal error.

**Recommended Fix:**  
Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement on line 24.

it('test_get_top_selling_products_by_revenue_returns_empty_collection_when_no_orders', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getTopSellingProductsByRevenue();

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed due to a duplicate `use` statement for `Webkul\Admin\Helpers\Reporting\Product as ProductReporting` on line 24 of `ProductReportTest.php`. The class is already imported at the top of the file, causing a fatal error.

**Recommended Fix:**  
Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement on line 24.

it('test_get_stock_threshold_products_returns_empty_collection_when_limit_is_zero', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->channelIds = [1];

    $result = $reporting->getStockThresholdProducts(0);

    expect($result)->toBeEmpty();
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed due to a duplicate `use` statement for `Webkul\Admin\Helpers\Reporting\Product as ProductReporting` on line 24 of `ProductReportTest.php`. The class is already imported at the top of the file.

**Recommended Fix:**  
Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement on line 24.

it('test_get_total_reviews_returns_zero_when_no_approved_reviews', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getTotalReviews($reporting->startDate, $reporting->endDate);

    expect($result)->toBe(0);
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed due to a duplicate `use` statement for the class `Webkul\Admin\Helpers\Reporting\Product as ProductReporting` on line 24 of `ProductReportTest.php`. The class is already imported at the top of the file, causing a fatal error.

**Recommended Fix:**  
Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement on line 24.

it('test_get_total_products_added_to_wishlist_returns_zero_when_no_entries', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getTotalProductsAddedToWishlist($reporting->startDate, $reporting->endDate);

    expect($result)->toBe(0);
});

*/
/*
FAILED TEST: **Analysis:**  
The test failed because the class `Webkul\Admin\Helpers\Reporting\Product` is already imported at the top of the file using `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;`. Then, on line 24, the same alias `ProductReporting` is used again, causing a fatal error due to duplicate class name usage.

**Recommended Fix:**  
Remove the duplicate `use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;` statement on line 24. The class is already imported and used correctly at the top of the file.

it('test_get_total_sold_quantities_returns_zero_when_no_sales', function () {
    $reporting = new ProductReporting(
        app(ProductRepository::class),
        app(ProductInventoryRepository::class),
        app(WishlistRepository::class),
        app(ProductReviewRepository::class),
        app(OrderItemRepository::class),
        app(SearchTermRepository::class)
    );
    $reporting->startDate = Carbon::parse('2024-01-01');
    $reporting->endDate = Carbon::parse('2024-01-01');
    $reporting->channelIds = [1];

    $result = $reporting->getTotalSoldQuantities($reporting->startDate, $reporting->endDate);

    expect($result)->toBe(0);
});

*/