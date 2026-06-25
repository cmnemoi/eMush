<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    normalizationContext: ['groups' => ['book_read']],
    denormalizationContext: ['groups' => ['book_write']],
    paginationItemsPerPage: 25,
)]
class Book extends Tool
{
    #[ORM\Column(type: 'string', nullable: false, enumType: SkillEnum::class, options: ['default' => SkillEnum::NULL])]
    #[Groups(['book_read', 'book_write'])]
    private SkillEnum $skill = SkillEnum::NULL;

    #[Groups(['book_read'])]
    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::BOOK;

        return $mechanics;
    }

    public function getSkill(): SkillEnum
    {
        return $this->skill;
    }

    /**
     * @return static
     */
    public function setSkill(SkillEnum $skill): self
    {
        $this->skill = $skill;

        return $this;
    }

    public function isMageBook(): bool
    {
        return $this->skill !== SkillEnum::NULL;
    }

    #[Groups(['book_read'])]
    public function getId(): int
    {
        return parent::getId();
    }

    #[Groups(['book_read', 'book_write'])]
    public function getName(): string
    {
        return parent::getName();
    }

    #[Groups(['book_read', 'book_write'])]
    public function getActions(): Collection
    {
        return parent::getActions();
    }
}
