<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
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

    #[ORM\OneToOne(targetEntity: Planet::class)]
    private Planet $planet;

    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'exploration')]
    private Collection $explorators;

    #[ORM\OneToMany(targetEntity: ExplorationLog::class, mappedBy: 'exploration', cascade: ['remove'])]
    private Collection $logs;

    public function __construct(Planet $planet)
    {
        $this->planet = $planet;
        $planet->setExploration($this);
        $this->explorators = new ArrayCollection();
        $this->logs = new ArrayCollection();
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

    public function addExplorator(Player $explorator): void
    {   
        $explorator->setExploration($this);
        $this->explorators->add($explorator);
    }

    public function getLogs(): ExplorationLogCollection
    {
        return new ExplorationLogCollection($this->logs->toArray());
    }

    public function addLog(ExplorationLog $log): void
    {
        $this->logs->add($log);
    }

    public function getDaedalus(): Daedalus
    {
        return $this->planet->getPlayer()->getDaedalus();
    }
}
