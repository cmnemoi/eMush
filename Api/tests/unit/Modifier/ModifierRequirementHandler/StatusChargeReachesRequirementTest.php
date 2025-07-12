<?php

declare(strict_types=1);

namespace Mush\tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierRequirementHandler\StatusChargeReachesRequirement;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class StatusChargeReachesRequirementTest extends TestCase
{
    private ModifierActivationRequirement $modifierRequirement;
    private StatusChargeReachesRequirement $statusChargeReachesRequirement;
    private Player $player;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->statusChargeReachesRequirement = new StatusChargeReachesRequirement();
        $this->player = PlayerFactory::createPlayer();
    }

    public function testShouldReturnTrueIfStatusChargeReachesRequirement(): void
    {
        $this->givenModifierRequirementFor5CyclesLyingDownStatus();

        $this->givenPlayerHasLyingDownStatusWithCharge(5);

        $result = $this->whenICheckTheRequirementForPlayer();

        self::assertTrue($result);
    }

    public function testShouldReturnFalseIfStatusChargeDoesNotReachRequirement(): void
    {
        $this->givenModifierRequirementFor5CyclesLyingDownStatus();

        $this->givenPlayerHasLyingDownStatusWithCharge(4);

        $result = $this->whenICheckTheRequirementForPlayer();

        self::assertFalse($result);
    }

    public function testShouldReturnFalseIfEntityDoesNotHaveTheStatus(): void
    {
        $this->givenModifierRequirementFor5CyclesLyingDownStatus();

        $result = $this->whenICheckTheRequirementForPlayer();

        self::assertFalse($result);
    }

    public function testShouldThrowIfEntityIsNotAStatusHolder(): void
    {
        $this->givenModifierRequirementFor5CyclesLyingDownStatus();

        $this->expectException(\RuntimeException::class);

        $this->whenICheckTheRequirementFor(self::createStub(ModifierHolderInterface::class));
    }

    private function givenModifierRequirementFor5CyclesLyingDownStatus(): ModifierActivationRequirement
    {
        $this->modifierRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::STATUS_CHARGE_REACHES);
        $this->modifierRequirement
            ->setActivationRequirement(PlayerStatusEnum::LYING_DOWN)
            ->setValue(5)
            ->buildName();

        return $this->modifierRequirement;
    }

    private function givenPlayerHasLyingDownStatusWithCharge(int $charge): void
    {
        StatusFactory::createChargeStatusFromStatusName(
            name: PlayerStatusEnum::LYING_DOWN,
            holder: $this->player,
            charge: $charge
        );
    }

    private function whenICheckTheRequirementForPlayer(): bool
    {
        return $this->statusChargeReachesRequirement->checkRequirement($this->modifierRequirement, $this->player);
    }

    private function whenICheckTheRequirementFor(object $object): bool
    {
        return $this->statusChargeReachesRequirement->checkRequirement($this->modifierRequirement, $object);
    }

    private function thenRequirementShouldBeVerified(bool $requirementCheck): void
    {
        self::assertTrue($requirementCheck);
    }

    private function thenRequirementShouldNotBeVerified(bool $requirementCheck): void
    {
        self::assertFalse($requirementCheck);
    }
}
