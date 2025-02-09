<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EstablishLinkWithSol extends AbstractAction
{
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
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $linkWithSol = $this->linkWithSol();
        $linkWithSol->increaseStrength($this->getOutputQuantity());

        if ($this->d100Roll->isSuccessful($linkWithSol->getStrength())) {
            $linkWithSol->markAsEstablished();
        }

        $this->linkWithSolRepository->save($linkWithSol);

        $this->markPlayerHasContactedSolToday();
    }

    private function daedalusId(): int
    {
        return $this->player->getDaedalus()->getId();
    }

    private function linkWithSol(): LinkWithSol
    {
        return $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalusId());
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
}
