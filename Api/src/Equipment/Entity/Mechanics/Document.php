<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
#[ApiResource(
    paginationItemsPerPage: 25,
    normalizationContext: ['groups' => ['document_read']],
    denormalizationContext: ['groups' => ['document_write']],
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
)]
class Document extends Tool
{
    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $content = '';

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isTranslated = false;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $canShred = false;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::DOCUMENT;

        return $mechanics;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isTranslated(): bool
    {
        return $this->isTranslated;
    }

    public function setIsTranslated(bool $isTranslated): self
    {
        $this->isTranslated = $isTranslated;

        return $this;
    }

    public function canShred(): bool
    {
        return $this->canShred;
    }

    public function setCanShred(bool $canShred): self
    {
        $this->canShred = $canShred;

        return $this;
    }
}
