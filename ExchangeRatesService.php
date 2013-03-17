<?php

/**
 * Gets currency information from a web service.
 */
class ExchangeRatesService
{
    public $url = 'https://toolserver.org/~kaldari/rates.xml';

    public $userAgent = 'FundraiserCurrencyConverter/1.0';

    /**
     * Returns an array of currency conversion rates.
     * @return array currency => rate
     */
    public function getRates()
    {
        $xml = $this->getXmlData();
        $rates = $this->parseXml($xml);
        return $rates;
    }

    /**
     * @return string
     */
    protected function getXmlData()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
            throw new Exception('Error downloading currency rate data: ' . curl_errno($ch) . ' ' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }

    /**
     * @param string $xml
     * @return array currency => rate
     */
    protected function parseXml($xml)
    {
        $response = simplexml_load_string($xml);
        if ($response === false) {
            throw new Exception("Could not parse XML:\n\t" . implode("\n\t", libxml_get_errors()));
        }

        $rates = array();
        foreach ($response->conversion as $conversion) {
            $rates[(string)$conversion->currency] = (string)$conversion->rate;
        }
        return $rates;
    }
}
