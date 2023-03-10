<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Hit extends AttemptAction
{
    protected string $name = ActionEnum::HIT;
    private const MIN_DAMAGE = 1;
    private const MAX_DAMAGE = 3;
    private EventModifierServiceInterface $modifierService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        EventModifierServiceInterface $modifierService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService,
        );
        $this->modifierService = $modifierService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $target */
        $target = $this->parameter;

        $damage = 0;

        if ($result instanceof Success) {
            $damage = $this->getDamage(withModifiersOnTarget: true);
        } elseif ($result instanceof CriticalSuccess) {
            $damage = $this->getDamage();
        }

        $this->inflictDamageToTarget($damage, $target);
    }

    private function applyPlayerModifiersOnDamage(Player $player, int $damage): int
    {
        $damage = $this->modifierService->getEventModifiedValue(
            holder: $player,
            scopes: [ModifierScopeEnum::INJURY],
            target: PlayerVariableEnum::HEALTH_POINT,
            initValue: $damage,
            reasons: $this->getAction()->getActionTags(),
            time: new \DateTime(),
        );

        return $damage;
    }

    private function getDamage(bool $withModifiersOnTarget = false): int
    {
        /** @var Player $agressor */
        $agressor = $this->player;
        /** @var Player $target */
        $target = $this->parameter;

        $damage = $this->randomService->random(self::MIN_DAMAGE, self::MAX_DAMAGE);
        $damage = $this->applyPlayerModifiersOnDamage($agressor, $damage);
        if ($withModifiersOnTarget) {
            $damage = $this->applyPlayerModifiersOnDamage($target, $damage);
        }

        return $damage;
    }

    private function inflictDamageToTarget(int $damage, Player $target): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getAction()->getActionTags(),
            new \DateTime()
        );

        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
