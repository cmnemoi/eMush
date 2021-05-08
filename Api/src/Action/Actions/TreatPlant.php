<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TreatPlant extends AbstractAction
{
    protected string $name = ActionEnum::TREAT_PLANT;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Status([
            'status' => EquipmentStatusEnum::PLANT_DISEASED,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::TREAT_PLANT_NO_DISEASE,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        if ($diseasedStatus = $parameter->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED)) {
            $parameter->removeStatus($diseasedStatus);
            $this->gameEquipmentService->persist($parameter);
        }

        return new Success();
    }
}
