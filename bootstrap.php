<?php

require_once('ExchangeRatesDAO.php');
require_once('ExchangeRatesService.php');
require_once('CurrencyConverter.php');

$dsn = 'mysql:host=localhost;dbname=currency_converter';
$username = 'root';
$password = 'null';

$dao = new ExchangeRatesDAO($dsn, $username, $password);
$service = new ExchangeRatesService();
$currencyConverter = new CurrencyConverter($service, $dao);