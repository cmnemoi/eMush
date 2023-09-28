<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasDiseases;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Fake disease" action.
 *
 * For 1 PA, "Fake disease" gives current player a disease.
 * This action is implemented for test purpose but may be further used as a mush skill
 */
class FakeDisease extends AbstractAction
{
    protected string $name = ActionEnum::FAKE_DISEASE;
    protected DiseaseCauseServiceInterface $diseaseCauseService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        DiseaseCauseServiceInterface $diseaseCauseService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->diseaseCauseService = $diseaseCauseService;
    }

    protected function support(?LogParameterInterface $support, array $parameters): bool
    {
        return $support === null;
    }

    /* This may be latter used
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasDiseases([
            'groups' => ['execute'],
            'type' => TypeEnum::DISEASE,
            'target' => HasDiseases::PLAYER,
            'isEmpty' => true,
            'message' => ActionImpossibleCauseEnum::HAVE_ALL_FAKE_DISEASES,
        ]));
    }*/

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->diseaseCauseService->handleDiseaseForCause($this->getActionName(), $this->player);
    }
}
