<?php

namespace Mush\Communication\Services;

use Mush\Communication\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

interface NeronMessageServiceInterface
{
    public function createNeronMessage(string $messageKey, Daedalus $daedalus, array $parameters, \DateTime $dateTime, Message $parent = null): Message;

    public function createPlayerDeathMessage(Player $player, string $cause, \DateTime $time): void;

    public function createBrokenEquipmentMessage(GameEquipment $equipment, string $visibility, \DateTime $time): void;

    public function createNewFireMessage(Daedalus $daedalus, \DateTime $time): void;

    public function getMessageNeronCycleFailures(Daedalus $daedalus, \DateTime $time): Message;

    public function createTitleAttributionMessage(Player $player, string $title, \DateTime $time): void;
}
