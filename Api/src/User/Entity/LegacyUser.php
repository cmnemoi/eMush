<?php

declare(strict_types=1);

namespace Mush\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class LegacyUser
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\GeneratedValue]
    private int $id = 0;

    #[ORM\OneToOne(targetEntity: User::class, mappedBy: 'legacyUser')]
    private User $user;

    #[ORM\OneToOne(targetEntity: LegacyUserTwinoidProfile::class, inversedBy: 'legacyUser')]
    private LegacyUserTwinoidProfile $twinoidProfile;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $characterLevels = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $historyHeroes = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $historyShips = [];

    public function __construct(User $user)
    {
        $this->user = $user;
        $user->setLegacyUser($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTwinoidProfile(): LegacyUserTwinoidProfile
    {
        return $this->twinoidProfile;
    }

    public function setTwinoidProfile(LegacyUserTwinoidProfile $twinoidProfile): void
    {
        $this->twinoidProfile = $twinoidProfile;
        $twinoidProfile->setLegacyUser($this);
    }

    public function getCharacterLevels(): array
    {
        return $this->characterLevels;
    }

    public function setCharacterLevels(array $characterLevels): void
    {
        $this->characterLevels = $characterLevels;
    }

    public function getHistoryHeroes(): array
    {
        return $this->historyHeroes;
    }

    public function setHistoryHeroes(array $historyHeroes): void
    {
        $this->historyHeroes = $historyHeroes;
    }

    public function getHistoryShips(): array
    {
        return $this->historyShips;
    }

    public function setHistoryShips(array $historyShips): void
    {
        $this->historyShips = $historyShips;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'characterLevels' => $this->characterLevels,
            'twinoidProfile' => $this->twinoidProfile->toArray(),
            'historyHeroes' => $this->historyHeroes,
            'historyShips' => $this->historyShips,
        ];
    }
}
