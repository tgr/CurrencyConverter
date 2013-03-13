<?php

class CurrencyConverter
{
    /** @var string this is the currency the class will convert to */
    public $newCurrency = 'USD';

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
            $this->exchangeRates = $this->ratesDao->loadRatesForCurrency($this->newCurrency);
        }
        return $this->exchangeRates;
    }

    /**
     * Gets rates from the service and stores them locally
     */
    public function refreshRates()
    {
        $exchangeRates = $this->ratesService->getRates();
        $this->ratesDao->replaceRatesForCurrency($this->newCurrency, $exchangeRates);
    }

    /**
     * @param string $currency ISO code of currency
     * @param number $amount amount of currency
     * @throws Exception if the exchange rate of the currency is not known
     * @return number converted amount
     */
    public function convert($currency, $amount)
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
     * @param string $sum sum to be converted in "<currency> <amount>" format, currency is ISO code
     * @return string converted sum
     */
    public function convertString($sum)
    {
        list($currency, $amount) = $this->parseSum($sum);
        $convertedAmount = $this->convert($currency, $amount);
        return $this->newCurrency .' '. $convertedAmount;
    }

    /**
     * @param array $sums array of strings in "<currency> <amount>" format, currency is ISO code
     * @return array converted sums in same format as input
     */
    public function convertArray(array $sums)
    {
        $converted = array();
        foreach ($sums as $sum) {
            $converted[] = $this->convertString($sum);
        }
        return $converted;
    }

    /**
     * Parses the input string, checks for correctness
     * @param string $sum
     * @return array [currency, number]
     * @throws Exception if malformed
     */
    protected function parseSum($sum)
    {
        $result = sscanf($sum, "%s %d");
        if ($result == -1 || !preg_match('/^\w{3}$/', $result[0]) || !is_numeric($result[1])) {
            throw new Exception('Invalid sum: ' . $sum);
        }
        return $result;
    }
}
