<?php

namespace Mush\Logger\Entity;

use Doctrine\ORM\Mapping as ORM;
use Monolog\LogRecord;

#[ORM\Entity]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'json', options: ['jsonb' => false])]
    private LogRecord $logRecord;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogRecord(): LogRecord
    {
        return $this->logRecord;
    }

    public function setLogRecord(LogRecord $logRecord): void
    {
        $this->logRecord = $logRecord;
    }
}
