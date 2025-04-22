<?php

declare(strict_types=1);

namespace WebServCo\Mail\Service\Processor;

use Override;
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

use function preg_split;
use function sleep;
use function sprintf;
use function trim;

use const PREG_SPLIT_NO_EMPTY;

final class MailingProcessor extends AbstractItemsProcessingReportConsumer implements MailingProcessorInterface
{
    private const string ADDRESS_SPLIT_PATTERN = '/(\s*,*\s*)*,+(\s*,*\s*)*/';

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

    #[Override]
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

    private function handleMailBcc(MailItem $mailItem): bool
    {
        $mailItemBcc = $mailItem->bcc;
        if ($mailItemBcc === null) {
            return false;
        }

        $bccItems = preg_split(self::ADDRESS_SPLIT_PATTERN, $mailItemBcc, -1, PREG_SPLIT_NO_EMPTY);

        if ($bccItems === false) {
            throw new UnexpectedValueException('Invalid cc');
        }

        foreach ($bccItems as $bccItem) {
            $this->phpMailer->addBCC(trim($bccItem));
        }

        return true;
    }

    private function handleMailCc(MailItem $mailItem): bool
    {
        $mailItemCc = $mailItem->cc;
        if ($mailItemCc === null) {
            return false;
        }

        $ccItems = preg_split(self::ADDRESS_SPLIT_PATTERN, $mailItemCc, -1, PREG_SPLIT_NO_EMPTY);

        if ($ccItems === false) {
            throw new UnexpectedValueException('Invalid cc');
        }

        foreach ($ccItems as $ccItem) {
            $this->phpMailer->addCC(trim($ccItem));
        }

        return true;
    }

    private function handleMailSending(MailItem $mailItem): bool
    {
        // No multi support (bad practice, recipients should not see each other's addresses).
        $this->phpMailer->addAddress($mailItem->to);

        $this->handleMailCc($mailItem);

        $this->handleMailBcc($mailItem);

        $this->phpMailer->Subject = $mailItem->subject;
        $this->phpMailer->isHTML();
        $this->phpMailer->msgHTML($mailItem->message);

        return $this->phpMailer->send();
    }

    private function processItem(MailItemEntity $mailItemEntity): bool
    {
        $this->logger->debug(sprintf('Process item id "%d".', $mailItemEntity->id));

        try {
            $this->mailItemStorage->clearError($mailItemEntity->id);

            $this->handleMailSending($mailItemEntity->mailItem);

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
}
