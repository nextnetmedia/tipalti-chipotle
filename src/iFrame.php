<?php

namespace Nextnetmedia\Chipotle;

use Exception;

/**
 * Get Payee iFrame URLs for Tipalti payees
 */
class iFrame
{
    /**
     * @var TipaltiPayer
     */
    private $client;

    /**
     *
     */
    private const IFRAME_BASE_URLS = [
      "production" => "https://ui2.tipalti.com/",
      "sandbox" => "https://ui2.sandbox.tipalti.com/"
    ];

    /**
     *
     */
    private const IFRAME_PATHS = [
      "home" => "payeedashboard/home",
      "invoices" => "PayeeDashboard/Invoices",
      "payments" => "PayeeDashboard/PaymentsHistory"
    ];

    /**
     * @param TipaltiPayer $client
     */
    public function __construct(TipaltiPayer $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $payeeIdentifier
     * @param array $extraParameters
     * @return string
     * @throws Exception
     */
    public function getPayeeHome(string $payeeIdentifier, array $extraParameters = []): string
    {
        return $this->getiFrameUrl("home", $payeeIdentifier, $extraParameters);
    }

    /**
     * @param string $payeeIdentifier
     * @param array $extraParameters
     * @return string
     * @throws Exception
     */
    public function getPayeeInvoiceHistory(string $payeeIdentifier, array $extraParameters = []): string
    {
        return $this->getiFrameUrl("invoices", $payeeIdentifier, $extraParameters);
    }

    /**
     * @param string $payeeIdentifier
     * @param array $extraParameters
     * @return string
     * @throws Exception
     */
    public function getPayeePaymentHistory(string $payeeIdentifier, array $extraParameters = []): string
    {
        return $this->getiFrameUrl("payments", $payeeIdentifier, $extraParameters);
    }

    /**
     * @throws Exception
     */
    public function getiFrameUrl(string $type, string $payeeIdentifier, array $extraParameters = []): string
    {
        $queryString = $extraParameters;
        $queryString["ts"] = time();
        $queryString["idap"] = empty($this->client->getIdapPrefix()) ? $payeeIdentifier : $this->client->getIdapPrefix() . $payeeIdentifier;
        ;
        $queryString["payer"] = $this->client->getPayerName();
        return $this->getiFrameBasePath($type) . "?" . $this->client->buildEncryptedQueryString($queryString);
    }

    /**
     * @throws Exception
     */
    private function getiFrameBasePath(string $type): string
    {
        $baseurl = ($this->client->isProduction()) ? self::IFRAME_BASE_URLS["production"] : self::IFRAME_BASE_URLS["sandbox"];
        if (!isset(self::IFRAME_PATHS[$type])) {
            throw new Exception('Invalid iFrame type specified');
        }
        $path = self::IFRAME_PATHS[$type];
        return $baseurl . $path;
    }
}
