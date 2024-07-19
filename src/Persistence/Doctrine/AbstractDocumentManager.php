<?php

declare(strict_types=1);

namespace SharedBundle\Persistence\Doctrine;

use Doctrine\Common\Collections\Selectable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use InvalidArgumentException;
use Shared\Criteria;
use SharedBundle\Criteria\CriteriaConverterException;

abstract readonly class AbstractDocumentManager
{
    public function __construct(
        private DocumentManager $manager,
        private DocumentRepository&Selectable $repository
    ) {
    }

    /**
     * @return object[]
     *
     * @throws CriteriaConverterException
     */
    final protected function search(
        Criteria\AndX|Criteria\OrX|null $criteria = null,
        ?Criteria\OrderX $sort = null,
        ?int $offset = null,
        ?int $limit = null
    ): array {
        $criteria = DoctrineCriteriaConverter::convert($criteria, $sort, $offset, $limit);

        return $this->repository
            ->matching($criteria)
            ->toArray();
    }

    /**
     * @throws CriteriaConverterException
     */
    final protected function count(Criteria\AndX|Criteria\OrX|null $criteria = null): int
    {
        $criteria = DoctrineCriteriaConverter::convert($criteria);

        return $this->repository
            ->matching($criteria)
            ->count();
    }

    /**
     * @throws InvalidArgumentException
     * @throws MongoDBException
     */
    final protected function register(object $model): void
    {
        $this->manager->persist($model);
        $this->apply();
    }

    /**
     * @throws InvalidArgumentException
     * @throws MongoDBException
     */
    final protected function unregister(object $model): void
    {
        $this->manager->remove($model);
        $this->apply();
    }

    /**
     * @throws MongoDBException
     */
    private function apply(): void
    {
        $this->manager->flush();
    }
}
