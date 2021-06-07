<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private const HAZARDOUS_RATE = 30;
    private const DECOMPOSING_RATE = 50;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        RandomServiceInterface $randomService,
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
        $this->consumableDiseaseService = $consumableDiseaseService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): void
    {
        if (($gameEquipment->hasStatus(EquipmentStatusEnum::HAZARDOUS) &&
                $this->randomService->isSuccessful(self::HAZARDOUS_RATE))
            || ($gameEquipment->hasStatus(EquipmentStatusEnum::DECOMPOSING) &&
                $this->randomService->isSuccessful(self::DECOMPOSING_RATE))
        ) {
            $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::PERISHED_FOOD, $player);
        }
    }

    public function handleConsumable(Player $player, GameEquipment $gameEquipment): void
    {
        $consumableEffect = $this->consumableDiseaseService->findConsumableDiseases($gameEquipment->getName(), $player->getDaedalus());

        if ($consumableEffect !== null) {
            /** @var ConsumableDiseaseAttribute $disease */
            foreach ($consumableEffect->getDiseases() as $disease) {
                if ($this->randomService->isSuccessful($disease->getRate())) {
                    $diseasePlayer = $this->playerDiseaseService->createDiseaseFromName($disease->getDisease(), $player);
                    $event = new DiseaseEvent($player, $diseasePlayer->getDiseaseConfig(), new \DateTime());
                    $this->eventDispatcher->dispatch($event, DiseaseEvent::NEW_DISEASE);
                    //@TODO: delay disease apparitio, currently creation and appaition happen at the same time
                    $this->eventDispatcher->dispatch($event, DiseaseEvent::APPEAR_DISEASE);
                }
            }

            /** @var ConsumableDiseaseAttribute $cure */
            foreach ($consumableEffect->getCures() as $cure) {
                if (($disease = $player->getDiseaseByName($cure->getDisease())) !== null &&
                    $this->randomService->isSuccessful($cure->getRate())
                ) {
                    $event = new DiseaseEvent($player, $disease->getDiseaseConfig(), new \DateTime());
                    $this->eventDispatcher->dispatch($event, DiseaseEvent::CURE_DISEASE);
                    $this->playerDiseaseService->delete($disease);
                }
            }
        }
    }
}
