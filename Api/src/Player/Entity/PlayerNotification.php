<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PlayerNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(targetEntity: Player::class, inversedBy: 'notification')]
    private Player $player;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $message = '';

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $parameters = [];

    public function __construct(Player $player, string $message, array $parameters = [])
    {
        $this->player = $player;
        $this->message = $message;
        $this->parameters = $parameters;

        $this->player->updateNotification($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getLanguage(): string
    {
        return $this->player->getLanguage();
    }
}
