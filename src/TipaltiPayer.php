<?php

namespace Nextnetmedia\Chipotle;

use Exception;
use Nextnetmedia\Tipalti\PayerClient;
use Nextnetmedia\Tipalti\Resource\ArrayOfTipaltiInvoiceItemRequest;
use SoapFault;

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
    protected $idapPrefix;
    /**
     * @var string|null
     */
    private $refcodePrefix;

    /**
     * @throws Exception
     */
    public function __construct(string $apiKey, string $payerName, bool $production, ?string $payerEntityName = "", ?string $idapPrefix = "", ?string $refcodePrefix = "")
    {
        try {
            $this->client = new PayerClient($apiKey, $payerName, $production);
        } catch (SoapFault $e) {
            // Change SoapFault to Exception so that clients don't need to worry about SoapFaults
            throw new Exception("Tipalti Payer Client SOAP Fault: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Tipalti Payer Client Initialization Error: " . $e->getMessage());
        }
        $this->idapPrefix = $idapPrefix;
        $this->refcodePrefix = $refcodePrefix;
        $this->payerEntityName = $payerEntityName;
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
     * @throws Exception
     */
    public function callCreateOrUpdateInvoices(ArrayOfTipaltiInvoiceItemRequest $arrayOfTipaltiInvoiceItemRequest): bool
    {
        $this->client->CreateOrUpdateInvoices($arrayOfTipaltiInvoiceItemRequest);
        return true;
    }
}
