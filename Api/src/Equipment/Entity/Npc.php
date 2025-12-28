<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\NpcConfig;
use Mush\Equipment\Enum\AIHandlerEnum;

#[ORM\Entity]
class Npc extends GameItem
{
    public function getAiHandler(): AIHandlerEnum
    {
        $config = $this->getEquipment();
        if ($config instanceof NpcConfig) {
            return $config->getAiHandler();
        }

        throw new \RuntimeException("NPC {$this->getName()} should have an NPC Config");
    }
}
