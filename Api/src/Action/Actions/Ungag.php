<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus as StatusValidator;
use Mush\Action\Validator\PlaceType;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * implement ungag action.
 * For 1 ActionConfig Points, a player with gag status can ungag
 *  - remove gagged status of the current player.
 *
 * More info: http://mushpedia.com/wiki/Duct_Tape
 */
class Ungag extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::UNGAG;

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new StatusValidator([
            'status' => PlayerStatusEnum::GAGGED,
            'target' => StatusValidator::PLAYER,
            'contain' => true,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->statusService->removeStatus(
            PlayerStatusEnum::GAGGED,
            $this->player,
            $this->getActionConfig()->getActionTags(),
            new \DateTime()
        );
    }
}
