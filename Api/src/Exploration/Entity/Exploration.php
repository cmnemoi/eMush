<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;

#[ORM\Entity]
class Exploration
{
    use TimestampableEntity;
    private const CYCLE_LENGTH = 18;

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

        $daedalus = $this->planet->getDaedalus();
        $daedalus->setExploration($this);
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
            static fn (Player $explorator) => !$explorator->hasOperationalEquipmentByName(GearItemEnum::SPACESUIT)
        );
    }

    /**
     * Return the number of explorators who has a skill to extend the exploration with +n steps.
     * Where n is the number of explorators with skills.
     * This method can be chained with more skills later.
     * E.G: Roland with Sprinter.
     */
    public function countSkillExtendingExploration(): int
    {
        return $this->getExplorators()
            ->getPlayersWithSkill(SkillEnum::SPRINTER)
            ->count();
    }

    /**
     * Returns active explorators : alive if there is oxygen on the planet, alive and with spacesuit otherwise.
     */
    public function getActiveExplorators(): PlayerCollection
    {
        if ($this->planet->hasSectorByName(PlanetSectorEnum::OXYGEN)) {
            return $this->getAliveExplorators();
        }

        return $this->getAliveExplorators()->filter(
            static fn (Player $explorator) => $explorator->hasOperationalEquipmentByName(GearItemEnum::SPACESUIT)
        );
    }

    public function getAliveExplorators(): PlayerCollection
    {
        return $this->getExplorators()->filter(static fn (Player $explorator) => $explorator->isAlive());
    }

    public function getNotLostActiveExplorators(): PlayerCollection
    {
        return $this->getActiveExplorators()->filter(static fn (Player $explorator) => !$explorator->hasStatus(PlayerStatusEnum::LOST));
    }

    public function getNotLostAliveExplorators(): PlayerCollection
    {
        return $this->getAliveExplorators()->filter(static fn (Player $explorator) => !$explorator->hasStatus(PlayerStatusEnum::LOST));
    }

    public function getNotLostExplorators(): PlayerCollection
    {
        return $this->getExplorators()->filter(static fn (Player $explorator) => !$explorator->hasStatus(PlayerStatusEnum::LOST));
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

    public function incrementCycle(): void
    {
        ++$this->cycle;
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
        $cycleLength = (int) ($this->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCycleLength() / self::CYCLE_LENGTH);

        return max($cycleLength, 1);
    }

    public function isFinished(): bool
    {
        return $this->closedExploration->isExplorationFinished();
    }

    public function isAnyExploratorAlive(): bool
    {
        return $this->getExplorators()->getPlayerAlive()->count() > 0;
    }

    public function allExploratorsAreDeadOrLost(): bool
    {
        return $this
            ->getAliveExplorators()
            ->filter(static fn (Player $player) => $player->doesNotHaveStatus(PlayerStatusEnum::LOST))
            ->isEmpty();
    }

    public function hasAPilotAlive(): bool
    {
        return $this->getAliveExplorators()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::PILOT))->count() > 0;
    }

    public function getEstimatedDuration(): int
    {
        return $this->getCycleLength() * min($this->numberOfSectionsToVisit - $this->cycle + 1, $this->planet->getUnvisitedSectors()->count());
    }

    public function hasAFunctionalDrill(): bool
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasOperationalEquipmentByName(ItemEnum::DRILL))->count() > 0;
    }

    public function hasAFunctionalCompass(): bool
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasEquipmentByName(ItemEnum::QUADRIMETRIC_COMPASS))->count() > 0;
    }

    public function hasAWhiteFlag(): bool
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasOperationalEquipmentByName(ItemEnum::WHITE_FLAG))->count() > 0;
    }

    public function hasAFunctionalBabelModule(): bool
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasOperationalEquipmentByName(ItemEnum::BABEL_MODULE))->count() > 0;
    }

    public function hasAFunctionalEcholocator(): bool
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasOperationalEquipmentByName(ItemEnum::ECHOLOCATOR))->count() > 0;
    }

    public function hasAFunctionalThermosensor(): bool
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasOperationalEquipmentByName(ItemEnum::THERMOSENSOR))->count() > 0;
    }

    public function getNumberOfActiveSurvivalists(): int
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::SURVIVALIST))->count();
    }

    public function getActiveNonSurvivalistExplorators(): PlayerCollection
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::SURVIVALIST) === false);
    }

    public function getNumberOfActiveBotanists(): int
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::BOTANIST))->count();
    }

    public function hasAnActiveDiplomat(): bool
    {
        return $this->getNotLostActiveExplorators()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::DIPLOMAT))->count() > 0;
    }

    public function hasATraitor(): bool
    {
        return $this->getAliveExplorators()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::TRAITOR))->count() > 0;
    }

    public function getTraitors(): PlayerCollection
    {
        return $this->getAliveExplorators()->getPlayersWithSkill(SkillEnum::TRAITOR);
    }
}
