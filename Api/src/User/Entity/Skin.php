<?php

namespace Mush\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\User\Enum\RoleEnum;
use Mush\User\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: Skin::class)]
#[ORM\Table(name: 'users')]
class Skin
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetType;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetName;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isPurchasable = false;

    #[ORM\Column(type: 'int', nullable: false)]
    private string $cost;

    public function getId(): int
    {
        return $this->id;
    }


}
