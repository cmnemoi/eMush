<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CheckSporeLevel extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHECK_SPORE_LEVEL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        $player = $this->player;

        if ($player->isMush()) {
            $nbSpores = 0;
        } else {
            $nbSpores = $player->getVariableValueByName(PlayerVariableEnum::SPORE);
        }

        $success = new Success();

        return $success->setQuantity($nbSpores);
    }

    protected function applyEffect(ActionResult $result): void {}
}
