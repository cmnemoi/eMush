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

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $availableExperience = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $skins = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $flairs = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private int $klix = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $experienceResetKlixCost = 0;

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

    public function getAvailableExperience(): int
    {
        return $this->availableExperience;
    }

    public function setAvailableExperience(int $availableExperience): void
    {
        $this->availableExperience = $availableExperience;
    }

    public function getCharacterLevels(): array
    {
        return $this->characterLevels;
    }

    public function setCharacterLevels(array $characterLevels): void
    {
        $this->characterLevels = $characterLevels;
    }

    public function getSkins(): array
    {
        return $this->skins;
    }

    public function setSkins(array $skins): void
    {
        $this->skins = $skins;
    }

    public function getFlairs(): array
    {
        return $this->flairs;
    }

    public function setFlairs(array $flairs): void
    {
        $this->flairs = $flairs;
    }

    public function getKlix(): int
    {
        return $this->klix;
    }

    public function setKlix(int $klix): void
    {
        $this->klix = $klix;
    }

    public function getExperienceResetKlixCost(): int
    {
        return $this->experienceResetKlixCost;
    }

    public function setExperienceResetKlixCost(int $experienceResetKlixCost): void
    {
        $this->experienceResetKlixCost = $experienceResetKlixCost;
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
}
