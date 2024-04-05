<?php

declare(strict_types=1);

namespace WebServCo\Mail\Service\Processor;

use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use Throwable;
use UnexpectedValueException;
use WebServCo\Command\Contract\ItemsProcessingReportInterface;
use WebServCo\Command\Service\AbstractItemsProcessingReportConsumer;
use WebServCo\Mail\Contract\Processor\MailingProcessorInterface;
use WebServCo\Mail\Contract\Service\Storage\MailItemEntityStorageInterface;
use WebServCo\Mail\Contract\Service\Storage\MailItemStorageInterface;
use WebServCo\Mail\DataTransfer\MailItem;
use WebServCo\Mail\Entity\MailItemEntity;

use function sleep;
use function sprintf;

final class MailingProcessor extends AbstractItemsProcessingReportConsumer implements MailingProcessorInterface
{
    public function __construct(
        ItemsProcessingReportInterface $itemsProcessingReportInterface,
        private LoggerInterface $logger,
        private MailItemEntityStorageInterface $mailItemEntityStorage,
        private MailItemStorageInterface $mailItemStorage,
        private int $pauseBetweenItemsProcessing,
        private PHPMailer $phpMailer,
    ) {
        parent::__construct($itemsProcessingReportInterface);
    }

    public function process(): bool
    {
        $this->logger->info(__METHOD__);

        // Iterate unsent items.
        foreach ($this->mailItemEntityStorage->iterateUnsentMailItemEntity() as $mailItemEntity) {
            if (!$mailItemEntity instanceof MailItemEntity) {
                throw new UnexpectedValueException('Item is not a valid object instance.');
            }

            // Process individual item.
            $this->processItem($mailItemEntity);

            // Pause if needed.
            if ($this->pauseBetweenItemsProcessing <= 0) {
                continue;
            }

            sleep($this->pauseBetweenItemsProcessing);
        }

        // Log result
        $this->logger->info(sprintf('Total items: %d', $this->getItemsProcessingReport()->getTotalItems()));
        $this->logger->info(sprintf('Total processed: %d', $this->getItemsProcessingReport()->getTotalProcessed()));

        return true;
    }

    private function processItem(MailItemEntity $mailItemEntity): bool
    {
        $this->logger->debug(sprintf('Process item id "%d".', $mailItemEntity->id));

        try {
            $this->mailItemStorage->clearError($mailItemEntity->id);

            $this->sendMail($mailItemEntity->mailItem);

            $this->mailItemStorage->setSent($mailItemEntity->id);

            $this->getItemsProcessingReport()->incrementTotalProcessed();

            $this->logger->debug(sprintf('Sent mail for item id "%d".', $mailItemEntity->id));
        } catch (Throwable $throwable) {
            $this->logger->error($throwable, [$throwable]);
            $this->mailItemStorage->setError($mailItemEntity->id, $throwable->getMessage());
        } finally {
            $this->resetMailer();
            $this->getItemsProcessingReport()->incrementTotalItems();
        }

        return true;
    }

    private function resetMailer(): bool
    {
        // `clearAllRecipients` removes: TO, CC, BCC
        // OK here since each mail can have different CC/BCC
        $this->phpMailer->clearAllRecipients();

        return true;
    }

    private function sendMail(MailItem $mailItem): bool
    {
        $this->phpMailer->addAddress($mailItem->to);
        if ($mailItem->cc !== null) {
            $this->phpMailer->addCC($mailItem->cc);
        }
        if ($mailItem->bcc !== null) {
            $this->phpMailer->addBCC($mailItem->bcc);
        }
        $this->phpMailer->Subject = $mailItem->subject;
        $this->phpMailer->isHTML();
        $this->phpMailer->msgHTML($mailItem->message);

        return $this->phpMailer->send();
    }
}
