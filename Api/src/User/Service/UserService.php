<?php

namespace Mush\User\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\User\Entity\User;
use Mush\User\Repository\UserRepository;

class UserService implements UserServiceInterface
{
    private EntityManagerInterface $entityManager;

    private UserRepository $repository;

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
        $user = $this->repository->find($id);

        return $user instanceof User ? $user : null;
    }

    public function findUsers(int $page, int $size, array $sort): array
    {
        return $this->repository->findBy([], $sort, $size, $page);
    }

    public function findUserByUserId(string $userId): ?User
    {
        return $this->repository->loadUserByUsername($userId);
    }

    public function findUserByNonceCode(string $nonceCode): ?User
    {
        $user = $this->repository->findOneBy(['nonceCode' => $nonceCode]);

        return $user instanceof User ? $user : null;
    }

    public function createUser(string $userId, string $username): User
    {
        $user = new User();
        $user
            ->setUserId($userId)
            ->setUsername($username)
        ;

        $this->persist($user);

        return $user;
    }

    public function findUserDaedaluses(User $user): array
    {
        return $this->repository->findUserDaedaluses($user);
    }
}
