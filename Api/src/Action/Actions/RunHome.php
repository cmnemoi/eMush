<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\IsExploring;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RunHome extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::RUN_HOME;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private ExplorationServiceInterface $explorationService,
        private UpdatePlayerNotificationService $updatePlayerNotification,
        private TranslationServiceInterface $translationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new IsExploring([
                'groups' => [ClassConstraint::VISIBILITY],
            ])
        );
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
        $exploration = $this->player->getExplorationOrThrow();
        $closedExploration = $exploration->getClosedExploration();

        $this->explorationService->closeExploration($exploration, reasons: $this->getTags());
        $this->sendExplorationClosedNotificationToExplorators($closedExploration);
    }

    private function sendExplorationClosedNotificationToExplorators(ClosedExploration $closedExploration): void
    {
        /** @var Collection<array-key, Player> $explorators */
        $explorators = $closedExploration->getClosedExplorators()->map(static fn (ClosedPlayer $explorator) => $explorator->getPlayerOrThrow());

        foreach ($explorators as $explorator) {
            $this->updatePlayerNotification->execute(
                player: $explorator,
                message: PlayerNotificationEnum::EXPLORATION_CLOSED_BY_U_TURN->toString(),
                parameters: [
                    $this->player->getLogKey() => $this->player->getLogName(),
                    'exploration_link' => $this->translatedExplorationLink($closedExploration),
                ]
            );
        }
    }

    private function translatedExplorationLink(ClosedExploration $closedExploration): string
    {
        $explorationUrl = \sprintf('/expPerma/%d', $closedExploration->getId());
        $explorationArchive = $this->translationService->translate(
            key: 'exploration_archive',
            parameters: [],
            domain: 'misc',
            language: $closedExploration->getDaedalusInfo()->getLanguage()
        );

        return \sprintf("<a href='%s'>%s</a>", $explorationUrl, $explorationArchive);
    }
}
