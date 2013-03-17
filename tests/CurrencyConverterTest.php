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
        'CHF' => 1.19,
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

    /**
     * @dataProvider makeRoundGenerator
     */
    public function testMakeRound($sum, $round)
    {
        $this->assertEquals($round, $this->currencyConverter->makeRound($sum));
    }

    public function makeRoundGenerator()
    {
        return array(
            // keep round numbers
            array(1, 1),
            array(10, 10),
            array(100, 100),
            array(1000, 1000),
            array(25, 25),
            // round first two valuable digits to multiple of 5
            array(9.7, 9.5),
            array(3.25, 3.5),
            array(0.123, 0.1),
            array(0.127, 0.15),
            array(1235, 1000),
            array(1275, 1500),
        );
    }

    public function testConvert()
    {
        $this->assertEquals(10, $this->currencyConverter->convertFrom('JPY', 1000));
        $this->assertEquals('USD 10', $this->currencyConverter->convertFromString('JPY 1000'));
        $this->assertEquals(array('USD 10', 'USD 15'), $this->currencyConverter->convertFromArray(array('JPY 1000', 'EUR 10')));
        $this->assertEquals(20, $this->currencyConverter->roundTo('CHF', 25));
    }
}
