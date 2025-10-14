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

