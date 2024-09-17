<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Factory;

use WebServCo\Mail\Contract\Processor\MailingProcessorInterface;

interface MailingProcessorFactoryInterface
{
    public function createMailingProcessor(): MailingProcessorInterface;
}
