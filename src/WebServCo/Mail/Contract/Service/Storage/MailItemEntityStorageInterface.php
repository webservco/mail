<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Service\Storage;

use Generator;

interface MailItemEntityStorageInterface
{
    /**
     * @return \Generator<\WebServCo\Mail\Entity\MailItemEntity>
     */
    public function iterateUnsentMailItemEntity(): Generator;
}
