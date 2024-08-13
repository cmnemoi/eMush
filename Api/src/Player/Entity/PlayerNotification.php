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

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'notifications')]
    private Player $player;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $message = '';

    public function __construct(Player $player, string $message)
    {
        $this->player = $player;
        $this->message = $message;

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

    public function getLanguage(): string
    {
        return $this->player->getLanguage();
    }
}
