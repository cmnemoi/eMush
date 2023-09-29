<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AreLateralReactorsBroken;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\OrientationHasChanged;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusOrientationEnum;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ChangeDaedalusOrientation extends AbstractAction
{
    protected string $name = ActionEnum::CHANGE_DAEDALUS_ORIENTATION;
    private DaedalusServiceInterface $daedalusService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        DaedalusServiceInterface $daedalusService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );
        $this->daedalusService = $daedalusService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'contain' => true,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
        $metadata->addConstraint(new OrientationHasChanged([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::NEED_TO_CHANGE_ORIENTATION,
        ]));
        $metadata->addConstraint(new AreLateralReactorsBroken([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::LATERAL_REACTORS_ARE_BROKEN,
        ]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        $targetIsValid = $target instanceof GameEquipment;
        $orientationIsValid = in_array($parameters['orientation'], DaedalusOrientationEnum::getAll(), strict: true);

        return $targetIsValid && $orientationIsValid;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($this->parameters === null) {
            throw new \InvalidArgumentException("Parameters should be set for action {$this->name}");
        }

        $chosenOrientation = $this->parameters['orientation'];
        if ($chosenOrientation === null) {
            throw new \InvalidArgumentException("Orientation parameter should be set for action {$this->name}");
        }

        $daedalus = $this->player->getDaedalus();
        $currentOrientation = $daedalus->getOrientation();

        if ($result instanceof Success) {
            if (
                $currentOrientation !== null
                && $chosenOrientation === DaedalusOrientationEnum::getOppositeOrientation($currentOrientation)
            ) {
                $this->action->setActionCost($this->action->getActionCost() + 1);
            }

            /** @var false|GameEquipment $alphaLateralReactor */
            $alphaLateralReactor = $this->gameEquipmentService->findByNameAndDaedalus(
                EquipmentEnum::REACTOR_LATERAL_ALPHA,
                $daedalus
            )->first();
            /** @var false|GameEquipment $bravoLateralReactor */
            $bravoLateralReactor = $this->gameEquipmentService->findByNameAndDaedalus(
                EquipmentEnum::REACTOR_LATERAL_BRAVO,
                $daedalus
            )->first();

            if (
                $currentOrientation !== null
                && $chosenOrientation === DaedalusOrientationEnum::getClockwiseOrientation($currentOrientation)
                && $alphaLateralReactor instanceof GameEquipment && !$alphaLateralReactor->isOperational()
            ) {
                $this->action->setActionCost($this->action->getActionCost() + 1);
            }

            if (
                $currentOrientation !== null
                && $chosenOrientation === DaedalusOrientationEnum::getCounterClockwiseOrientation($currentOrientation)
                && $bravoLateralReactor instanceof GameEquipment && !$bravoLateralReactor->isOperational()
            ) {
                $this->action->setActionCost($this->action->getActionCost() + 1);
            }

            $daedalus->setOrientation($chosenOrientation);
            $this->daedalusService->persist($daedalus);
        }
    }
}
