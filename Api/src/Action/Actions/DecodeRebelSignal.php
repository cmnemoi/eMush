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
use Mush\Action\Validator\IsThereContactingRebelBase;
use Mush\Action\Validator\LinkWithSolConstraint;
use Mush\Action\Validator\NeedTitle;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DecodeRebelSignal extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::DECODE_REBEL_SIGNAL;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private ModifierCreationServiceInterface $modifierCreationService,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasStatus([
                'status' => PlayerStatusEnum::FOCUSED,
                'target' => HasStatus::PLAYER,
                'statusTargetName' => EquipmentEnum::COMMUNICATION_CENTER,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new NeedTitle([
                'title' => TitleEnum::COM_MANAGER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::COMS_NOT_OFFICER,
            ]),
            new LinkWithSolConstraint([
                'shouldBeEstablished' => true,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::LINK_WITH_SOL_NOT_ESTABLISHED,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::DIRTY,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            ]),
            new IsThereContactingRebelBase([
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::NO_ACTIVE_REBEL,
            ]),
        ]);
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $rebelBase = $this->rebelBase();
        $this->increaseDecodingProgress($rebelBase);

        if ($rebelBase->isDecoded()) {
            $this->createRebelBaseModifiers($rebelBase);
            $this->endRebelBaseContact($rebelBase);
        }
    }

    private function increaseDecodingProgress(RebelBase $rebelBase): void
    {
        $progress = $this->getRandomInteger->execute(1, $this->getOutputQuantity());
        $rebelBase->increaseDecodingProgress($progress);
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function createRebelBaseModifiers(RebelBase $rebelBase): void
    {
        foreach ($rebelBase->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $this->player->getDaedalus(),
                modifierProvider: $rebelBase,
                tags: $this->getTags(),
                time: new \DateTime(),
            );
        }
    }

    private function endRebelBaseContact(RebelBase $rebelBase): void
    {
        $rebelBase->endContact();
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function rebelBase(): RebelBase
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow(
            daedalusId: $this->player->getDaedalus()->getId(),
            name: RebelBaseEnum::from($this->getParameterOrThrow('rebel_base')),
        );

        if ($rebelBase->isNotContacting()) {
            throw new GameException('You cannot decode the signal of a non-contacting rebel base!');
        }

        return $rebelBase;
    }
}
