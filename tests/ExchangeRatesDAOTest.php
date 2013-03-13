<?php

require_once('ExchangeRatesDao.php');

class ExchangeRatesDAOTest extends PHPUnit_Framework_TestCase
{
    /** @var ExchangeRatesDAO */
    public $dao;

    public function setUp()
    {
        $dsn = 'mysql:host=localhost;dbname=currency_converter_test';
        $username = 'root';
        $password = null;
        $this->dao = new ExchangeRatesDAO($dsn, $username, $password);

    }

    public function testReadWrite()
    {
        $this->assertReadWrite(array());
        $this->assertReadWrite(array('JPY' => 0.1, 'CHF' => 42));
        $this->assertReadWrite(array());
    }

    protected function assertReadWrite($data)
    {
        $this->dao->replaceRatesForCurrency('USD', $data);
        $readBackData = $this->dao->loadRatesForCurrency('USD');
        $this->assertEquals($data, $readBackData);
    }
}
