<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalFail;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\OneShot;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Event\EnhancePercentageRollEvent;
use Mush\Game\Event\PreparePercentageRollEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the attack Action.
 */
class Attack extends AttemptAction
{
    protected string $name = ActionEnum::ATTACK;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    protected RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );

        $this->playerDiseaseService = $playerDiseaseService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::KNIFE],
            'contains' => true,
            'checkIfOperational' => true,
            'target' => HasEquipment::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {
        $player = $this->player;

        $knifeItem = $this->getPlayerKnife();
        if ($knifeItem == null) {
            throw new \Exception("Attack action : {$player->getLogName()} should have a knife");
        }

        /** @var Weapon $knifeWeapon */
        $knifeWeapon = $knifeItem->getMechanics()->first();

        if (!$knifeWeapon instanceof Weapon) {
            throw new \Exception('Attack action : Knife should have a weapon mechanic');
        }

        $result = parent::checkResult();

        if ($result instanceof Success) {
            if ($this->isOneShot($knifeWeapon)) {
                return new OneShot();
            }

            if ($this->isCriticalSuccess($knifeWeapon)) {
                return new CriticalSuccess();
            }

            return new Success();
        } else {
            if ($this->isCriticalFail($knifeWeapon)) {
                return new CriticalFail();
            }

            return new Fail();
        }
    }

    protected function applyEffect(ActionResult $result): void
    {
        $player = $this->player;
        /** @var Player $target */
        $target = $this->parameter;

        $knifeItem = $this->getPlayerKnife();
        if ($knifeItem == null) {
            throw new \Exception("Attack action : {$player->getLogName()} should have a knife");
        }

        /** @var Weapon $knifeWeapon */
        $knifeWeapon = $knifeItem->getMechanics()->first();
        if (!$knifeWeapon instanceof Weapon) {
            throw new \Exception('Attack action : Knife should have a weapon mechanic');
        }

        if ($result instanceof Success) {
            if ($result instanceof OneShot) {
                $deathEvent = new PlayerEvent(
                    $target,
                    EndCauseEnum::BLED,
                    new \DateTime()
                );

                $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

                return;
            }

            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($knifeWeapon->getBaseDamageRange()));

            if ($result instanceof CriticalSuccess) {
                $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_SUCCESS_KNIFE, $target);
                $this->inflictDamage($damage, $target, true);
            } else {
                $this->inflictDamage($damage, $target);
            }
        } else {
            if ($result instanceof CriticalFail) {
                $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_FAIL_KNIFE, $player);
            }
        }
    }

    private function getPlayerKnife(): ?EquipmentConfig
    {
        return $this->player->getEquipments()->filter(
            fn (GameItem $gameItem) => $gameItem->getName() === ItemEnum::KNIFE && $gameItem->isOperational()
        )->first()->getEquipment();
    }

    private function isCriticalFail(Weapon $knife): bool
    {
        return $this->isTriggered(ActionOutputEnum::CRITICAL_SUCCESS, $knife->getCriticalFailRate());
    }

    private function isCriticalSuccess(Weapon $knife): bool
    {
        return $this->isTriggered(ActionOutputEnum::CRITICAL_SUCCESS, $knife->getCriticalSuccessRate());
    }

    private function isOneShot(Weapon $knife): bool
    {
        return $this->isTriggered(ActionOutputEnum::ONE_SHOT, $knife->getOneShotRate());
    }

    private function isTriggered(string $output, int $rate): bool
    {
        $event = new PreparePercentageRollEvent(
            $this->player,
            $rate,
            $this->getActionName(),
            new \DateTime()
        );
        $event->addReason($output);
        $this->eventService->callEvent($event, PreparePercentageRollEvent::ACTION_ROLL_RATE);

        $threshold = $this->randomService->getSuccessThreshold();

        if ($event->getRate() > $threshold) {
            return true;
        }

        $enhanceEvent = new EnhancePercentageRollEvent(
            $this->player,
            $event->getRate(),
            $threshold,
            true,
            $this->getActionName(),
            new \DateTime()
        );
        $enhanceEvent->addReason($output);
        $this->eventService->callEvent($enhanceEvent, EnhancePercentageRollEvent::ACTION_ROLL_RATE);

        return $enhanceEvent->getRate() > $enhanceEvent->getThresholdRate();
    }

    private function inflictDamage(int $damage, Player $target, $trueDamage = false): void
    {
        $damageEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getActionName(),
            new \DateTime(),
            $trueDamage
        );

        $this->eventService->callEvent($damageEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
