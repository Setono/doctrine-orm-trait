<?php

declare(strict_types=1);

namespace Setono\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

final class ORMTraitTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_returns_entity_manager(): void
    {
        $manager = $this->prophesize(EntityManagerInterface::class);
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($manager->reveal());

        $managerTraitAware = new ConcreteService($managerRegistry->reveal());

        self::assertSame($manager->reveal(), $managerTraitAware->getManagerTest());
    }

    /**
     * @test
     */
    public function it_returns_repository(): void
    {
        $repository = $this->prophesize(EntityRepository::class);

        $manager = $this->prophesize(EntityManagerInterface::class);
        $manager->getRepository(Argument::type('string'))->willReturn($repository->reveal());

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($manager->reveal());

        $managerTraitAware = new ConcreteService($managerRegistry->reveal());

        self::assertSame($repository->reveal(), $managerTraitAware->getRepositoryTest());
    }

    /**
     * @test
     */
    public function it_returns_repository_with_Type(): void
    {
        $repository = $this->prophesize(TestRepository::class);

        $manager = $this->prophesize(EntityManagerInterface::class);
        $manager->getRepository(Argument::type('string'))->willReturn($repository->reveal());

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($manager->reveal());

        $managerTraitAware = new ConcreteService($managerRegistry->reveal());

        self::assertSame($repository->reveal(), $managerTraitAware->getRepositoryWithTypeTest());
    }

    /**
     * @test
     */
    public function it_throws_if_no_manager_exists_for_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn(null);

        $managerTraitAware = new ConcreteService($managerRegistry->reveal());

        $managerTraitAware->getManagerTest();
    }

    /**
     * @test
     */
    public function it_throws_if_manager_is_not_entity_manager(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = $this->prophesize(ObjectManager::class);
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($manager->reveal());

        $managerTraitAware = new ConcreteService($managerRegistry->reveal());

        $managerTraitAware->getManagerTest();
    }

    /**
     * @test
     */
    public function it_throws_if_repository_is_not_the_correct_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $repository = $this->prophesize(EntityRepository::class);

        $manager = $this->prophesize(EntityManagerInterface::class);
        $manager->getRepository(Argument::type('string'))->willReturn($repository->reveal());

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($manager->reveal());

        $managerTraitAware = new ConcreteService($managerRegistry->reveal());
        $managerTraitAware->getRepositoryWithTypeTest();
    }
}

abstract class ManagerTraitAware
{
    use ORMTrait;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
}

final class ConcreteService extends ManagerTraitAware
{
    public function getManagerTest(): EntityManagerInterface
    {
        return $this->getManager(new \stdClass());
    }

    public function getRepositoryTest(): EntityRepository
    {
        return $this->getRepository(new \stdClass());
    }

    public function getRepositoryWithTypeTest(): EntityRepository
    {
        return $this->getRepository(new \stdClass(), TestRepository::class);
    }
}

/**
 * @template T of object
 *
 * @extends EntityRepository<T>
 */
class TestRepository extends EntityRepository
{
}
