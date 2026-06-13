<?php

declare(strict_types=1);

namespace Mush\Player\Entity\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePersonalNotesTabsRequest
{
    #[Assert\NotNull(message: 'Tabs array is required')]
    #[Assert\Type('array')]
    #[Assert\Count(max: 16, maxMessage: 'Maximum number of tabs (16) exceeded')]
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'id' => new Assert\Optional([new Assert\Type('integer')]),
                'index' => new Assert\Required([
                    new Assert\NotNull(),
                    new Assert\Type('integer'),
                    new Assert\PositiveOrZero(message: 'Order must be a positive integer'),
                ]),
                'icon' => new Assert\Required([new Assert\Type('string')]),
                'content' => new Assert\Required([
                    new Assert\Type('string'),
                    new Assert\Length(max: 65536, maxMessage: 'Maximum length of content ({{ limit }}) exceeded'),
                ]),
            ],
            allowExtraFields: true
        ),
    ])]
    private array $tabs = [];

    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function setTabs(array $tabs): self
    {
        $this->tabs = $tabs;

        return $this;
    }
}
