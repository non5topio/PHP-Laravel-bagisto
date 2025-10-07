<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\Locale;
use Webkul\Core\Models\Currency;
use Webkul\Customer\Models\CustomerGroup;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create minimal test data (SQLite-compatible, no seeders needed)
        $this->seedMinimalTestData();
    }
    
    /**
     * Seed minimal data required for tests (SQLite compatible).
     */
    protected function seedMinimalTestData(): void
    {
        // Create default locale
        Locale::create([
            'id' => 1,
            'code' => 'en',
            'name' => 'English',
            'direction' => 'ltr',
        ]);
        
        // Create default currency
        $currency = Currency::create([
            'id' => 1,
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
        ]);
        
        // Create default channel (without root_category_id to avoid FK constraint)
        Channel::create([
            'id' => 1,
            'code' => 'default',
            'name' => 'Default',
            'description' => 'Default Channel',
            'hostname' => 'localhost',
            'default_locale_id' => 1,
            'base_currency_id' => $currency->id,
        ]);
        
        // Create default customer group
        CustomerGroup::create([
            'id' => 1,
            'code' => 'guest',
            'name' => 'Guest',
            'is_user_defined' => 0,
        ]);
    }
}
