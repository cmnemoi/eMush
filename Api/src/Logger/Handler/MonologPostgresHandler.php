<?php

namespace Mush\Logger\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Mush\Logger\Entity\Log;

class MonologPostgresHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $entityManager;
    private bool $loop;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->loop = false;
    }

    /**
     * Writes the (already formatted) record down to the log of the implementing handler.
     */
    protected function write(LogRecord $record): void
    {
        $log = new Log();
        $log->setLogRecord($record);

        // exception on persist can cause an infinite loop here
        if ($this->loop) {
            return;
        }
        $this->loop = true;

        if ($this->entityManager->isOpen()) {
            try {
                $this->entityManager->persist($log);
                $this->entityManager->flush();
            } catch (\Exception) {
            } finally {
                $this->loop = false;
            }
        }
    }
}
