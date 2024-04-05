<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Processor;

use WebServCo\Command\Contract\ItemsProcessingReportConsumerInterface;

interface MailingProcessorInterface extends ItemsProcessingReportConsumerInterface
{
    public function process(): bool;
}
