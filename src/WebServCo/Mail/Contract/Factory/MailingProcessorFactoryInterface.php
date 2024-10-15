<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Factory;

use WebServCo\Mail\Contract\Processor\MailingProcessorInterface;
use WebServCo\Mail\Contract\Service\MailingTableNameServiceInterface;

interface MailingProcessorFactoryInterface
{
    public function createMailingProcessor(
        MailingTableNameServiceInterface $tableNameService,
    ): MailingProcessorInterface;
}
