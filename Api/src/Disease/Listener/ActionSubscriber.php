<?php

namespace Mush\Disease\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private const CONTACT_DISEASE_RATE = 1;

    private DiseaseCauseServiceInterface $diseaseCauseService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;

    public function __construct(
        DiseaseCauseServiceInterface $diseaseCauseService,
        RandomServiceInterface $randomService,
        PlayerDiseaseServiceInterface $playerDiseaseService
    ) {
        $this->diseaseCauseService = $diseaseCauseService;
        $this->randomService = $randomService;
        $this->playerDiseaseService = $playerDiseaseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        $this->handleContactDiseases($event);
    }

    private function handleContactDiseases(ActionEvent $event): void
    {
        if ($event->getActionConfig()->getActionName() !== ActionEnum::MOVE) {
            return;
        }

        $player = $event->getAuthor();
        $otherPlayersInRoom = $player->getPlace()->getPlayers()->filter(static function ($otherPlayer) use ($player) {
            return $otherPlayer->getId() !== $player->getId();
        });

        if ($otherPlayersInRoom->isEmpty()) {
            return;
        }

        $playerDiseases = $player->getMedicalConditions()->getActiveDiseases();
        $playerDiseases = $playerDiseases->map(static function ($disease) {
            return $disease->getDiseaseConfig()->getName();
        })->toArray();

        if (\count($playerDiseases) === 0) {
            return;
        }

        $contactDiseases = array_keys(
            $this->diseaseCauseService->findCauseConfigByDaedalus(
                DiseaseCauseEnum::CONTACT,
                $player->getDaedalus()
            )
                ->getDiseases()
                ->toArray()
        );

        if (\count(array_intersect($playerDiseases, $contactDiseases)) === 0) {
            return;
        }

        $diseaseToTransmit = $this->randomService->getRandomElements($contactDiseases, 1)[0] ?? null;
        $playerToTransmitTo = $this->randomService->getRandomElements($otherPlayersInRoom->toArray(), 1)[0] ?? null;

        if ($diseaseToTransmit === null || $playerToTransmitTo === null) {
            return;
        }

        if ($this->randomService->isSuccessful(self::CONTACT_DISEASE_RATE)) {
            $this->playerDiseaseService->createDiseaseFromName($diseaseToTransmit, $playerToTransmitTo, [DiseaseCauseEnum::CONTACT]);
        }
    }
}
