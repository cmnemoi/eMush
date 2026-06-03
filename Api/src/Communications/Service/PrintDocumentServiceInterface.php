<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Equipment\Entity\GameEquipment;

interface PrintDocumentServiceInterface
{
    public function execute(
        GameEquipment $printer,
        array $tags,
    ): void;
}
