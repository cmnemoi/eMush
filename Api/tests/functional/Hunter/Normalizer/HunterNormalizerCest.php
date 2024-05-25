<?php

declare(strict_types=1);

namespace Api\Tests\functional\Hunter\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Normalizer\HunterNormalizer;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class HunterNormalizerCest extends AbstractFunctionalTest
{
    private HunterNormalizer $normalizer;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(HunterNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldNormalizeHunter(FunctionalTester $I): void
    {
        // given Chun is in a turret
        $this->chun->changePlace($this->createExtraPlace(placeName: RoomEnum::CENTRE_ALPHA_TURRET, I: $I, daedalus: $this->daedalus));

        // given there is a turret in Chun's room
        $turret = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::TURRET_COMMAND,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $shootHunterActionId = $turret->getEquipment()
            ->getMechanicByName(EquipmentMechanicEnum::WEAPON)
            ->getActionByNameOrThrow(ActionEnum::SHOOT_HUNTER)
            ->getId();

        // given 1 hunter is spawned
        $hunter = $this->createHunter();

        // when I normalize the hunter for Chun
        $normalizedHunter = $this->normalizer->normalize($hunter, format: null, context: ['currentPlayer' => $this->chun]);

        // then the normalized hunter should be the expected array
        $I->assertEquals(
            expected: $normalizedHunter,
            actual: [
                'id' => $hunter->getId(),
                'key' => HunterEnum::HUNTER,
                'name' => 'Hunter',
                'description' => 'Chasseur standard de la FDS',
                'health' => 6,
                'charges' => null,
                'actions' => [
                    [
                        'id' => $shootHunterActionId,
                        'key' => ActionEnum::SHOOT_HUNTER->value,
                        'name' => 'Tirer',
                        'actionPointCost' => 1,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'specialistPointCosts' => [],
                        'successRate' => 30,
                        'description' => 'Tire une charge de Telsatron sur un vaisseau ennemi.',
                        'canExecute' => true,
                        'confirmation' => null,
                        'actionProvider' => ['class' => $turret->getClassName(), 'id' => $turret->getId()],
                    ],
                ],
            ]
        );
    }

    private function createHunter(): Hunter
    {
        $this->daedalus->setHunterPoints(10);
        $hunterPoolEvent = new HunterPoolEvent(
            $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        return $this->daedalus->getAttackingHunters()->first();
    }
}
