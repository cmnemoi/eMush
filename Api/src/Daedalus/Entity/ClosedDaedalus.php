<?php

namespace Mush\Daedalus\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'daedalus_closed')]
#[ApiResource(
    shortName: 'ClosedDaedalus',
    description: 'eMush Closed Daedalus',
    normalizationContext: ['groups' => ['closed_daedalus_read']],
)]
#[GetCollection(
    paginationEnabled: false,
    security: 'is_granted("ROLE_USER")',
    filters: ['default.search_filter', 'default.order_filter']
)]
#[Get(
    paginationEnabled: false,
    security: 'is_granted("ROLE_USER") and is_granted("DAEDALUS_IS_FINISHED", object)',
)]
class ClosedDaedalus
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['closed_daedalus_read'])]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'closedDaedalus', targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: ClosedPlayer::class)]
    #[Groups(['closed_daedalus_read'])]
    private Collection $players;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['closed_daedalus_read'])]
    private string $endCause = EndCauseEnum::STILL_LIVING;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['closed_daedalus_read'])]
    private int $endDay = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['closed_daedalus_read'])]
    private int $endCycle = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    #[Groups(['closed_daedalus_read'])]
    private int $numberOfHuntersKilled = 0;

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
}
