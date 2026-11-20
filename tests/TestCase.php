<?php

namespace Muzi\Jeepay\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Mockery;

class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
