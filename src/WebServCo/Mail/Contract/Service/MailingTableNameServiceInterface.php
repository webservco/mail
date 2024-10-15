<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Service;

interface MailingTableNameServiceInterface
{
    public function getTableName(): string;
}
