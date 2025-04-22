<?php

declare(strict_types=1);

namespace WebServCo\Mail\Service;

use Override;
use WebServCo\Configuration\Contract\ConfigurationGetterInterface;
use WebServCo\Mail\Contract\Service\MailingTableNameServiceInterface;

/**
 * A default service using configuration data.
 */
final class MailingTableNameServiceFromConfig implements MailingTableNameServiceInterface
{
    public function __construct(
        private ConfigurationGetterInterface $configurationGetter,
        private string $configurationKey = 'MAILING_TABLE_NAME',
    ) {
    }

    #[Override]
    public function getTableName(): string
    {
        return $this->configurationGetter->getString($this->configurationKey);
    }
}
