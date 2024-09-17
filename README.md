# webservco/mail

A PHP component/library for async email sending.

---

## Setup

- [Create table](resources/storage/structure/mailing.sql)
  - Can use custom name if needed.

## Usage

### Adding email to sending queue

1. Create a `\WebServCo\Mail\DataTransfer\MailItem` DTO.

2. Store it: `WebServCo\Mail\Contract\Service\Storage\MailItemStorageInterface`.`storeMailItem(MailItem $mailItem): int`.

### Sending emails

- Use a scheduled job;
- Process: `MailingProcessorInterface`.`process()`;
- Get report: `MailingProcessorInterface`.`getItemsProcessingReport()`;

---

## TODO

- [ ] Add support for attachments
