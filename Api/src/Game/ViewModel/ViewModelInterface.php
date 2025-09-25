<?php

declare(strict_types=1);

namespace Mush\Game\ViewModel;

interface ViewModelInterface
{
    public static function fromQueryRow(array $row): self;
}
