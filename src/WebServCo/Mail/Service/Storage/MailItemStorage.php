<?php

declare(strict_types=1);

namespace WebServCo\Mail\Service\Storage;

use WebServCo\Database\Contract\PDOContainerInterface;
use WebServCo\Mail\Contract\Service\Storage\MailItemStorageInterface;
use WebServCo\Mail\DataTransfer\MailItem;

use function sprintf;

final class MailItemStorage implements MailItemStorageInterface
{
    public function __construct(private PDOContainerInterface $pdoContainer, private string $tableName)
    {
    }

    public function clearError(int $id): bool
    {
        $stmt = $this->pdoContainer->getPDOService()->prepareStatement(
            $this->pdoContainer->getPDO(),
            sprintf(
                'UPDATE %s SET error_message = NULL, when_error = NULL WHERE id = ? LIMIT 1',
                $this->tableName,
            ),
        );

        return $stmt->execute([$id]);
    }

    public function setError(int $id, string $errorMessage): bool
    {
        $stmt = $this->pdoContainer->getPDOService()->prepareStatement(
            $this->pdoContainer->getPDO(),
            sprintf(
                'UPDATE %s SET error_message = ?, when_error = NOW() WHERE id = ? LIMIT 1',
                $this->tableName,
            ),
        );

        return $stmt->execute([$errorMessage, $id]);
    }

    public function setSent(int $id): bool
    {
        $stmt = $this->pdoContainer->getPDOService()->prepareStatement(
            $this->pdoContainer->getPDO(),
            sprintf(
                'UPDATE %s SET when_sent = NOW() WHERE id = ? LIMIT 1',
                $this->tableName,
            ),
        );

        return $stmt->execute([$id]);
    }

    public function storeMailItem(MailItem $mailItem): int
    {
        $stmt = $this->pdoContainer->getPDOService()->prepareStatement(
            $this->pdoContainer->getPDO(),
            sprintf(
                'INSERT INTO %s 
                (mail_subject, mail_message, mail_to, mail_cc, mail_bcc) 
                VALUES (?, ?, ?, ?, ?)',
                $this->tableName,
            ),
        );
        $stmt->execute(
            [
                $mailItem->subject,
                $mailItem->message,
                $mailItem->to,
                $mailItem->cc,
                $mailItem->bcc,
            ],
        );

        return (int) $this->pdoContainer->getPDOService()->getLastInsertId($this->pdoContainer->getPDO());
    }
}
