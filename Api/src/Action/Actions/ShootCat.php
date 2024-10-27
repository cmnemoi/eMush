<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalFail;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\OneShot;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Special implementation of the Shoot action that targets equipments instead of players, for the purpose of shooting at Schrodinger.
 */
class ShootCat extends AttemptAction
{
    private const string CAT_DEATH_TAG = 'cat_death';
    protected ActionEnum $name = ActionEnum::SHOOT_CAT;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    private DiseaseCauseServiceInterface $diseaseCauseService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService,
        );

        $this->diseaseCauseService = $diseaseCauseService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem && $target->isSchrodinger();
    }

    // Special checkResult for Shoot action waiting for a refactor
    protected function checkResult(): ActionResult
    {
        $weapon = $this->getPlayerWeapon();

        $success = $this->randomService->isSuccessful($this->getSuccessRate());

        if ($success) {
            // @TODO Only difference with regular successes is a different log line, since Schrodinger dies in a single successful hit.
            /*if ($this->rollCriticalChances($weapon->getOneShotRate())) {
                return new OneShot();
            }
            if ($this->rollCriticalChances($weapon->getCriticalSuccessRate())) {
                return new CriticalSuccess();
            }*/

            return new Success();
        }
        if ($this->rollCriticalChances($weapon->getCriticalFailRate())) {
            return new CriticalFail();
        }

        return new Fail();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $player = $this->player;

        if ($result instanceof Success) {
            // @TODO
            /*if ($result instanceof OneShot) {
                $reasons = $this->getTags();
                $reasons[] = ActionOutputEnum::ONE_SHOT;
                $this->killCat();

                return;
            }
            if ($result instanceof CriticalSuccess) {
                $reasons = $this->getTags();
                $reasons[] = ActionOutputEnum::CRITICAL_SUCCESS;
                $this->killCat();

                return;
            }*/

            $this->killCat();
        } else {
            if ($result instanceof CriticalFail) {
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_FAIL_BLASTER, $player);
            }
        }
    }

    private function getPlayerWeapon(): Weapon
    {
        /** @var GameItem $weaponItem */
        $weaponItem = $this->actionProvider;

        return $weaponItem->getWeaponMechanicOrThrow();
    }

    private function rollCriticalChances(int $percentage): bool
    {
        $criticalRollEvent = new ActionVariableEvent(
            actionConfig: $this->actionConfig,
            actionProvider: $this->actionProvider,
            variableName: ActionVariableEnum::PERCENTAGE_CRITICAL,
            quantity: $percentage,
            player: $this->player,
            tags: $this->getTags(),
            actionTarget: $this->target
        );

        /** @var ActionVariableEvent $criticalRollEvent */
        $criticalRollEvent = $this->eventService->computeEventModifications($criticalRollEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);

        return $this->randomService->isSuccessful($criticalRollEvent->getRoundedQuantity());
    }

    private function killCat(): void
    {
        $interactEvent = new InteractWithEquipmentEvent(
            $this->gameItemTarget(),
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getTags(),
            new \DateTime(),
        );
        $interactEvent->addTag(self::CAT_DEATH_TAG);

        $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
