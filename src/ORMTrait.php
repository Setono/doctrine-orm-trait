<?php

declare(strict_types=1);

namespace Setono\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

trait ORMTrait
{
    /** @var array<string, EntityManagerInterface> */
    private array $managers = [];

    private readonly ManagerRegistry $managerRegistry;

    /**
     * @param class-string|object $obj
     *
     * @throws \InvalidArgumentException if no manager exists for the class or if the manager is not an instance of EntityManagerInterface
     */
    protected function getManager(object|string $obj): EntityManagerInterface
    {
        $cls = is_object($obj) ? $obj::class : $obj;

        if (!isset($this->managers[$cls])) {
            $manager = $this->managerRegistry->getManagerForClass($cls);

            if (!$manager instanceof EntityManagerInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected manager to be of type %s, but got %s',
                    EntityManagerInterface::class,
                    null === $manager ? 'null' : $manager::class,
                ));
            }

            $this->managers[$cls] = $manager;
        }

        return $this->managers[$cls];
    }

    /**
     * @template TEntity of object
     * @template TRepository of EntityRepository
     *
     * @param TEntity|class-string<TEntity> $obj
     * @param class-string<TRepository>|null $expectedType
     *
     * @psalm-return ($expectedType is null ? EntityRepository<TEntity> : TRepository)
     */
    protected function getRepository(object|string $obj, string $expectedType = null): EntityRepository
    {
        $cls = is_object($obj) ? $obj::class : $obj;

        $repository = $this->getManager($cls)->getRepository($cls);

        if (null !== $expectedType && !$repository instanceof $expectedType) {
            throw new \InvalidArgumentException(sprintf(
                'Expected repository to be of type %s, but got %s',
                $expectedType,
                $repository::class,
            ));
        }

        return $repository;
    }
}
