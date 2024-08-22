<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ExchangeBody extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::EXCHANGE_BODY;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private AddSkillToPlayerService $addSkillToPlayer,
        private UpdatePlayerNotificationService $updatePlayerNotification,
        private PlayerRepositoryInterface $playerRepository,
        private StatusServiceInterface $statusService,
        private TranslationServiceInterface $translationService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_EXCHANGED_BODY,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::UNIQUE_ACTION,
            ])
        );
        $metadata->addConstraint(
            new GameVariableLevel([
                'variableName' => PlayerVariableEnum::SPORE,
                'target' => GameVariableLevel::TARGET_PLAYER,
                'checkMode' => GameVariableLevel::IS_MIN,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::TRANSFER_NO_SPORE,
            ]),
        );
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->exchangePlayerUsers();
        $this->transferMushSkills();
        $this->makeTargetMush();
        $this->makePlayerHuman();
        $this->createHasExchangedBodyStatus();
        $this->createTargetNotification();
        $this->createPlayerNotification();

        // Add some details to allow player swap in front-end
        $result->addDetail('playerId', $this->target()->getId());
    }

    private function exchangePlayerUsers(): void
    {
        $player = $this->player;
        $target = $this->target();

        $playerUser = $player->getUser();
        $targetUser = $target->getUser();

        $playerInfo = $player->getPlayerInfo();
        $targetInfo = $target->getPlayerInfo();

        $playerInfo->updateUser($targetUser);
        $targetInfo->updateUser($playerUser);

        $this->playerRepository->save($player);
        $this->playerRepository->save($target);
    }

    private function transferMushSkills(): void
    {
        $this->player
            ->getMushSkills()
            ->map(fn (Skill $skill) => $this->addSkillToPlayer->execute($skill->getName(), $this->target()));
    }

    private function makeTargetMush(): void
    {
        /** @var Player $target */
        $target = $this->target;

        $event = new PlayerEvent(
            $target,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($event, PlayerEvent::CONVERSION_PLAYER);
    }

    private function makePlayerHuman(): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function createHasExchangedBodyStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_EXCHANGED_BODY,
            holder: $this->target(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function createTargetNotification(): void
    {
        $this->updatePlayerNotification->execute($this->target(), sprintf('%s_mush', ActionEnum::EXCHANGE_BODY->value));
    }

    private function createPlayerNotification(): void
    {
        $this->updatePlayerNotification->execute($this->player, sprintf('%s_human', ActionEnum::EXCHANGE_BODY->value));
    }

    private function target(): Player
    {
        if ($this->target instanceof Player) {
            return $this->target;
        }

        throw new \RuntimeException('Target is not a player');
    }
}
