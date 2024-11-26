<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class RandomString implements RandomStringInterface
{
    public function __construct(private GetRandomIntegerServiceInterface $getRandomInteger) {}

    public function generate(int $minLength, int $maxLength): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        $length = $this->getRandomInteger->execute($minLength, $maxLength);

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[$this->getRandomInteger->execute(0, \strlen($characters) - 1)];
        }

        return $randomString;
    }
}
