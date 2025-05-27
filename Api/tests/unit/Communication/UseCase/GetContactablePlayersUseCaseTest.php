<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communication\UseCase;

use Mush\Communication\UseCase\GetContactablePlayersUseCase;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetContactablePlayersUseCaseTest extends TestCase
{
    private GetContactablePlayersUseCase $useCase;

    private Daedalus $daedalus;
    private Player $jinSu;
    private Player $chun;

    protected function setUp(): void
    {
        $this->useCase = new GetContactablePlayersUseCase();
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->jinSu = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::JIN_SU, $this->daedalus);
        $this->chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $this->daedalus);
    }

    public function testShouldReturnPlayersInTheRoom(): void
    {
        // when I call use case on Jin Su
        $contactablePlayers = $this->useCase->execute($this->jinSu);

        // then I should see Chun in contactable players
        self::assertContains($this->chun, $contactablePlayers->toArray());
    }

    public function testShouldReturnPlayersWithAWalkieTalkie(): void
    {
        // given JS has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $this->jinSu);

        // given Paola
        $paola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, $this->daedalus);

        // given Paola has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $paola);

        // given Paola is in front corridor
        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        $paola->changePlace($frontCorridor);

        // when I call use case on Jin Su
        $contactablePlayers = $this->useCase->execute($this->jinSu);

        // then I should see Paola in contactable players
        self::assertContains($paola, $contactablePlayers->toArray());
    }

    public function testShouldReturnPlayersWithAnItrackie(): void
    {
        // given JS has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $this->jinSu);

        // given Paola
        $paola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, $this->daedalus);

        // given Paola has an itrackie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::ITRACKIE, $paola);

        // given Paola is in front corridor
        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        $paola->changePlace($frontCorridor);

        // when I call use case on Jin Su
        $contactablePlayers = $this->useCase->execute($this->jinSu);

        // then I should see Paola in contactable players
        self::assertContains($paola, $contactablePlayers->toArray());
    }

    public function testShouldReturnPlayersWithBrainsync(): void
    {
        // given JS has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $this->jinSu);

        // given Paola
        $paola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, $this->daedalus);

        // given Paola has Brainsync status
        StatusFactory::createStatusByNameForHolder(PlayerStatusEnum::BRAINSYNC, $paola);

        // given Paola is in front corridor
        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        $paola->changePlace($frontCorridor);

        // when I call use case on Jin Su
        $contactablePlayers = $this->useCase->execute($this->jinSu);

        // then I should see Paola in contactable players
        self::assertContains($paola, $contactablePlayers->toArray());
    }

    public function testShouldReturnPlayersInCommsCenterRoom(): void
    {
        // given JS has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $this->jinSu);

        // given Paola
        $paola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, $this->daedalus);

        // given Paola is in front corridor
        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        $paola->changePlace($frontCorridor);

        // given there is a comms center in the room
        GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::COMMUNICATION_CENTER, $frontCorridor);

        // when I call use case on Jin Su
        $contactablePlayers = $this->useCase->execute($this->jinSu);

        // then I should see Paola in contactable players
        self::assertContains($paola, $contactablePlayers->toArray());
    }

    public function testShouldReturnPlayersWithCommsManagerTitle(): void
    {
        // given JS has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $this->jinSu);

        // given Paola
        $paola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, $this->daedalus);

        // given Paola has comms manager title
        $paola->addTitle(TitleEnum::COM_MANAGER);

        // given Paola is in front corridor
        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        $paola->changePlace($frontCorridor);

        // when I call use case on Jin Su
        $contactablePlayers = $this->useCase->execute($this->jinSu);

        // then I should see Paola in contactable players
        self::assertContains($paola, $contactablePlayers->toArray());
    }

    public function testShouldNotReturnPlayerWithMeansOfCommunicationIfAskingPlayerDoesNotHaveOne(): void
    {
        // given Paola
        $paola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, $this->daedalus);

        // given Paola has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $paola);

        // given Paola is in front corridor
        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        $paola->changePlace($frontCorridor);

        // when I call use case on Jin Su
        $contactablePlayers = $this->useCase->execute($this->jinSu);

        // then I should not see Paola in contactable players
        self::assertNotContains($paola, $contactablePlayers->toArray());
    }
}
