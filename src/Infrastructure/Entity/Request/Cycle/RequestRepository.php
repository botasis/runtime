<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\Request\Cycle;

use Cycle\ORM\ORM;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Transaction;
use DateTimeZone;
use Viktorprogger\TelegramBot\Domain\Entity\Request\RequestId;
use Viktorprogger\TelegramBot\Domain\Entity\Request\RequestRepositoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;

final class RequestRepository implements RequestRepositoryInterface
{
    private RepositoryInterface $repository;

    public function __construct(private readonly ORM $orm)
    {
        $this->repository = $this->orm->getRepository(RequestEntity::class);
    }

    /**
     * @inheritDoc
     */
    public function create(TelegramRequest $request): void
    {
        $entity = new RequestEntity();
        $entity->id = $request->id->value;
        $entity->contents = json_encode($request->raw, JSON_THROW_ON_ERROR);
        $entity->created_at = new \DateTimeImmutable(timezone: new DateTimeZone('UTC'));
        (new Transaction($this->orm))->persist($entity)->run();
    }

    /**
     * @inheritDoc
     */
    public function find(RequestId $id): ?TelegramRequest
    {
        // TODO: Implement find() method.
    }

    /**
     * @inheritDoc
     */
    public function getBiggestId(): ?RequestId
    {
        return null;
    }
}
