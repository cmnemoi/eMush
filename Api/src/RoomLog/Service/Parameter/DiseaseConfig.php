<?php

namespace Mush\RoomLog\Service\Parameter;

class DiseaseConfig extends AbstractHandler implements HandlerInterface
{
    public function canProcess(): bool
    {
        return $this->data->getDiseaseConfig() !== null;
    }

    public function getName(): string
    {
        return $this->data->getDiseaseConfig()->getLogKey();
    }

    public function execute()
    {
        return $this->data->getDiseaseConfig()->getLogName();
    }
}