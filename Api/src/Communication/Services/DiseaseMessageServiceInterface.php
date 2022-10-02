<?php

namespace Mush\Communication\Services;

use Mush\Communication\Entity\Message;

interface DiseaseMessageServiceInterface
{
    public function applyDiseaseEffects(Message $message): Message;
}
