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

