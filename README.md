# 🌶 Chipotle, the tasty way to use Tipalti with PHP

Chipotle makes the spicy, SOAP-flavored Tipalti API and seasons it with modern PHP. Now, you can perform common Tipalti functions from PHP 7.3+ without having to deal with SOAP.

# Setup
This package requires PHP 7.3+ and the SOAP extension (which may not be installed by default in your PHP installation). Install it in your project with:
```shell
composer require nextnetmedia/tipalti-chipotle
```

# Sending a Single Invoice

```injectablephp
use Nextnetmedia\Chipotle\TipaltiPayer;
use Nextnetmedia\Chipotle\NewInvoice;

// Initialize a TipaltiPayer client with your sandbox API key, your payer name, your payer entity name (if used), and an automatic prefix to append to IDAP and refcodes (which can be helpful when using Tipalti for multiple brands or businesses/payer entities that could have conflicting names or ID's)
$payer = new TipaltiPayer('your-api-key', 'YourPayerName', false, 'YourPayerEntityName', 'TEST-', 'TESTBILL-');

// Create a new invoice using your TipaltiPayer instance
$invoice = new NewInvoice($payer);

// Set the identifier which will be used as the IDAP (will have the the prefix specified for the payer prepended automatically, if supplied)
$invoice->setPayeeIdentifier('187'); // User ID 187 from your local system

// Set the invoice reference ID which will be used as the refcode (will have the prefix supplied for the payer prepended to it automatically if you supplied one)
$invoice->setInvoiceIdentifier('9001'); // Invoice ID 9001 from your local system

// Now, we add lines to the invoice
$invoice->addLine(9.81, "Payment for services this week", ['externalid'=>10001]); // Here, we add an invoice line for $9.81 with a description of "payment for services this week", and the custom field (must be defined in Tipalti first) externalid set to 10001
$invoice->addLine(18.31, "What I owe you from last week");

// We can also add custom fields on the invoice itself
$invoice->setField('relatedcustomer', 813114);
// or 
$invoice->setFields(['relatedcustomer'=>813114, 'relatedworkorder'=>83123]);

// Now, our invoice is ready to send!
$invoice->send();
```

# Getting the Payee iFrames
```injectablephp
use Nextnetmedia\Chipotle\TipaltiPayer;
use Nextnetmedia\Chipotle\iFrame;

// Initialize a TipaltiPayer client with your sandbox API key, your payer name, your payer entity name (if used), and an automatic prefix to append to IDAP and refcodes (which can be helpful when using Tipalti for multiple brands or businesses/payer entities that could have conflicting names or ID's)
$payer = new TipaltiPayer('your-api-key', 'YourPayerName', false, 'YourPayerEntityName', 'TEST-', 'TESTBILL-');

$iframe = new iFrame($payer);
echo $iframe->getPayeeHome('187'); // returns the onboarding and payout/tax settings iFrame for user 187 from your local system
echo $iframe->getPayeePaymentHistory('187'); // returns the payment history iFrame for user 187 from your local system
echo $iframe->getPayeeInvoiceHistory('187'); // returns the invoice history iFrame for user 187 from your local system
```
