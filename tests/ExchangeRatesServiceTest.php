<?php

require_once('ExchangeRatesService.php');

class ExchangeRatesServiceTest extends PHPUnit_Framework_TestCase
{
    /** @var ExchangeRatesService */
    public $service;

    public function setUp()
    {
        $this->service = new ExchangeRatesService();
    }

    public function testGetRates()
    {
        $rates = $this->service->getRates();
        $this->assertInternalType('array', $rates);
        $this->assertArrayHasKey('JPY', $rates); // FIXME not a very stable test...
        $this->assertTrue(is_numeric($rates['JPY']), 'Exchange rate should be a number');
    }
}
