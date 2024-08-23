<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;

abstract class AbstractLootItemsEventHandler extends AbstractPlanetSectorEventHandler
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService);
        $this->gameEquipmentService = $gameEquipmentService;
    }

    /**
     * @return ArrayCollection<array-key, GameEquipment>
     *
     * @psalm-suppress InvalidArgument
     */
    protected function createRandomItemsFromEvent(PlanetSectorEvent $event): ArrayCollection
    {
        $numberOfItemsToCreate = $this->getNumberOfItemsToCreate($event);
        $createdItems = new ArrayCollection();

        for ($i = 0; $i < $numberOfItemsToCreate; ++$i) {
            $itemToCreate = (string) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputTable());
            $finder = $this->randomService->getRandomPlayer($event->getExploration()->getNotLostActiveExplorators());

            $tags = $event->getTags();
            $tags[] = LogEnum::FOUND_ITEM_IN_EXPLORATION;
            $createdItems->add($this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $itemToCreate,
                equipmentHolder: $event->getExploration()->getDaedalus()->getPlanetPlace(),
                reasons: $tags,
                time: $event->getTime(),
                visibility: VisibilityEnum::PUBLIC,
                author: $finder
            ));
        }

        return $createdItems;
    }

    protected function getLogParameters(PlanetSectorEvent $event): array
    {
        $logParameters = parent::getLogParameters($event);
        $logParameters['bonus_loot_thanks_to_skill'] = $this->getBonusLootLog($event);

        return $logParameters;
    }

    protected function getBonusLootLog(PlanetSectorEvent $event): ?string
    {
        $bonusLoot = $this->getBonusLootFromEvent($event);
        $language = $event->getExploration()->getDaedalus()->getLanguage();
        $skill = $this->getBonusSkillFromEvent($event);

        return $bonusLoot > 0 ? \sprintf(
            '////%s',
            $this->translationService->translate(
                key: 'bonus_loot_thanks_to_skill',
                parameters: [
                    'skill' => $skill->toString(),
                    'quantity' => $bonusLoot,
                ],
                domain: 'planet_sector_event',
                language: $language
            )
        ) : '';
    }

    private function getNumberOfItemsToCreate(PlanetSectorEvent $event): int
    {
        $numberOfItemsToCreate = (int) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputQuantity());

        return $numberOfItemsToCreate + $this->getBonusLootFromEvent($event);
    }

    private function getBonusLootFromEvent(PlanetSectorEvent $event): int
    {
        $exploration = $event->getExploration();

        return match ($event->getName()) {
            PlanetSectorEvent::PROVISION => $exploration->getNumberOfActiveSurvivalists(),
            PlanetSectorEvent::HARVEST => $exploration->getNumberOfActiveBotanists(),
            default => 0,
        };
    }

    private function getBonusSkillFromEvent(PlanetSectorEvent $event): SkillEnum
    {
        return match ($event->getName()) {
            PlanetSectorEvent::PROVISION => SkillEnum::SURVIVALIST,
            PlanetSectorEvent::HARVEST => SkillEnum::BOTANIST,
            default => SkillEnum::NULL,
        };
    }
}
