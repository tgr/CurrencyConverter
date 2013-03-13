<?php

class ExchangeRatesDAO
{
    /** @var string database table name */
    public $table = 'exchange_rates';

    /** @var string PDO data source name  */
    protected $dsn;

    /** @var string DB username */
    protected $username;

    /** @var string DB password */
    protected $password;

    /** @var PDO */
    protected $db;

    public function __construct($dsn, $username = null, $password = null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns rates for converting to $currency.
     * @param string $currency ISO code of currency
     * @return array original currency => rate
     */
    public function loadRatesForCurrency($currency)
    {
        $rates = array();
        $db = $this->getConnection();
        $stmt = $db->prepare("SELECT * FROM `$this->table` WHERE er_to = :er_to");
        $stmt->bindValue(':er_to', $currency);
        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            $rates[$row['er_from']] = $row['er_rate'];
        }
        return $rates;
    }

    /**
     * Replaces all old rates for converting to $currency with new ones from $exchangeRates.
     * @param string $currency ISO code of currency
     * @param array $exchangeRates exchange rates in currency => rate format
     */
    public function replaceRatesForCurrency($currency, $exchangeRates)
    {
        $db = $this->getConnection();
        $db->beginTransaction();
        // FIXME should we keep old rates if we have no updates for them?
        $this->clearDataForCurrency($currency);
        $this->addRatesForCurrency($currency, $exchangeRates);
        $db->commit();
    }

    /**
     * @param string $currency ISO code of currency
     */
    protected function clearDataForCurrency($currency)
    {
        $db = $this->getConnection();
        $stmt = $db->prepare("DELETE FROM `$this->table` WHERE er_to = :er_to");
        $stmt->bindValue(':er_to', $currency);
        $stmt->execute();
    }

    /**
     * @param string $newCurrency ISO code of currency
     * @param array $exchangeRates exchange rates for $newCurrency in old currency => rate format
     */
    protected function addRatesForCurrency($newCurrency, $exchangeRates)
    {
        $db = $this->getConnection();
        $stmt = $db->prepare("INSERT INTO`$this->table` (er_from, er_to, er_rate) VALUES (:er_from, :er_to, :er_rate)");
        foreach ($exchangeRates as $oldCurrency => $rate) {
            $stmt->bindValue(':er_from', $oldCurrency);
            $stmt->bindValue(':er_to', $newCurrency);
            $stmt->bindValue(':er_rate', $rate);
            $stmt->execute();
        }
    }

    /**
     * @return PDO
     */
    protected function getConnection()
    {
        if (!$this->db) {
            $this->db = new PDO($this->dsn, $this->username, $this->password);
        }
        return $this->db;
    }
}
