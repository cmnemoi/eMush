<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\DailySporesLimit;
use Mush\Action\Validator\MushSpore;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Infect extends AbstractAction
{
    protected string $name = ActionEnum::INFECT;

    /** @var Player */
    protected $parameter;

    private StatusServiceInterface $statusService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Status(['status' => PlayerStatusEnum::MUSH, 'target' => Status::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Reach(['player' => true, 'groups' => ['visibility']]));
        $metadata->addConstraint(new MushSpore(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INFECT_NO_SPORE]));
        $metadata->addConstraint(new Status(['status' => PlayerStatusEnum::MUSH, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INFECT_MUSH]));
        $metadata->addConstraint(new Status(['status' => PlayerStatusEnum::IMMUNIZED, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INFECT_IMMUNE]));
        $metadata->addConstraint(new DailySporesLimit(['target' => DailySporesLimit::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INFECT_DAILY_LIMIT]));
    }

    public function cannotExecuteReason(): ?string
    {
        //@TODO
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);

        if ($sporeStatus === null || !($sporeStatus instanceof ChargeStatus) ||
            $mushStatus === null || !($mushStatus instanceof ChargeStatus)
        ) {
            throw new Error('invalid spore and mush status');
        }

        if ($sporeStatus->getCharge() <= 0) {
            return ActionImpossibleCauseEnum::INFECT_NO_SPORE;
        }
        if ($mushStatus->getCharge() <= 0) {
            return ActionImpossibleCauseEnum::INFECT_DAILY_LIMIT;
        }
        if ($this->parameter->isMush()) {
            return ActionImpossibleCauseEnum::INFECT_MUSH;
        }
        if ($this->parameter->getStatusByName(PlayerStatusEnum::IMMUNIZED)) {
            return ActionImpossibleCauseEnum::INFECT_IMMUNE;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $playerEvent = new PlayerEvent($this->parameter);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        $sporeStatus->addCharge(-1);
        $this->statusService->persist($sporeStatus);

        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);
        $mushStatus->addCharge(-1);
        $this->statusService->persist($mushStatus);

        $target = new Target($this->parameter->getCharacterConfig()->getName(), 'character');

        return new Success($target);
    }
}
