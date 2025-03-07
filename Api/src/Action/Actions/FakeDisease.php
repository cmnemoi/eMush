<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasRole;
use Mush\Action\Validator\PlaceType;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
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
    protected ActionEnum $name = ActionEnum::FAKE_DISEASE;
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

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new PlaceType([
                'groups' => ['execute'],
                'type' => 'planet',
                'allowIfTypeMatches' => false,
                'message' => ActionImpossibleCauseEnum::ON_PLANET,
            ]),
            new HasRole([
                'roles' => [RoleEnum::SUPER_ADMIN, RoleEnum::ADMIN],
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
        ]);
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->diseaseCauseService->handleDiseaseForCause($this->getActionName(), $this->player);
    }
}
