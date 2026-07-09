<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\ClosedPlayer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")',
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER") and (is_granted("DAEDALUS_IS_FINISHED", object) or is_granted("IS_AN_EXPLORATOR", object) or is_granted("IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED", object)))',
        ),
    ],
    normalizationContext: ['groups' => ['closed_exploration_read']],
    paginationItemsPerPage: 25,
)]
#[ORM\Entity]
class ClosedExploration
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[ApiProperty(identifier: false)]
    #[Groups(['closed_exploration_read'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[ApiProperty(identifier: true)]
    #[Groups(['closed_exploration_read'])]
    private string $uuid;

    #[ORM\OneToOne(targetEntity: Exploration::class, mappedBy: 'closedExploration')]
    private ?Exploration $exploration;

    #[ORM\ManyToOne(targetEntity: DaedalusInfo::class, inversedBy: 'closedExplorations')]
    private DaedalusInfo $daedalusInfo;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['closed_exploration_read'])]
    private array $planetName = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    #[Groups(['closed_exploration_read'])]
    private int $startDay = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    #[Groups(['closed_exploration_read'])]
    private int $startCycle = 0;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    #[Groups(['closed_exploration_read'])]
    private array $exploredSectorKeys = [];

    #[ORM\OneToMany(targetEntity: ExplorationLog::class, mappedBy: 'closedExploration')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    #[Groups(['closed_exploration_read'])]
    private Collection $logs;

    #[ORM\ManyToMany(targetEntity: ClosedPlayer::class, inversedBy: 'closedExplorations')]
    #[Groups(['closed_exploration_read'])]
    private Collection $closedExplorators;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isExplorationFinished = false;

    public function __construct(Exploration $exploration)
    {
        $this->exploration = $exploration;
        $this->daedalusInfo = $exploration->getDaedalus()->getDaedalusInfo();
        $this->planetName = $exploration->getPlanet()->getName()->toArray();
        $this->startDay = $exploration->getStartDay();
        $this->startCycle = $exploration->getStartCycle();
        $this->logs = new ArrayCollection();
        $this->uuid = Uuid::v4()->toRfc4122();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
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

    public function getStartDay(): int
    {
        return $this->startDay;
    }

    public function getStartCycle(): int
    {
        return $this->startCycle;
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

    public function setClosedExplorators(array|Collection $closedExplorators): void
    {
        if (\is_array($closedExplorators)) {
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

    #[Groups(['closed_exploration_read'])]
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    #[Groups(['closed_exploration_read'])]
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
