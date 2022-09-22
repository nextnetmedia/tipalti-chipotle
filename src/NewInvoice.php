<?php

namespace Nextnetmedia\Chipotle;

use DateTime;
use Exception;
use Nextnetmedia\Tipalti\Resource\ArrayOfInvoiceLine;
use Nextnetmedia\Tipalti\Resource\ArrayOfKeyValuePair;
use Nextnetmedia\Tipalti\Resource\ArrayOfTipaltiInvoiceItemRequest;
use Nextnetmedia\Tipalti\Resource\InvoiceLine;
use Nextnetmedia\Tipalti\Resource\KeyValuePair;
use Nextnetmedia\Tipalti\Resource\TipaltiInvoiceItemRequest;

/**
 * Build and send an invoice to Tipalti
 */
class NewInvoice
{
    /**
     * @var TipaltiPayer
     */
    private $client;

    // Locally-created fields

    /**
     * A string or integer which is used as part of the unique payee identifier
     * @var string|int
     */
    private $payeeIdentifier;
    /**
     * A string or integer which is used as part of the unique invoice reference code
     * @var string|int
     */
    private $invoiceIdentifier;
    /**
     * @var array<string, string|int>
     */
    private $fields = [];
    /**
     * @var array<int, array{amount: float, description: ?string, customFields: array<string, string|int>}>
     */
    private $lines = [];

    // Tipalti-specified fields

    /**
     * @var DateTime|null
     */
    private $invoiceDueDate;
    /**
     * @var DateTime
     */
    private $invoiceDate;
    /**
     * @var string|null
     */
    private $subject;
    /**
     * @var string|null
     */
    private $description;
    /**
     * @var string
     */
    private $currency = "USD";
    /**
     * @var bool
     */
    private $canApprove = false;
    /**
     * @var bool
     */
    private $isPaidManually = false;

    /**
     * Tipalti fields we don't handle yet.
     * @todo Add support for invoice fields that are not yet supported.
     */
    private $internalInvoiceNotes;
    private $apAccountNumber;
    private $invoiceNumber;
    private $approvers;
    private $invoiceStatus;
    private $incomeType;
    private $ManualPaymentDetails;

