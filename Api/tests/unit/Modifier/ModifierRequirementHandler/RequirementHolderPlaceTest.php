<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\ModifierRequirementHandler\RequirementHolderPlace;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RequirementHolderPlaceTest extends TestCase
{
    private RequirementHolderPlace $handler;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->handler = new RequirementHolderPlace();
    }

    public function testShouldReturnTrueWhenPlayerIsInThePlace(): void
    {
        $player = $this->givenAPlayerInPlace(RoomEnum::LABORATORY);

        $requirement = $this->givenHolderPlaceModifierRequirementForLaboratory();

        $result = $this->whenCheckingIfRequirementIsMet($requirement, $player);

        $this->thenRequirementShouldBeMet($result);
    }

    public function testShouldReturnFalseWhenPlayerIsNotInThePlace(): void
    {
        $player = $this->givenAPlayerInPlace(RoomEnum::FRONT_ALPHA_TURRET);

        $requirement = $this->givenHolderPlaceModifierRequirementForLaboratory();

        $result = $this->whenCheckingIfRequirementIsMet($requirement, $player);

        $this->thenRequirementShouldNotBeMet($result);
    }

    public function testShouldReturnTrueWhenGameEquipmentIsInPlace(): void
    {
        $gameEquipment = $this->givenAnEquipmentInPlace(RoomEnum::LABORATORY);

        $requirement = $this->givenHolderPlaceModifierRequirementForLaboratory();

        $result = $this->whenCheckingIfRequirementIsMet($requirement, $gameEquipment);

        $this->thenRequirementShouldBeMet($result);
    }

    public function testShouldReturnFalseWhenGameEquipmentIsNotInPlace(): void
    {
        $gameEquipment = $this->givenAnEquipmentInPlace(RoomEnum::FRONT_ALPHA_TURRET);

        $requirement = $this->givenHolderPlaceModifierRequirementForLaboratory();

        $result = $this->whenCheckingIfRequirementIsMet($requirement, $gameEquipment);

        self::assertFalse($result);
    }

    public function testShouldThrowExceptionWhenCalledForPlace(): void
    {
        // given a place
        $place = Place::createByName(RoomEnum::LABORATORY);

        $requirement = $this->givenHolderPlaceModifierRequirementForLaboratory();

        $this->expectException(\LogicException::class);
        $this->whenCheckingIfRequirementIsMet($requirement, $place);
    }

    public function testShouldThrowExceptionWhenCalledForDaedalus(): void
    {
        // given a daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        $requirement = $this->givenHolderPlaceModifierRequirementForLaboratory();

        $this->expectException(\LogicException::class);
        $this->whenCheckingIfRequirementIsMet($requirement, $daedalus);
    }

    private function givenAPlayerInPlace(string $placeName): Player
    {
        $player = PlayerFactory::createPlayer();
        $player->setPlace(Place::createByName($placeName));

        return $player;
    }

    private function givenAnEquipmentInPlace(string $placeName): GameEquipment
    {
        return GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::null, Place::createByName($placeName));
    }

    private function givenHolderPlaceModifierRequirementForLaboratory(): ModifierActivationRequirement
    {
        $modifierRequirement = new ModifierActivationRequirement('holder_place');
        $modifierRequirement
            ->setActivationRequirement(RoomEnum::LABORATORY)
            ->buildName();

        return $modifierRequirement;
    }

    private function whenCheckingIfRequirementIsMet(ModifierActivationRequirement $modifierRequirement, ModifierHolderInterface $entity): bool
    {
        return $this->handler->checkRequirement($modifierRequirement, $entity);
    }

    private function thenRequirementShouldBeMet(bool $result): void
    {
        self::assertTrue($result);
    }

    private function thenRequirementShouldNotBeMet(bool $result): void
    {
        self::assertFalse($result);
    }
}
