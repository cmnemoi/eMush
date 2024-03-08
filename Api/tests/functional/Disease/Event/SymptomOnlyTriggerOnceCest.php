<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

use Mush\Action\Actions\Consume;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\SymptomLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class SymptomOnlyTriggerOnceCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private Consume $consume;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->consume = $I->grabService(Consume::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testSymptomOnlyTriggerOnce(FunctionalTester $I): void
    {
        // given player has a disease with vomiting symptom on consume
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::GASTROENTERIS,
            player: $this->player,
            reasons: [],
        );

        // given player has another disease with vomiting symptom on consume
        $disease2 = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FOOD_POISONING,
            player: $this->player,
            reasons: [],
        );

        // when player consume food
        $food = $this->gameEquipmentService->createGameEquipmentFromName(
            GameRationEnum::STANDARD_RATION,
            $this->player1,
            [],
            new \DateTime()
        );
        $consumeConfig = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::CONSUME]);
        $this->consume->loadParameters($consumeConfig, $this->player1, $food);

        $this->consume->execute();

        // then I should see a room log reporting vomiting only once
        $logs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'playerInfo' => $this->player->getPlayerInfo(),
                'place' => $this->player->getPlace()->getLogName(),
                'log' => SymptomLogEnum::VOMITING,
            ]
        );

        $I->assertCount(1, $logs);
    }
}
