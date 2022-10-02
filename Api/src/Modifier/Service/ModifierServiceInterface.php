<?php

namespace Mush\Modifier\Service;

use Mush\Action\Entity\Action;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ModifierServiceInterface
{
    public function persist(Modifier $modifier): Modifier;

    public function delete(Modifier $modifier): void;

    public function getHolderFromConfig(
        ModifierConfig $config,
        ModifierHolder $holder,
        ModifierHolder $target = null
    ) : ModifierHolder;

    public function createModifier(ModifierConfig $config, ModifierHolder $holder) : Modifier;

    public function deleteModifier(ModifierConfig $modifierConfig, ModifierHolder $holder) : void;

    public function isSuccessfulWithModifier(
        ModifierHolder $holder,
        int $baseSuccessRate,
        array $reasons,
        bool $tryToSucceed = true
    ) : bool;

}
