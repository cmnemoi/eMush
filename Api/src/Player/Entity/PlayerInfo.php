<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\PlayerStatistics;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\User\Entity\User;

#[ORM\Entity(repositoryClass: PlayerInfoRepository::class)]
#[ORM\UniqueConstraint(
    columns: ['user_id', 'character_config_id', 'player_id']
)]
class PlayerInfo
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'playerInfo', targetEntity: Player::class, cascade: ['ALL'])]
    private ?Player $player;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $gameStatus;

    #[ORM\ManyToOne(targetEntity: CharacterConfig::class)]
    private CharacterConfig $characterConfig;

    #[ORM\OneToOne(inversedBy: 'playerInfo', targetEntity: ClosedPlayer::class, cascade: ['ALL'])]
    private ClosedPlayer $closedPlayer;

    #[ORM\Embedded(class: PlayerStatistics::class)]
    private PlayerStatistics $playerStatistics;

    public function __construct(
        Player $player,
        User $user,
        CharacterConfig $characterConfig
    ) {
        $this->player = $player;
        $this->user = $user;
        $this->characterConfig = $characterConfig;
        $this->closedPlayer = new ClosedPlayer();
        $this->playerStatistics = new PlayerStatistics();
        $this->gameStatus = GameStatusEnum::CURRENT;

        $this->player->setPlayerInfo($this);
        $this->closedPlayer->setPlayerInfo($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function deletePlayer(): static
    {
        $this->player = null;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->characterConfig->getCharacterName();
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function isAlive(): bool
    {
        return $this->gameStatus === GameStatusEnum::CURRENT;
    }

    public function setGameStatus(string $gameStatus): static
    {
        $this->gameStatus = $gameStatus;

        return $this;
    }

    public function getCharacterConfig(): CharacterConfig
    {
        return $this->characterConfig;
    }

    public function getClosedPlayer(): ClosedPlayer
    {
        return $this->closedPlayer;
    }

    public function getStatistics(): PlayerStatistics
    {
        return $this->playerStatistics;
    }

    public function getDaedalusId(): int
    {
        if ($this->player) {
            return $this->player->getDaedalus()->getId();
        }

        return $this->closedPlayer->getClosedDaedalus()->getId();
    }

    public function getDaedalusName(): string
    {
        if ($this->player) {
            return $this->player->getDaedalus()->getName();
        }

        return $this->closedPlayer->getClosedDaedalus()->getDaedalusInfo()->getName();
    }

    public function updateUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isDead(): bool
    {
        return $this->isAlive() === false;
    }

    /**
     * @return PlayerHighlight[]
     */
    public function getPlayerHighlights(): array
    {
        return $this->closedPlayer->getPlayerHighlights();
    }

    public function addPlayerHighlight(PlayerHighlight $playerHighlight): static
    {
        $this->closedPlayer->addPlayerHighlight($playerHighlight);

        return $this;
    }
}
