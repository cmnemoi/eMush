<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AllPlanetSectorsRevealed;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AnalyzePlanet extends AbstractAction
{
    protected string $name = ActionEnum::ANALYZE_PLANET;
    private PlanetServiceInterface $planetService;
    private RandomServiceInterface $randomService;
    private Planet $planet;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlanetServiceInterface $planetService,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
        $this->planetService = $planetService;
        $this->randomService = $randomService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Planet;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new AllPlanetSectorsRevealed(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::DIRTY,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
        ]));
        // TODO : check that astro terminal is not broken
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $sectorsToReveal = $this->getSectorsToReveal();

        $sectorsToReveal->map(fn (PlanetSector $sector) => $sector->reveal());

        $this->planetService->persist($sectorsToReveal->toArray());
    }

    private function getSectorsToRevealProbaCollection(Planet $planet): ProbaCollection
    {
        $probaCollection = new ProbaCollection();
        foreach ($planet->getUnrevealedSectors() as $sector) {
            $probaCollection->setElementProbability($sector->getId(), $sector->getWeightAtPlanetAnalysis());
        }

        return $probaCollection;
    }

    private function getSectorsToReveal(): ArrayCollection
    {
        /** @var Planet $planet */
        $planet = $this->target;

        $sectorIdsToReveal = $this->randomService->getRandomElementsFromProbaCollection(
            array: $this->getSectorsToRevealProbaCollection($planet),
            number: $this->getOutputQuantity(),
        );

        /** @var ArrayCollection<int, PlanetSector> $sectorsToReveal */
        $sectorsToReveal = new ArrayCollection();
        foreach ($sectorIdsToReveal as $sectorId) {
            $sector = $this->planetService->findPlanetSectorById($sectorId);
            if (!$sector) {
                throw new \RuntimeException("Sector $sectorId not found on planet {$this->planet->getId()}");
            }
            $sectorsToReveal->add($sector);
        }

        return $sectorsToReveal;
    }
}
