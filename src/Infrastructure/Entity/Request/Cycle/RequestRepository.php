<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\Request\Cycle;

use Cycle\ORM\ORM;
use Cycle\ORM\Select\Repository;
use Cycle\ORM\Transaction;
use DateTimeImmutable;
use DateTimeZone;
use Viktorprogger\TelegramBot\Domain\Entity\Request\RequestId;
use Viktorprogger\TelegramBot\Domain\Entity\Request\RequestRepositoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequestFactory;

class RequestRepository implements RequestRepositoryInterface
{
    private Repository $repository;

    public function __construct(private readonly ORM $orm, private readonly TelegramRequestFactory $requestFactory)
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        /** @psalm-suppress PropertyTypeCoercion */
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
        $entity->created_at = new DateTimeImmutable(timezone: new DateTimeZone('UTC'));
        (new Transaction($this->orm))->persist($entity)->run();
    }

    /**
     * @inheritDoc
     */
    public function find(RequestId $id): ?TelegramRequest
    {
        /** @var RequestEntity|null $entity */
        $entity = $this->repository->findByPK($id->value);
        if ($entity === null) {
            return null;
        }

        return $this->requestFactory->create(json_decode($entity->contents, true, flags: JSON_THROW_ON_ERROR));
    }

    /**
     * @inheritDoc
     */
    public function getBiggestId(): ?RequestId
    {
        /** @var RequestEntity|null $entity */
        $entity = $this->repository->select()
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->fetchOne();

        if ($entity === null) {
            return null;
        }

        return new RequestId($entity->id);
    }
}
