<?php

set_include_path('..');
require_once('ExchangeRatesDAO.php');
require_once('ExchangeRatesService.php');
require_once('CurrencyConverter.php');

$dsn = 'mysql:host=localhost;dbname=currency_converter';
$username = 'root';
$password = null;

/** @var array $options donation options in USD */
$options = array(10, 25, 50, 100);

$dao = new ExchangeRatesDAO($dsn, $username, $password);
$service = new ExchangeRatesService();
$currencyConverter = new CurrencyConverter($service, $dao);

$currency = isset($_GET['currency']) ? $_GET['currency'] : 'USD';
$availableCurrencies = array_keys($currencyConverter->getRates());
$availableCurrencies[] = 'USD';

if ($currency == 'USD') {
    // we are good, no need to convert
} else if (!in_array($currency, $availableCurrencies)) {
    $error = 'Invalid currency!';
} else {
    $options = array_map(function($sum) use ($currencyConverter, $currency) {
        return $currencyConverter->roundTo($currency, $sum);
    }, $options);
}

/** @var array $messages placeholder for i18n */
$messages = array(
    'title' => '',
    'currency-switcher-title' => 'Use another currency:',
    'privacy-policy' => 'Privacy policy',
    'more-information' => 'More information',
    'donation-buttons-title' => 'Please choose your donation size:',
);
function t($key) {
    global $messages;
    return $messages[$key];
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= t('title') ?></title>
    <meta name="viewport" content="width=device-width">
    <style><?php
        // with so small css and html size, and with most users only viewing the donation page a single time,
        // inlining the css is faster
        include('main.css');
    ?></style>
    <!--[if lt IE 9]>
    <script src="respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div class="wrapper">
        <div class="currency-switcher">
            <?= t('currency-switcher-title') ?>
            <ul>
            <?php foreach (array_diff($availableCurrencies, array($currency)) as $otherCurrency): ?>
                <li><a href="?currency=<?= $otherCurrency ?>"><?= $otherCurrency ?></a></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php if (isset($error)): ?>
            <div class="content error"><?= $error ?></div>
        <?php else: ?>
            <div class="content donation-buttons">
                <p><?= t('donation-buttons-title') ?></p>
                <ul>
                <?php foreach ($options as $option): ?>
                    <li><a href="payment?currency=<?= $currency ?>&amount=<?= $option ?>"><?= $currency .' '. $option ?></a></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="footer">
            <ul>
                <li><a href="privacy-policy"><?= t('privacy-policy') ?></a></li>
                <li><a href="more-information"><?= t('more-information') ?></a></li>
            </ul>
        </div>
    </div>
</body>
</html>