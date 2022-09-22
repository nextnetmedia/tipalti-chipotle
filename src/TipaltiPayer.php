<?php

namespace Nextnetmedia\Chipotle;

use Exception;
use SoapFault;
use Nextnetmedia\Tipalti\Authentication\EncryptionKey;
use Nextnetmedia\Tipalti\PayerClient;
use Nextnetmedia\Tipalti\Resource\ArrayOfTipaltiInvoiceItemRequest;

/**
 * Tipalti base client for Payer functions, used for invoices and iFrame URL generation
 */
class TipaltiPayer
{
    /**
     * @var PayerClient
     */
    private $client;
    /**
     * @var string|null
     */
    private $payerEntityName;
    /**
     * @var string|null
     */
    private $idapPrefix;
    /**
     * @var string|null
     */
    private $refcodePrefix;
    /**
     * @var string
     */
    private $payerName;
    /**
     * @var bool
     */
    private $production;
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $apiKey
     * @param string $payerName
     * @param bool $production
     * @param string|null $payerEntityName
     * @param string|null $idapPrefix
     * @param string|null $refcodePrefix
     * @throws Exception
     */
    public function __construct(string $apiKey, string $payerName, bool $production = false, ?string $payerEntityName = "", ?string $idapPrefix = "", ?string $refcodePrefix = "")
    {
        $this->apiKey = $apiKey;
        $this->payerName = $payerName;
        $this->production = $production;
        $this->idapPrefix = $idapPrefix;
        $this->refcodePrefix = $refcodePrefix;
        $this->payerEntityName = $payerEntityName;
        try {
            $this->client = new PayerClient($apiKey, $payerName, $production);
        } catch (SoapFault $e) {
            // Change SoapFault to Exception so that clients don't need to worry about catching SoapFaults
            throw new Exception("Tipalti Payer Client SOAP Fault: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Tipalti Payer Client Initialization Error: " . $e->getMessage());
        }
    }

    /**
     * @return string|null
     */
    public function getRefcodePrefix(): ?string
    {
        return $this->refcodePrefix;
    }

    /**
     * @return string|null
     */
    public function getIdapPrefix(): ?string
    {
        return $this->idapPrefix;
    }

    /**
     * @return string|null
     */
    public function getPayerName(): ?string
    {
        return $this->payerName;
    }

    /**
     * @return string|null
     */
    public function getPayerEntityName(): ?string
    {
        return $this->payerEntityName;
    }

    /**
     * @param ArrayOfTipaltiInvoiceItemRequest $arrayOfTipaltiInvoiceItemRequest
     * @return bool
     */
    public function callCreateOrUpdateInvoices(ArrayOfTipaltiInvoiceItemRequest $arrayOfTipaltiInvoiceItemRequest): bool
    {
        $this->client->CreateOrUpdateInvoices($arrayOfTipaltiInvoiceItemRequest);
        return true;
    }

    /**
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->production;
    }

    /**
     * @param array $queryArray
     * @return string
     */
    public function buildEncryptedQueryString(array $queryArray): string
    {
        $queryArray['hashkey'] = EncryptionKey::generateHmac(http_build_query($queryArray), $this->getApiKey());
        return http_build_query($queryArray);
    }

    /**
     * @return string
     */
    private function getApiKey(): string
    {
        return $this->apiKey;
    }
}
