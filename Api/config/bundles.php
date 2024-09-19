<?php

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use FOS\RestBundle\FOSRestBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    FrameworkBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    FOSRestBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    LexikJWTAuthenticationBundle::class => ['all' => true],
    NelmioApiDocBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    MonologBundle::class => ['all' => true],
    NelmioCorsBundle::class => ['all' => true],
    ApiPlatformBundle::class => ['all' => true],
    SensioFrameworkExtraBundle::class => ['all' => true],
];
