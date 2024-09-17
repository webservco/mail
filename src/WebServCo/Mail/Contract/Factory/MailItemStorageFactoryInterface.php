<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Factory;

use WebServCo\Mail\Contract\Service\Storage\MailItemStorageInterface;

interface MailItemStorageFactoryInterface
{
    public function createMailItemStorage(): MailItemStorageInterface;
}
