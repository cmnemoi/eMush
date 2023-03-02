<?php

namespace Mush\Logger\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Mush\Logger\Entity\Log;

class MonologPostgresHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
 
    /**
     * Writes the (already formatted) record down to the log of the implementing handler.
     */
    protected function write(LogRecord $record): void
    {
        $log = new Log();
        $log->setLogRecord($record);

        if($this->entityManager->isOpen()){
            try{
                $this->entityManager->persist($log);
                $this->entityManager->flush();
            }catch(Exception)
            {

            }
            
        }
    }
}
