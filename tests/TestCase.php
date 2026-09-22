<?php

namespace Tests;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property Customer $customer
 * @property CustomerAddress $address
 */
#[\AllowDynamicProperties]
abstract class TestCase extends BaseTestCase
{
    public $customer;
    public $address;
}
