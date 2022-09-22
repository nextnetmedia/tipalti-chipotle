<?php

namespace Nextnetmedia\Chipotle;

class iFrame
{
    /**
     * @var TipaltiPayer
     */
    private $client;
    /**
     * @var string
     */
    private $iFrameBaseUrl;

    public function __construct(TipaltiPayer $client)
    {
        $this->client = $client;
        $this->iFrameBaseUrl = $this->client->production ? "https://ui2.tipalti.com/" : "https://ui2.sandbox.tipalti.com/";
    }

    public function getPayeeHome(string $payeeIdentifier, array $extraParameters = []): string
    {
        return $this->getiFrameUrl("home", $payeeIdentifier, $extraParameters);
    }

    public function getPayeeInvoiceHistory(string $payeeIdentifier, array $extraParameters = []): string
    {
        return $this->getiFrameUrl("invoices", $payeeIdentifier, $extraParameters);
    }

    public function getPayeePaymentHistory(string $payeeIdentifier, array $extraParameters = []): string
    {
        return $this->getiFrameUrl("history", $payeeIdentifier, $extraParameters);
    }

    public function getiFrameUrl(string $type, string $payeeIdentifier, array $extraParameters = []): string
    {
        /*
        $queryString = $extraParameters;
        $queryString["ts"] = time();
        $queryString["idap"] = empty($this->client->getIdapPrefix()) ? $payeeIdentifier : $this->client->getIdapPrefix() . $payeeIdentifier;;
        $queryString["payer"] = $this->client->getPayerName();
        return $this->iframeBaseUrl($type) . "?" . $this->buildEncryptedQueryString($queryString);
        */
    }
}
