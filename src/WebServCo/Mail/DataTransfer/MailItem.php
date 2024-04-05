<?php

declare(strict_types=1);

namespace WebServCo\Mail\DataTransfer;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class MailItem implements DataTransferInterface
{
    public function __construct(
        public string $subject,
        public string $message,
        public string $to,
        public ?string $cc,
        public ?string $bcc,
    ) {
    }
}
