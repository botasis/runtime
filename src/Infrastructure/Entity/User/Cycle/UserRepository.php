<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\User\Cycle;

use Cycle\ORM\ORM;
use Cycle\ORM\Select\Repository;
use Cycle\ORM\Transaction;
use Viktorprogger\TelegramBot\Domain\Entity\User\User;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserId;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private readonly Repository $cycleRepository;

    public function __construct(private readonly ORM $orm)
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->cycleRepository = $orm->getRepository(UserEntity::class);
    }

    public function exists(UserId $id): bool
    {
        return $this->cycleRepository->findByPK($id->value) !== null;
    }

    public function create(User $user): void
    {
        $entity = new UserEntity($user->id->value);
        (new Transaction($this->orm))->persist($entity)->run();
    }
}
