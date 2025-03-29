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
use Mush\Action\Validator\NeedTitle;
use Mush\Action\Validator\NumberOfAttackingHunters;
use Mush\Communications\Service\AreTradeOptionConditionsAreMetService;
use Mush\Communications\Service\ConsumeRequiredTradeAssetsService;
use Mush\Communications\Service\CreateOfferedTradeAssetsService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\DeleteTransportService;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AcceptTrade extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::ACCEPT_TRADE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly AreTradeOptionConditionsAreMetService $areTradeOptionConditionsAreMet,
        private readonly ConsumeRequiredTradeAssetsService $consumeRequiredTradeAssets,
        private readonly CreateOfferedTradeAssetsService $createOfferedTradeAssets,
        private readonly DeleteTransportService $deleteTransport,
    ) {
        parent::__construct($eventService, $actionService, $validator);
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
            new NumberOfAttackingHunters([
                'mode' => NumberOfAttackingHunters::GREATER_THAN,
                'number' => 0,
                'exclude' => [HunterEnum::ASTEROID, HunterEnum::TRANSPORT],
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new NeedTitle([
                'title' => TitleEnum::COM_MANAGER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::COMS_NOT_OFFICER,
                'allowIfNoPlayerHasTheTitle' => true,
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
        $this->throwIfTradeOptionConditionsAreNotMet();

        $this->consumeRequiredTradeAssets->execute($this->player, $this->tradeOptionId());
        $this->createOfferedTradeAssets->execute($this->player, $this->tradeOptionId());
        $this->deleteTransport->byTradeOptionId($this->tradeOptionId());
    }

    private function throwIfTradeOptionConditionsAreNotMet(): void
    {
        if (!$this->areTradeOptionConditionsAreMet->execute($this->player, $this->tradeOptionId())) {
            throw new GameException('Trade option conditions are not met!');
        }
    }

    private function tradeOptionId(): int
    {
        return $this->getParameterOrThrow('tradeOptionId');
    }
}
