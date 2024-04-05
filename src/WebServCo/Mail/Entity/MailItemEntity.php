<?php

declare(strict_types=1);

namespace WebServCo\Mail\Entity;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;
use WebServCo\Mail\DataTransfer\MailItem;

final readonly class MailItemEntity implements DataTransferInterface
{
    public function __construct(public int $id, public MailItem $mailItem)
    {
    }
}
