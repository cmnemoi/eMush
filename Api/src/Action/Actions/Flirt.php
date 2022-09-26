<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FlirtedAlready;
use Mush\Action\Validator\FromSameFamily;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsSameGender;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Event\Service\EventService;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Flirt extends AbstractAction
{
    protected string $name = ActionEnum::FLIRT;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventService $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsSameGender(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::ANTISOCIAL,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::FLIRT_ANTISOCIAL,
        ]));
        $metadata->addConstraint(new FromSameFamily(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FLIRT_SAME_FAMILY]));
        $metadata->addConstraint(new FlirtedAlready(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FLIRT_ALREADY_FLIRTED]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        // @TODO add pop up to confirm flirt

        $this->player->addFlirt($parameter);

        $this->playerService->persist($this->player);

        return new Success();
    }
}
