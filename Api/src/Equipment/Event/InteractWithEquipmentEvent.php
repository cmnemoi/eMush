<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

class InteractWithEquipmentEvent extends EquipmentEvent
{
    protected EquipmentHolderInterface $author;

    public function __construct(
        GameEquipment            $equipment,
        EquipmentHolderInterface $author,
        string                   $visibility,
        array                    $tags,
        \DateTime                $time
    ) {
        parent::__construct($equipment, false, $visibility, $tags, $time);

        $this->author = $author;

        if ($author instanceof Player) {
            $this->player = $author;
        }
    }

    public function getAuthor(): EquipmentHolderInterface
    {
        return $this->author;
    }

    public function getLogParameters(): array
    {
        $logParameters = [];

        $logParameters['target_' . $this->getGameEquipment()->getLogKey()] = $this->getGameEquipment()->getLogName();

        if ($this->author instanceof Player) {
            $logParameters[$this->author->getLogKey()] = $this->author->getLogName();
        }

        return $logParameters;
    }
}
