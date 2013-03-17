<?php

class CurrencyConverter
{
    /** @var string this is the currency the class will convert to */
    public $baseCurrency = 'USD';

    /** @var int|null number of decimal places to round to, null means no rounding */
    public $precision = 2;

    /** @var ExchangeRatesService */
    protected $ratesService;

    /** @var ExchangeRatesDAO */
    protected $ratesDao;

    /** @var array cache for exchange rates, in currency => rate format */
    protected $exchangeRates = null;

    public function __construct(ExchangeRatesService $ratesService, ExchangeRatesDAO $ratesDao)
    {
        $this->ratesService = $ratesService;
        $this->ratesDao = $ratesDao;
    }

    /**
     * Loads rates from DB or internal cache
     */
    public function getRates()
    {
        if ($this->exchangeRates === null) {
            $this->exchangeRates = $this->ratesDao->loadRatesForCurrency($this->baseCurrency);
        }
        return $this->exchangeRates;
    }

    /**
     * Gets rates from the service and stores them locally
     */
    public function refreshRates()
    {
        $exchangeRates = $this->ratesService->getRates();
        $this->ratesDao->replaceRatesForCurrency($this->baseCurrency, $exchangeRates);
    }

    /**
     * Converts from $currency to $this->baseCurrency
     * @param string $currency ISO code of currency
     * @param number $amount amount of currency
     * @return number converted amount
     */
    public function convertFrom($currency, $amount)
    {
        $exchangeRates = $this->getRates();
        if (!isset($exchangeRates[$currency])) {
            throw new Exception('Rate not found for currency ' . $currency);
        }
        $convertedAmount = $amount * $exchangeRates[$currency];
        if (isset($this->precision)) {
            $convertedAmount = round($convertedAmount, $this->precision);
        }
        return $convertedAmount;
    }

    /**
     * Converts from $this->baseCurrency to $currency, and returns a round number near to the result.
     * "Round" is used in the loose sense (e.g. 65 instead of 63.72).
     * @param string $currency ISO code of currency
     * @param number $fromAmount amount to convert
     * @return number converted amount
     */
    public function roundTo($currency, $fromAmount)
    {
        $exchangeRates = $this->getRates();
        if (!isset($exchangeRates[$currency])) {
            throw new Exception('Rate not found for currency ' . $currency);
        }
        $convertedAmount = $fromAmount / $exchangeRates[$currency];
        return $this->makeRound($convertedAmount);
    }

    /**
     * @param string $sum sum to be converted in "<currency> <amount>" format, currency is ISO code
     * @return string converted sum
     */
    public function convertFromString($sum)
    {
        list($currency, $amount) = $this->parseSum($sum);
        $convertedAmount = $this->convertFrom($currency, $amount);
        return $this->baseCurrency .' '. $convertedAmount;
    }

    /**
     * @param array $sums array of strings in "<currency> <amount>" format, currency is ISO code
     * @return array converted sums in same format as input
     */
    public function convertFromArray(array $sums)
    {
        $converted = array();
        foreach ($sums as $sum) {
            $converted[] = $this->convertFromString($sum);
        }
        return $converted;
    }

    /**
     * Parses the input string, checks for correctness
     * @param string $sum
     * @return array [currency, number]
     */
    protected function parseSum($sum)
    {
        $result = sscanf($sum, "%s %d");
        if ($result == -1 || !preg_match('/^\w{3}$/', $result[0]) || !is_numeric($result[1])) {
            throw new Exception('Invalid sum: ' . $sum);
        }
        return $result;
    }

    /**
     * Returns a round number close to the original.
     * "Round" is used in the loose sense (e.g. 65 instead of 63.72).
     * @param number $sum
     * @return number
     */
    public function makeRound($sum)
    {
        $exponent = floor(log10($sum)) + 1; // exponent in normalized scientific notation
        $valuableDigits = round($sum * pow(10, 2 - $exponent)); // first two valuable digits
        $valuableDigits = round($valuableDigits / 5) * 5; // first two valuable digits rounded to 5
        return $valuableDigits * pow(10, $exponent - 2);
    }
}
