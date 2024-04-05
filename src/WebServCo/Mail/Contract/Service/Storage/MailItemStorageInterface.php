<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Service\Storage;

use WebServCo\Mail\DataTransfer\MailItem;

interface MailItemStorageInterface
{
    /**
     * Clear error information for mail item.
     */
    public function clearError(int $id): bool;

    /**
     * Set error information for mail item.
     */
    public function setError(int $id, string $errorMessage): bool;

    public function setSent(int $id): bool;

    public function storeMailItem(MailItem $mailItem): int;
}
