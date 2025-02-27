<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communications\Service\KillLinkWithSolService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ActionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private KillLinkWithSolService $killLinkWithSol) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        $actionName = $event->getActionName();

        if ($actionName === ActionEnum::EXPRESS_COOK) {
            $this->killLinkWithSol->execute(
                $event->getDaedalusId(),
                successRate: $event->getActionConfig()->getOutputQuantity(),
                tags: $event->getTags(),
            );
        }
    }
}
