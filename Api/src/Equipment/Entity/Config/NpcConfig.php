<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\Npc;
use Mush\Equipment\Enum\AIHandlerEnum;

#[ORM\Entity]
class NpcConfig extends ItemConfig
{
    #[ORM\Column(type: 'string', enumType: AIHandlerEnum::class, nullable: false, options: ['default' => AIHandlerEnum::NOTHING])]
    private AIHandlerEnum $aiHandler = AIHandlerEnum::NOTHING;

    public function createGameEquipment(
        EquipmentHolderInterface $holder,
    ): Npc {
        $gameItem = new Npc($holder);
        $gameItem
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this);

        return $gameItem;
    }

    public function getAiHandler(): AIHandlerEnum
    {
        return $this->aiHandler;
    }

    public function setAiHandler(AIHandlerEnum $aiHandler): self
    {
        $this->aiHandler = $aiHandler;

        return $this;
    }
}
