<?php

declare(strict_types=1);

namespace Mush\Skill\Handler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Factory\GameModifierFactory;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Equipment\TestDoubles\FakePlayerMoralVariableEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ShrinkHandlerTest extends TestCase
{
    private ShrinkHandler $shrinkHandler;
    private Daedalus $daedalus;
    private Player $shrink;
    private Player $patient;
    private Player $secondPatient;

    protected function setUp(): void
    {
        $this->shrinkHandler = new ShrinkHandler(new FakePlayerMoralVariableEventService(moraleGain: 1));
    }

    public function testShouldGiveMoraleToPlayersInRoom(): void
    {
        // Given
        $this->givenAShrinkWithTwoLaidDownPatientsInRoom();

        // When
        $this->whenShrinkSkillActs();

        // Then
        $this->thenBothPatientsShouldGainOneMoralePoint();
    }

    public function testShouldNotGiveMoraleIfDead(): void
    {
        // Given
        $this->givenAShrinkWithOneLaidDownPatientInRoom();
        $this->givenShrinkIsDead();

        // When
        $this->whenShrinkSkillActs();

        // Then
        $this->thenPatientShouldNotGainMoralePoint();
    }

    public function testShouldNotGiveMoraleIfPlayerNotShrink(): void
    {
        // Given
        $this->givenAPlayerWithoutShrinkSkillAndOneLaidDownPatientInRoom();

        // When
        $this->whenShrinkSkillActs();

        // Then
        $this->thenPatientShouldNotGainMoralePoint();
    }

    public function testShouldNotGiveMoraleIfPatientsAreNotLaidDown(): void
    {
        // Given
        $this->givenAShrinkWithOnePatientNotLaidDownInRoom();

        // When
        $this->whenShrinkSkillActs();

        // Then
        $this->thenPatientShouldNotGainMoralePoint();
    }

    public function testShouldGiveMoraleToOtherLaidDownShrinkInRoom(): void
    {
        // Given
        $this->givenAShrink();
        $laidDownShrink = $this->givenLaidDownShrink();

        // When
        $this->whenShrinkSkillActs();

        // Then
        $this->thenPlayerShouldGainOneMoralePoint($laidDownShrink);
    }

    public function testShouldNotGiveMoraleToDeadPlayer(): void
    {
        // Given
        $this->givenAShrink();
        $laidDownDeadPatient = $this->givenALaidDownDeadPatient();

        // When
        $this->whenShrinkSkillActs();

        // Then
        $this->thenPlayerShouldNotGainMoralePoint($laidDownDeadPatient);
    }

    public function testMultipleShrinksInRoomGiveOnlyOneMoralePoint(): void
    {
        // Given
        $this->givenAShrink();
        $this->givenAnotherShrink();
        $laidDownPatient = $this->givenALaidDownPatient();

        // When
        $this->whenShrinkSkillActs();

        // Then
        $this->thenPlayerShouldGainOneMoralePoint($laidDownPatient);
    }

    private function givenAShrinkWithTwoLaidDownPatientsInRoom(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->shrink = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        Skill::createByNameForPlayer(SkillEnum::SHRINK, $this->shrink);
        GameModifierFactory::createByNameForHolder(
            name: ModifierNameEnum::PLAYER_PLUS_1_MORALE_POINT_TO_OTHER_LAID_DOWN_PLAYERS_IN_ROOM,
            holder: $this->shrink,
        );

        $this->patient = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $this->patient,
        );

        $this->secondPatient = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $this->secondPatient,
        );
    }

    private function givenAShrinkWithOneLaidDownPatientInRoom(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->shrink = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        Skill::createByNameForPlayer(SkillEnum::SHRINK, $this->shrink);

        $this->patient = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $this->patient,
        );
    }

    private function givenAPlayerWithoutShrinkSkillAndOneLaidDownPatientInRoom(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->shrink = PlayerFactory::createPlayerWithDaedalus($this->daedalus);

        $this->patient = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $this->patient,
        );
    }

    private function givenAShrinkWithOnePatientNotLaidDownInRoom(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->shrink = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        Skill::createByNameForPlayer(SkillEnum::SHRINK, $this->shrink);
        GameModifierFactory::createByNameForHolder(
            name: ModifierNameEnum::PLAYER_PLUS_1_MORALE_POINT_TO_OTHER_LAID_DOWN_PLAYERS_IN_ROOM,
            holder: $this->shrink,
        );

        $this->patient = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenAShrink(): Player
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->shrink = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        Skill::createByNameForPlayer(SkillEnum::SHRINK, $this->shrink);
        GameModifierFactory::createByNameForHolder(
            name: ModifierNameEnum::PLAYER_PLUS_1_MORALE_POINT_TO_OTHER_LAID_DOWN_PLAYERS_IN_ROOM,
            holder: $this->shrink,
        );

        return $this->shrink;
    }

    private function givenLaidDownShrink(): Player
    {
        $shrink = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        Skill::createByNameForPlayer(SkillEnum::SHRINK, $shrink);
        GameModifierFactory::createByNameForHolder(
            name: ModifierNameEnum::PLAYER_PLUS_1_MORALE_POINT_TO_OTHER_LAID_DOWN_PLAYERS_IN_ROOM,
            holder: $shrink,
        );
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $shrink,
        );

        return $shrink;
    }

    private function givenShrinkIsDead(): void
    {
        $this->shrink->kill();
    }

    private function givenALaidDownDeadPatient(): Player
    {
        $deadPatient = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $deadPatient->kill();
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $deadPatient,
        );

        return $deadPatient;
    }

    private function givenAnotherShrink(): Player
    {
        $anotherShrink = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        Skill::createByNameForPlayer(SkillEnum::SHRINK, $anotherShrink);
        GameModifierFactory::createByNameForHolder(
            name: ModifierNameEnum::PLAYER_PLUS_1_MORALE_POINT_TO_OTHER_LAID_DOWN_PLAYERS_IN_ROOM,
            holder: $anotherShrink,
        );

        return $anotherShrink;
    }

    private function givenALaidDownPatient(): Player
    {
        $patient = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $patient,
        );

        return $patient;
    }

    private function whenShrinkSkillActs(): void
    {
        $this->shrinkHandler->execute($this->shrink->getPlace());
    }

    private function thenBothPatientsShouldGainOneMoralePoint(): void
    {
        self::assertEquals(1, $this->patient->getMoralPoint(), 'Patient should have gained 1 morale point');
        self::assertEquals(1, $this->secondPatient->getMoralPoint(), 'Second patient should have gained 1 morale point');
    }

    private function thenPatientShouldNotGainMoralePoint(): void
    {
        self::assertEquals(0, $this->patient->getMoralPoint(), 'Patient should not have gained 1 morale point');
    }

    private function thenPlayerShouldGainOneMoralePoint(Player $player): void
    {
        self::assertEquals(1, $player->getMoralPoint(), 'Player should have gained 1 morale point');
    }

    private function thenPlayerShouldNotGainMoralePoint(Player $player): void
    {
        self::assertEquals(0, $player->getMoralPoint(), 'Player should not have gained 1 morale point');
    }
}
