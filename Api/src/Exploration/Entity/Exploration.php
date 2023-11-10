<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class Exploration
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: ClosedExploration::class, inversedBy: 'exploration', cascade: ['persist'])]
    private ClosedExploration $closedExploration;

    #[ORM\OneToOne(targetEntity: Planet::class)]
    private Planet $planet;

    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'exploration')]
    private Collection $explorators;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $numberOfSectionsToVisit = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $startPlaceName = '';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $shipUsedName = '';

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycle = 0;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isChangingCycle = false;

    public function __construct(Planet $planet)
    {
        $this->planet = $planet;
        $planet->setExploration($this);

        $this->explorators = new ArrayCollection();

        $this->closedExploration = new ClosedExploration($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlanet(): Planet
    {
        return $this->planet;
    }

    public function getExplorators(): PlayerCollection
    {
        return new PlayerCollection($this->explorators->toArray());
    }

    public function setExplorators(PlayerCollection $explorators): void
    {
        foreach ($explorators as $explorator) {
            $explorator->setExploration($this);
        }

        $this->explorators = $explorators;
    }

    public function getExploratorsWithoutSpacesuit(): PlayerCollection
    {
        return $this->getExplorators()->filter(
            fn (Player $explorator) => !$explorator->hasOperationalEquipmentByName(GearItemEnum::SPACESUIT)
        );
    }

    /**
     * Returns active explorators : alive if there is oxygen on the planet, alive and with spacesuit otherwise.
     */
    public function getActiveExplorators(): PlayerCollection
    {
        if ($this->planet->hasSectorByName(PlanetSectorEnum::OXYGEN)) {
            return $this->getExplorators()->filter(
                fn (Player $explorator) => $explorator->isAlive()
            );
        }

        return $this->getExplorators()->filter(
            fn (Player $explorator) => $explorator->isAlive() && $explorator->hasOperationalEquipmentByName(GearItemEnum::SPACESUIT)
        );
    }

    public function addExplorator(Player $explorator): void
    {
        $explorator->setExploration($this);
        $this->explorators->add($explorator);
    }

    public function getClosedExploration(): ClosedExploration
    {
        return $this->closedExploration;
    }

    public function getNumberOfSectionsToVisit(): int
    {
        return $this->numberOfSectionsToVisit;
    }

    public function setNumberOfSectionsToVisit(int $numberOfSectionsToVisit): void
    {
        $this->numberOfSectionsToVisit = $numberOfSectionsToVisit;
    }

    public function getStartPlaceName(): string
    {
        return $this->startPlaceName;
    }

    public function setStartPlaceName(string $startPlaceName): void
    {
        $this->startPlaceName = $startPlaceName;
    }

    public function getShipUsedName(): string
    {
        return $this->shipUsedName;
    }

    public function setShipUsedName(string $shipUsedName): void
    {
        $this->shipUsedName = $shipUsedName;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): void
    {
        $this->cycle = $cycle;
    }

    public function isChangingCycle(): bool
    {
        return $this->isChangingCycle;
    }

    public function setIsChangingCycle(bool $isChangingCycle): void
    {
        $this->isChangingCycle = $isChangingCycle;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->planet->getPlayer()->getDaedalus();
    }

    public function getCycleLength(): int
    {
        return intval($this->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCycleLength() / 18);
    }
}
