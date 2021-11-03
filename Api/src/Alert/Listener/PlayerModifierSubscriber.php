<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerModifierEvent::class => ['onChangeVariable', -10], //Applied after player modification
        ];
    }

    public function onChangeVariable(PlayerModifierEvent $playerEvent): void
    {
        if ($playerEvent->getModifiedVariable() === PlayerVariableEnum::SATIETY) {
            $this->alertService->handleSatietyAlert($playerEvent->getPlayer()->getDaedalus());
        }
    }
}
