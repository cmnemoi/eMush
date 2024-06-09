<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Repository\ActionConfigRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Repository\ModifierConfigRepository;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CanGoToIcarusBayValidator extends ConstraintValidator
{
    public function __construct(
        private ActionConfigRepository $actionConfigRepository,
        private ModifierConfigRepository $modifierConfigRepository
    ) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }
        if (!$constraint instanceof CanGoToIcarusBay) {
            throw new UnexpectedTypeException($constraint, CanGoToIcarusBay::class);
        }

        /** @var Door $door */
        $door = $value->getTarget();
        $daedalus = $door->getDaedalus();
        $player = $value->getPlayer();
        $icarusBay = $player->getDaedalus()->getPlaceByNameOrThrow(RoomEnum::ICARUS_BAY);

        $icarusBayIsFull = $icarusBay->getNumberOfPlayersAlive() >= $this->getMaxNumberOfPlayersAllowedInIcarusBay($daedalus);
        $playerWantsToGoToIcarusBay = $door->getRooms()->contains($icarusBay) && $player->getPlace() !== $icarusBay;

        if ($icarusBayIsFull && $playerWantsToGoToIcarusBay) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function getMaxNumberOfPlayersAllowedInIcarusBay(Daedalus $daedalus): int
    {
        $takeoffToPlanetActionConfig = $this->getTakeoffToPlanetActionConfig();
        $largeBayUpgradeModifierConfig = $this->getIcarusLargerBayModifierConfig();

        $maxNumberOfPlayersAllowedInIcarusBay = $takeoffToPlanetActionConfig->getOutputQuantity();
        if ($daedalus->hasFinishedProject(ProjectName::ICARUS_LARGER_BAY)) {
            $maxNumberOfPlayersAllowedInIcarusBay += (int) $largeBayUpgradeModifierConfig->getDelta();
        }

        return $maxNumberOfPlayersAllowedInIcarusBay;
    }

    private function getTakeoffToPlanetActionConfig(): ActionConfig
    {
        /** @var ?ActionConfig $actionConfig */
        $actionConfig = $this->actionConfigRepository->findOneBy(
            ['name' => ActionEnum::TAKEOFF_TO_PLANET->value]
        );
        if ($actionConfig === null) {
            throw new \RuntimeException('Takeoff to planet action config not found');
        }

        return $actionConfig;
    }

    private function getIcarusLargerBayModifierConfig(): VariableEventModifierConfig
    {
        /** @var ?VariableEventModifierConfig $largeBayUpgradeModifierConfig */
        $largeBayUpgradeModifierConfig = $this->modifierConfigRepository->findOneBy(
            ['modifierName' => ModifierNameEnum::ICARUS_LARGER_BAY_MODIFIER]
        );
        if ($largeBayUpgradeModifierConfig === null) {
            throw new \RuntimeException('Icarus larger bay modifier config not found');
        }

        return $largeBayUpgradeModifierConfig;
    }
}
