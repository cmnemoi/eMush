<?php

namespace Mush\User\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\User\Entity\User;
use Mush\User\Repository\UserRepository;

class UserService implements UserServiceInterface
{
    private EntityManagerInterface $entityManager;

    private UserRepository $repository;

    /**
     * UserService constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $repository
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function persist(User $user): User
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function findById(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function findUserByUserId(string $userId): ?User
    {
        return $this->repository->loadUserByUsername($userId);
    }
}