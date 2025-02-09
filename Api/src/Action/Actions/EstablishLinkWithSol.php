<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\LinkWithSolConstraint;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EstablishLinkWithSol extends AbstractAction
{
    private const int FIRST_CONTACT_MORALE_BONUS = 3;
    protected ActionEnum $name = ActionEnum::ESTABLISH_LINK_WITH_SOL;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly D100RollServiceInterface $d100Roll,
        private readonly LinkWithSolRepository $linkWithSolRepository,
        private readonly StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new LinkWithSolConstraint([
                'shouldBeEstablished' => false,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::CONTACTED_SOL_TODAY,
                'contain' => false,
                'target' => HasStatus::PLAYER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::COMS_ALREADY_ATTEMPTED,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::DIRTY,
                'contain' => false,
                'target' => HasStatus::PLAYER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        $linkWithSol = $this->linkWithSol();
        $this->increaseStrength($linkWithSol);

        return $this->isLinkEstablished($linkWithSol) ? new Success() : new Fail();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $linkWithSol = $this->linkWithSol();
        if ($result->isASuccess()) {
            $this->markAsEstablished($linkWithSol);
            $this->giveFirstTimeContactMoraleBonus();
        }

        $this->markPlayerHasContactedSolToday();
    }

    private function giveFirstTimeContactMoraleBonus(): void
    {
        if ($this->daedalus()->doesNotHaveStatus(DaedalusStatusEnum::LINK_WITH_SOL_ESTABLISHED_ONCE)) {
            $this->addMoraleToAllPlayers();
            $this->markFirstContactMoraleBonusWasGiven();
        }
    }

    private function markFirstContactMoraleBonusWasGiven(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::LINK_WITH_SOL_ESTABLISHED_ONCE,
            holder: $this->daedalus(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function markPlayerHasContactedSolToday(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::CONTACTED_SOL_TODAY,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function addMoraleToAllPlayers(): void
    {
        foreach ($this->daedalus()->getAlivePlayers() as $player) {
            $this->eventService->callEvent(
                event: new PlayerVariableEvent(
                    player: $player,
                    variableName: PlayerVariableEnum::MORAL_POINT,
                    quantity: self::FIRST_CONTACT_MORALE_BONUS,
                    tags: $this->getTags(),
                    time: new \DateTime(),
                ),
                name: VariableEventInterface::CHANGE_VARIABLE
            );
        }
    }

    private function increaseStrength(LinkWithSol $linkWithSol): void
    {
        $linkWithSol->increaseStrength($this->getOutputQuantity());
        $this->linkWithSolRepository->save($linkWithSol);
    }

    private function markAsEstablished(LinkWithSol $linkWithSol): void
    {
        $linkWithSol->markAsEstablished();
        $this->linkWithSolRepository->save($linkWithSol);
    }

    private function isLinkEstablished(LinkWithSol $linkWithSol): bool
    {
        return $this->d100Roll->isSuccessful($linkWithSol->getStrength());
    }

    private function daedalus(): Daedalus
    {
        return $this->player->getDaedalus();
    }

    private function daedalusId(): int
    {
        return $this->daedalus()->getId();
    }

    private function linkWithSol(): LinkWithSol
    {
        return $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalusId());
    }
}
