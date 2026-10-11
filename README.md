# webservco/mail

A PHP component/library. Please see [webservco/sandbox](https://webservco.github.io/sandbox/) for an example project using these types of components.

Saves outgoing emails in a database table and sends them later from a scheduled job using PHPMailer.

- Requires: the `mailing` table ([structure](resources/storage/structure/mailing.sql)), a PDO container from `webservco/database`
- Related: `webservco/command` (the sending job runs as a command and reports what it processed)

---

## Installation

```shell
composer require webservco/mail
```

---

## Usage

### Add an email to the queue

```php
use WebServCo\Mail\DataTransfer\MailItem;
use WebServCo\Mail\Service\MailingTableNameServiceFromConfig;
use WebServCo\Mail\Service\Storage\MailItemStorage;

// \WebServCo\Mail\Contract\Service\MailingTableNameServiceInterface
$tableNameService = new MailingTableNameServiceFromConfig($configurationGetter);
// \WebServCo\Mail\Contract\Service\Storage\MailItemStorageInterface
$mailItemStorage = new MailItemStorage($tableNameService, $pdoContainer);

$mailItemId = $mailItemStorage->storeMailItem(
    // subject, HTML message, to, cc, bcc
    new MailItem('Your order', '<p>Thank you.</p>', 'client@example.com', null, 'shop@example.com'),
);
```

### Send the queue

Run this from a command on a schedule (cron).

```php
// \WebServCo\Mail\Contract\Factory\MailingProcessorFactoryInterface (your factory)
$mailingProcessor = $mailingProcessorFactory->createMailingProcessor($tableNameService);
$mailingProcessor->process();

// \WebServCo\Command\Contract\ItemsProcessingReportInterface
$report = $mailingProcessor->getItemsProcessingReport();
$output->write(sprintf('Sent %d of %d.', $report->getTotalProcessed(), $report->getTotalItems()));
```

Your factory creates `Service\Processor\MailingProcessor` with a report, a logger, `MailItemEntityStorage`, `MailItemStorage`, a pause in seconds between emails, and a configured `PHPMailer` (SMTP settings and sender).

---

## Contracts

| Interface | Implementation | Purpose |
| --- | --- | --- |
| `Contract\Service\Storage\MailItemStorageInterface` | `Service\Storage\MailItemStorage` | Adds emails and marks them as sent or failed. |
| `Contract\Service\Storage\MailItemEntityStorageInterface` | `Service\Storage\MailItemEntityStorage` | Iterates emails that haven't been sent. |
| `Contract\Processor\MailingProcessorInterface` | `Service\Processor\MailingProcessor` | Sends each unsent email and counts the results. |
| `Contract\Service\MailingTableNameServiceInterface` | `Service\MailingTableNameServiceFromConfig` | Table name, from the configuration. |
| `Contract\Factory\MailingProcessorFactoryInterface` | your factory | Creates the processor. |
| `Contract\Factory\PHPMailerFactoryInterface` | your factory | Creates a configured `PHPMailer`. |

Namespaces are relative to `WebServCo\Mail\`.

---

## Configuration

The table can have any name.

```ini
[Mail]

MAILING_TABLE_NAME = mailing
```

---

## Notes

Messages are sent as HTML. `to` takes exactly one address, while `cc` and `bcc` take several, separated by commas.

When sending fails, the error goes into `error_message` and the email stays unsent. The next run tries it again, and the one after that, with no limit. Check the error column now and then for addresses that will never work.

The processor clears PHPMailer's recipients after each email.

---

## Development

```shell
# Start project.
ddev start
# Install dependencies.
ddev c u
# Run all validations.
ddev c c
# Run unit tests.
ddev c test:dox
```

---

## License

[MIT](LICENSE.md)
