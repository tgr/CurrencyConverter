<?php

require_once('ExchangeRatesDao.php');
require_once('ExchangeRatesService.php');
require_once('CurrencyConverter.php');

class CurrencyConverterTest extends PHPUnit_Framework_TestCase
{
    /** @var CurrencyConverter */
    public $currencyConverter;

    public $testRates = array(
        'JPY' => 0.01,
        'EUR' => 1.5,
    );

    public function setUp()
    {
        $dsn = 'mysql:host=localhost;dbname=currency_converter';
        $username = 'root';
        $password = null;

        $service = $this->getMock('ExchangeRatesService');
        $dao = $this->getMock('ExchangeRatesDAO', array(), array($dsn, $username, $password));
        $dao->expects($this->any())->method('loadRatesForCurrency')->will($this->returnValue($this->testRates));
        $this->currencyConverter = new CurrencyConverter($service, $dao);
    }

    public function testConvert()
    {
        $this->assertEquals(10, $this->currencyConverter->convert('JPY', 1000));
        $this->assertEquals('USD 10', $this->currencyConverter->convertString('JPY 1000'));
        $this->assertEquals(array('USD 10', 'USD 15'), $this->currencyConverter->convertArray(array('JPY 1000', 'EUR 10')));
    }
}
