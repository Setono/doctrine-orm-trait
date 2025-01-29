<?php

declare(strict_types=1);

namespace Setono\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

trait ORMTrait
{
    /** @var array<string, EntityManagerInterface> */
    private array $managers = [];

    private readonly ManagerRegistry $managerRegistry;

    /**
     * @param class-string|object|null $obj if null, the default entity manager will be returned
     *
     * @throws \InvalidArgumentException if $obj is not given and more than one manager exists
     * @throws \InvalidArgumentException if $obj is given and no manager exists for the class or if the manager is not an instance of EntityManagerInterface
     */
    protected function getManager(object|string $obj = null): EntityManagerInterface
    {
        $obj = is_object($obj) ? $obj::class : (string) $obj;

        if (!isset($this->managers[$obj])) {
            if ('' === $obj) {
                if (count($this->managerRegistry->getManagerNames()) > 1) {
                    throw new \InvalidArgumentException('More than one manager found. Please specify the class name');
                }

                $manager = $this->managerRegistry->getManager();
            } else {
                $manager = $this->managerRegistry->getManagerForClass($obj);
            }

            if (!$manager instanceof EntityManagerInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected manager to be of type %s, but got %s',
                    EntityManagerInterface::class,
                    null === $manager ? 'null' : $manager::class,
                ));
            }

            $this->managers[$obj] = $manager;
        }

        return $this->managers[$obj];
    }

    /**
     * @template TEntity of object
     * @template TRepository of ObjectRepository
     *
     * @param TEntity|class-string<TEntity> $obj
     * @param class-string<TRepository>|null $expectedType
     *
     * @return ($expectedType is null ? EntityRepository<TEntity> : TRepository&EntityRepository<TEntity>)
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
