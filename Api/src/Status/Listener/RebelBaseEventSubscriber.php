<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RebelBaseEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private StatusServiceInterface $statusService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RebelBaseDecodedEvent::class => 'onRebelBaseDecoded',
        ];
    }

    public function onRebelBaseDecoded(RebelBaseDecodedEvent $event): void
    {
        if ($event->hasTag(RebelBaseEnum::LUYTEN_CETI->toString())) {
            $daedalus = $this->daedalusRepository->findByIdOrThrow($event->daedalusId);
            $players = $daedalus->getAlivePlayers();

            $modifier = $daedalus->getModifiers()->getModifierByModifierNameOrThrow(ModifierNameEnum::LUYTEN_CETI_REBEL_BASE_MODIFIER);
            foreach ($players as $player) {
                $this->statusService->createStatusFromName(
                    $modifier->getVariableModifierConfigOrThrow()->getTargetVariable(),
                    $player,
                    $event->getTags(),
                    new \DateTime(),
                );
            }
        }
    }
}
