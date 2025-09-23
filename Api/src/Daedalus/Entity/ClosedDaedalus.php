<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Enum\EndCauseEnum;

#[ORM\Entity]
#[ORM\Table(name: 'daedalus_closed')]
class ClosedDaedalus
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'closedDaedalus', targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\OneToMany(mappedBy: 'closedDaedalus', targetEntity: ClosedPlayer::class)]
    private Collection $players;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $endCause = EndCauseEnum::STILL_LIVING;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $endDay = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $endCycle = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $numberOfHuntersKilled = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $finishedAt = null;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => -1])]
    private int $humanTriumphSum = -1;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => -1])]
    private int $mushTriumphSum = -1;

    public function getId(): int
    {
        return $this->id;
    }

    public function isDaedalusFinished(): bool
    {
        return $this->daedalusInfo->isDaedalusFinished();
    }

    public function getPlayers(): ArrayCollection
    {
        return new PlayerCollection($this->players->toArray());
    }

    public function addPlayer(ClosedPlayer $player): static
    {
        if (!$this->getPlayers()->contains($player)) {
            $this->players->add($player);

            $player->setClosedDaedalus($this);
        }

        return $this;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalusInfo(DaedalusInfo $daedalusInfo): static
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->daedalusInfo->getLanguage();
    }

    public function getEndCycle(): int
    {
        return $this->endCycle;
    }

    public function getEndDay(): int
    {
        return $this->endDay;
    }

    public function getEndCause(): string
    {
        return $this->endCause;
    }

    public function updateEnd(Daedalus $daedalus, string $cause): static
    {
        $this->endDay = $daedalus->getDay();
        $this->endCycle = $daedalus->getCycle();
        $this->endCause = $cause;

        return $this;
    }

    public function setEndCause(string $endCause): static
    {
        $this->endCause = $endCause;

        return $this;
    }

    public function getNumberOfHuntersKilled(): int
    {
        return $this->numberOfHuntersKilled;
    }

    public function incrementNumberOfHuntersKilled(): static
    {
        ++$this->numberOfHuntersKilled;

        return $this;
    }

    public function getFinishedAtOrThrow(): \DateTime
    {
        if ($this->finishedAt === null) {
            throw new \RuntimeException("Closed daedalus {$this->id} should have a non null finishedAt attribute");
        }

        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTime $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCreatedAtOrThrow(): \DateTime
    {
        if ($this->createdAt === null) {
            throw new \RuntimeException("Closed daedalus {$this->id} should have a non null createdAt attribute");
        }

        return $this->createdAt;
    }

    public function getHumanTriumphSum(): int
    {
        if ($this->humanTriumphSum < 0) {
            $this->humanTriumphSum = $this->players->reduce(
                static function (int $carry, ClosedPlayer $player) {
                    return $carry + ($player->isMush() ? 0 : $player->getTriumph());
                },
                0
            );
        }

        return $this->humanTriumphSum;
    }

    public function setHumanTriumphSum(int $humanTriumphSum): static
    {
        $this->humanTriumphSum = $humanTriumphSum;

        return $this;
    }

    public function getMushTriumphSum(): int
    {
        if ($this->mushTriumphSum < 0) {
            $this->mushTriumphSum = $this->players->reduce(
                static function (int $carry, ClosedPlayer $player) {
                    return $carry + ($player->isMush() ? $player->getTriumph() : 0);
                },
                0
            );
        }

        return $this->mushTriumphSum;
    }

    public function setMushTriumphSum(int $mushTriumphSum): static
    {
        $this->mushTriumphSum = $mushTriumphSum;

        return $this;
    }
}
