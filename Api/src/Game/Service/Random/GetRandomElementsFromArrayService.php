<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

use Doctrine\Common\Collections\ArrayCollection;

final readonly class GetRandomElementsFromArrayService implements GetRandomElementsFromArrayServiceInterface
{
    public function __construct(private GetRandomIntegerServiceInterface $getRandomIntegerService) {}

    public function execute(array $elements, int $number): ArrayCollection
    {
        if (empty($elements)) {
            return new ArrayCollection();
        }
        if ($number > \count($elements)) {
            $number = \count($elements);
        }

        $result = [];
        for ($i = 0; $i < $number; ++$i) {
            $keysNotPicked = array_values(array_diff(array_keys($elements), array_keys($result)));

            $key = $keysNotPicked[$this->getRandomInteger(0, \count($keysNotPicked) - 1)];
            $result[$key] = $elements[$key];
        }

        return new ArrayCollection($result);
    }

    private function getRandomInteger(int $min, int $max): int
    {
        return $this->getRandomIntegerService->execute($min, $max);
    }
}
