<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Listener;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PregnantCest extends AbstractExplorationTester
{
    private StatusServiceInterface $statusService;

    private Player $human;
    private Player $otherHuman;
    private Player $mush;
    private Player $otherMush;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->human = $this->chun;
        $this->otherHuman = $this->kuanTi;
        $this->mush = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::FINOLA);
        $this->convertPlayerToMush($I, $this->mush);
        $this->otherMush = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::TERRENCE);
        $this->convertPlayerToMush($I, $this->otherMush);
    }

    public function shouldDistributeTriumphOnHumanPregnancy(FunctionalTester $I): void
    {
        $this->whenPlayerBecomesPregnant($this->human);

        // Then pregnant human gets 8 triumph and all humans get 2 triumph
        $I->assertEquals(10, $this->human->getTriumph());
        $I->assertEquals(2, $this->otherHuman->getTriumph());
        $I->assertEquals(0, $this->mush->getTriumph());
        $I->assertEquals(0, $this->otherMush->getTriumph());
    }

    public function shouldDistributeTriumphOnMushPregnancy(FunctionalTester $I): void
    {
        $this->whenPlayerBecomesPregnant($this->mush);

        // Then all mush get 8 triumph and all humans get 2 triumph
        $I->assertEquals(2, $this->human->getTriumph());
        $I->assertEquals(2, $this->otherHuman->getTriumph());
        $I->assertEquals(8, $this->mush->getTriumph());
        $I->assertEquals(8, $this->otherMush->getTriumph());
    }

    private function whenPlayerBecomesPregnant(Player $mother): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::PREGNANT,
            holder: $mother,
            tags: [],
            time: new \DateTime(),
            visibility: VisibilityEnum::PRIVATE,
        );
    }
}
