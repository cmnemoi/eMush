<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\ClosedPlayer;

#[ORM\Entity]
class ClosedExploration
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Exploration::class, mappedBy: 'closedExploration')]
    private ?Exploration $exploration;

    #[ORM\ManyToOne(targetEntity: DaedalusInfo::class, inversedBy: 'closedExplorations')]
    private DaedalusInfo $daedalusInfo;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $planetName = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $exploredSectorKeys = [];

    #[ORM\OneToMany(targetEntity: ExplorationLog::class, mappedBy: 'closedExploration')]
    private Collection $logs;

    #[ORM\ManyToMany(targetEntity: ClosedPlayer::class, inversedBy: 'closedExplorations')]
    private Collection $closedExplorators;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isExplorationFinished = false;

    public function __construct(Exploration $exploration)
    {
        $this->exploration = $exploration;
        $this->daedalusInfo = $exploration->getDaedalus()->getDaedalusInfo();
        $this->planetName = $exploration->getPlanet()->getName()->toArray();
        $this->logs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getExploration(): ?Exploration
    {
        return $this->exploration;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function getPlanetName(): array
    {
        return $this->planetName;
    }

    public function getExploredSectorKeys(): array
    {
        return $this->exploredSectorKeys;
    }

    public function addExploredSectorKey(string $exploredSectorKey): void
    {
        $this->exploredSectorKeys[] = $exploredSectorKey;
    }

    public function getLogs(): ExplorationLogCollection
    {
        return new ExplorationLogCollection($this->logs->toArray());
    }

    public function addLog(ExplorationLog $log): void
    {
        $this->logs->add($log);
    }

    public function getClosedExplorators(): Collection
    {
        return new ArrayCollection($this->closedExplorators->toArray());
    }

    public function setClosedExplorators(Collection|array $closedExplorators): void
    {
        if (is_array($closedExplorators)) {
            $closedExplorators = new ArrayCollection($closedExplorators);
        }

        $this->closedExplorators = $closedExplorators;
    }

    public function isExplorationFinished(): bool
    {
        return $this->isExplorationFinished;
    }

    public function finishExploration(): void
    {
        if (!$this->exploration) {
            throw new \LogicException('Exploration should not be null to be finished');
        }

        foreach ($this->exploration->getExplorators() as $explorator) {
            $explorator->setExploration(null);
        }
        $this->exploration->getPlanet()->setExploration(null);
        $this->exploration->getDaedalus()->setExploration(null);
        $this->exploration = null;
        $this->isExplorationFinished = true;
    }
}