    /**
     * @throws Exception
     */
    public function __construct(TipaltiPayer $client)
    {
        $this->client = $client;
    }

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $invoices = new ArrayOfTipaltiInvoiceItemRequest();
        $invoices[] = $this->getInvoiceItemRequest();
        $this->client->callCreateOrUpdateInvoices($invoices);
    }

    /**
     * @return TipaltiInvoiceItemRequest
     */
    public function getInvoiceItemRequest(): TipaltiInvoiceItemRequest
    {
        $invoice = new TipaltiInvoiceItemRequest($this->getInvoiceDate(), $this->isCanApprove(), $this->isPaidManually());
        $invoice->setIdap($this->getIdap());
        $invoice->setInvoiceRefCode($this->getRefcode());
        if ($this->getInvoiceDueDate() instanceof DateTime) {
            $invoice->setInvoiceDueDate($this->getInvoiceDueDate());
        }
        if (!empty($this->getDescription())) {
            $invoice->setDescription($this->getDescription());
        }
        if (!empty($this->getSubject())) {
            $invoice->setInvoiceSubject($this->getSubject());
        }
        if (!empty($this->client->getPayerEntityName())) {
            $invoice->setPayerEntityName($this->client->getPayerEntityName());
        }
        if (!empty($this->getCustomFields())) {
            $invoice->setCustomFields($this->getCustomFields());
        }
        $invoice->setInvoiceLines($this->getInvoiceLines());
        return $invoice;
    }

    /**
     * @return int|string
     */
    public function getPayeeIdentifier()
    {
        return $this->payeeIdentifier;
    }

    /**
     * @param int|string $payeeIdentifier
     */
    public function setPayeeIdentifier($payeeIdentifier): void
    {
        $this->payeeIdentifier = $payeeIdentifier;
    }

    /**
     * @return int|string
     */
    public function getInvoiceIdentifier()
    {
        return $this->invoiceIdentifier;
    }

    /**
     * @param int|string $invoiceIdentifier
     */
    public function setInvoiceIdentifier($invoiceIdentifier): void
    {
        $this->invoiceIdentifier = $invoiceIdentifier;
    }

    /**
     * @return string
     */
    public function getIdap(): string
    {
        return empty($this->client->getIdapPrefix()) ? $this->getPayeeIdentifier() : $this->client->getIdapPrefix() . $this->getPayeeIdentifier();
    }

    /**
     * @return string
     */
    public function getRefcode(): string
    {
        return empty($this->client->getRefcodePrefix()) ? $this->getInvoiceIdentifier() : $this->client->getRefcodePrefix() . $this->getInvoiceIdentifier();
    }

    /**
     * @return DateTime|null
     */
    public function getInvoiceDueDate(): ?DateTime
    {
        return $this->invoiceDueDate;
    }

    /**
     * @param DateTime|null $invoiceDueDate
     */
    public function setInvoiceDueDate(?DateTime $invoiceDueDate): void
    {
        $this->invoiceDueDate = $invoiceDueDate;
    }

    /**
     * @return DateTime
     */
    public function getInvoiceDate(): DateTime
    {
        return ($this->invoiceDate instanceof DateTime) ? $this->invoiceDate : new DateTime();
    }

    /**
     * @param DateTime $invoiceDate
     */
    public function setInvoiceDate(DateTime $invoiceDate): void
    {
        $this->invoiceDate = $invoiceDate;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return ArrayOfKeyValuePair|null
     */
    public function getCustomFields(): ?ArrayOfKeyValuePair
    {
        if (empty($this->fields)) {
            return null;
        }
        $arrayOfKeyValuePair = new ArrayOfKeyValuePair();
        foreach ($this->fields as $key=>$value) {
            $keyValuePair = new KeyValuePair();
            $keyValuePair->setKey($key);
            $keyValuePair->setValue($value);
            $arrayOfKeyValuePair[]=$keyValuePair;
        }
        return $arrayOfKeyValuePair;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return bool
     */
    public function isCanApprove(): bool
    {
        return $this->canApprove;
    }

    /**
     * @param bool $canApprove
     */
    public function setCanApprove(bool $canApprove): void
    {
        $this->canApprove = $canApprove;
    }

    /**
     * @return bool
     */
    public function isPaidManually(): bool
    {
        return $this->isPaidManually;
    }

    /**
     * @param bool $isPaidManually
     */
    public function setIsPaidManually(bool $isPaidManually): void
    {
        $this->isPaidManually = $isPaidManually;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @param string $key
     * @param string|int $value
     * @return void
     */
    public function setField(string $key, $value): void
    {
        $this->fields[$key] = $value;
    }

    /**
     * @return array
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @param array $lines
     */
    public function setLines(array $lines): void
    {
        $this->lines = [];
        foreach ($lines as $line) {
            $this->addLine(...$line);
        }
    }

    /**
     *
     * @todo Add support for more features of InvoiceLine such as custom currency per line, ewallet/banking message, tax related and GL account settings, etc.
     *
     * @param float $amount
     * @param string|null $description
     * @param array<string, string|int> $customFields
     * @return void
     */
    public function addLine(float $amount, ?string $description = null, array $customFields = []): void
    {
        $this->lines[] = ['amount'=>$amount, 'description'=>$description, 'customFields'=>$customFields];
    }

    /**
     * @todo Add proper support for tax calculation (see addLine())
     * @return ArrayOfInvoiceLine
     */
    public function getInvoiceLines(): ArrayOfInvoiceLine
    {
        $arrayOfInvoiceLines = new ArrayOfInvoiceLine();
        foreach ($this->getLines() as $line) {
            $invoiceLine = new InvoiceLine($line['amount'], 0, $line['amount']);
            $invoiceLine->setCurrency($this->getCurrency());
            if (!empty($line['description'])) {
                $invoiceLine->setDescription($line['description']);
            }
            if (!empty($line['customFields'])) {
                $customFields = new ArrayOfKeyValuePair();
                foreach ($line['customFields'] as $key => $value) {
                    $field = new KeyValuePair();
                    $field->setKey($key);
                    $field->setValue($value);
                    $customFields[] = $field;
                }
            }
        }
        return $arrayOfInvoiceLines;
    }
}
