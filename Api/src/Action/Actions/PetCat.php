<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Carress" action (petting the cat).
 * This action is granted by Schrodinger.
 *
 * For 1 PA, "Caress" gives 3 Morale Points
 * to the player committing the action, if they haven't
 * done it before.
 *
 * @TODO: Infect player on injury if converted, can't cuddle if germaphobic, can't cuddle if ailurophobic, can't cuddle if allergic
 *
 * More info : http://www.mushpedia.com/wiki/Schr%C3%B6dinger
 */
class PetCat extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PET_CAT;

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
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::INVENTORY, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::GERMAPHOBE, 'target' => HasStatus::PLAYER, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PLAYER_IS_GERMAPHOBIC]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $tags = $this->getTags();
        $tags[] = $this->gameItemTarget()->getName();

        if ($this->player->hasStatus(PlayerStatusEnum::HAS_PETTED_CAT)) {
            return; // Stop early if player has already petted cat
        }

        $this->addMoraleToPlayer($tags);

        $this->addHasPettedCatToPlayer($tags);
    }

    private function addMoraleToPlayer(array $tags): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::MORAL_POINT,
            $this->getOutputQuantity(),
            $tags,
            new \DateTime()
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function addHasPettedCatToPlayer(array $tags): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::HAS_PETTED_CAT,
            $this->player,
            $tags,
            new \DateTime(),
        );
    }
}
