<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\Skill;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetSkillPoints extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::RESET_SKILL_POINTS;
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
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN, RoleEnum::ADMIN], 'groups' => ['visibility']]));
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
        /** @var Skill $skill */
        foreach ($this->player->getSkills()->filter(static fn (Skill $skill) => !$skill->getSkillPointConfig()->isNull()) as $skill) {
            $skillPointsStatus = $this->player->getChargeStatusByNameOrThrow($skill->getSkillPointConfig()->getStatusName());
            $this->statusService->updateCharge($skillPointsStatus, $skillPointsStatus->getMaxChargeOrThrow(), [], new \DateTime());
        }
    }
}
