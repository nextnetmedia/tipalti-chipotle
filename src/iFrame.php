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
     * Default height for the iFrame
     */
    public const IFRAME_DEFAULT_HEIGHT = 200;

    /**
     * The base URL's provided by Tipalti for each environment
     */
    private const IFRAME_BASE_URLS = [
      "production" => "https://ui2.tipalti.com/",
      "sandbox" => "https://ui2.sandbox.tipalti.com/"
    ];

    /**
     * and the paths to use depending on the type of iFrame to show
     */
    private const IFRAME_PATHS = [
      "home" => "payeedashboard/home",
      "invoices" => "PayeeDashboard/Invoices",
      "payments" => "PayeeDashboard/PaymentsHistory"
    ];

    /**
     * Default style for the iFrame
     */
    public const DEFAULT_STYLE = "border: none; margin-top: 20px; margin-bottom: 20px;";

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
     * @param bool $fullHtml
     * @return string
     * @throws Exception
     */
    public function getPayeeHome(string $payeeIdentifier, array $extraParameters = [], bool $fullHtml = false, $height = self::IFRAME_DEFAULT_HEIGHT): string
    {
        return !$fullHtml ? $this->getiFrameUrl("home", $payeeIdentifier, $extraParameters) : $this->getiFrameHTML("home", $payeeIdentifier, $extraParameters, $height);
    }

    /**
     * @param string $payeeIdentifier
     * @param array $extraParameters
     * @param bool $fullHtml
     * @return string
     * @throws Exception
     */
    public function getPayeeInvoiceHistory(string $payeeIdentifier, array $extraParameters = [], bool $fullHtml = false, $height = self::IFRAME_DEFAULT_HEIGHT): string
    {
        return !$fullHtml ? $this->getiFrameUrl("invoices", $payeeIdentifier, $extraParameters) : $this->getiFrameHTML("invoices", $payeeIdentifier, $extraParameters, $height);
    }

    /**
     * @param string $payeeIdentifier
     * @param array $extraParameters
     * @param bool $fullHtml
     * @return string
     * @throws Exception
     */
    public function getPayeePaymentHistory(string $payeeIdentifier, array $extraParameters = [], bool $fullHtml = false, $height = self::IFRAME_DEFAULT_HEIGHT): string
    {
        return !$fullHtml ? $this->getiFrameUrl("payments", $payeeIdentifier, $extraParameters) : $this->getiFrameHTML("payments", $payeeIdentifier, $extraParameters, $height);
    }

    /**
     * @param string $type
     * @param string $payeeIdentifier
     * @param array $extraParameters
     * @return string
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
     * @param string $type
     * @return string
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

    /**
     * @param string $type
     * @param string $payeeIdentifier
     * @param array $extraParameters
     * @param string $style
     * @param int $height
     * @return string
     * @throws Exception
     */
    public function getiFrameHTML(string $type, string $payeeIdentifier, array $extraParameters = [], string $style = self::DEFAULT_STYLE, $height = self::IFRAME_DEFAULT_HEIGHT): string
    {
        $url = $this->getiFrameUrl($type, $payeeIdentifier, $extraParameters);
        return '<iframe width="100%" height="' . $height . '" style="' . $style . '" src="' . $url . '" id="tipaltiEmbed"></iframe><script>tipaltiiFrameResize=function(t){t.data&&t.data.TipaltiIframeInfo&&t.data.TipaltiIframeInfo.height&&(document.getElementById("tipaltiEmbed").height=t.data.TipaltiIframeInfo.height)},window.addEventListener?window.addEventListener("message",tipaltiiFrameResize,!1):window.attachEvent("onmessage",tipaltiiFrameResize);</script>';
    }
}
