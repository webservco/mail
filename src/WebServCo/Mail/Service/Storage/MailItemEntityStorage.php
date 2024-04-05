<?php

declare(strict_types=1);

namespace WebServCo\Mail\Service\Storage;

use Generator;
use OutOfRangeException;
use PDOStatement;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;
use WebServCo\Database\Contract\PDOContainerInterface;
use WebServCo\Mail\Contract\Service\Storage\MailItemEntityStorageInterface;
use WebServCo\Mail\DataTransfer\MailItem;
use WebServCo\Mail\Entity\MailItemEntity;

final class MailItemEntityStorage implements MailItemEntityStorageInterface
{
    public function __construct(
        private DataExtractionContainerInterface $dataExtractionContainer,
        private PDOContainerInterface $pdoContainer,
    ) {
    }

    /**
     * @return \Generator<\WebServCo\Mail\Entity\MailItemEntity>
     */
    public function iterateUnsentMailItemEntity(): Generator
    {
        $stmt = $this->getUnsentMailItemEntityStatement();

        $stmt->execute([]);

        while ($row = $this->pdoContainer->getPDOService()->fetchAssoc($stmt)) {
            yield $this->hydrateMailItemEntity($row);
        }
    }

    private function getUnsentMailItemEntityStatement(): PDOStatement
    {
        return $this->pdoContainer->getPDOService()->prepareStatement(
            $this->pdoContainer->getPDO(),
            'SELECT id, mail_to, mail_cc, mail_bcc, mail_subject, mail_message
            FROM mailing WHERE when_sent IS NULL',
        );
    }

    /**
     * @param array<string,scalar|null> $data
     */
    private function hydrateMailItemEntity(array $data): MailItemEntity
    {
        if ($data === []) {
            throw new OutOfRangeException('Data is empty.');
        }

        return new MailItemEntity(
            $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService()
                ->getNonEmptyInt($data, 'id'),
            new MailItem(
                $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService()
                    ->getNonEmptyString($data, 'mail_subject'),
                $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService()
                    ->getNonEmptyString($data, 'mail_message'),
                $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService()
                    ->getNonEmptyString($data, 'mail_to'),
                $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService()
                    ->getNonEmptyNullableString($data, 'mail_cc'),
                $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService()
                ->getNonEmptyNullableString($data, 'mail_bcc'),
            ),
        );
    }
}
