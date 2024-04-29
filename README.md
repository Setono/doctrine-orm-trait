# Doctrine ORM Trait

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Mutation testing][ico-infection]][link-infection]

If you are like and me and usually [don't inject entity managers directly](https://matthiasnoback.nl/2014/05/inject-the-manager-registry-instead-of-the-entity-manager/),
but inject the manager registry instead then this little library will come in handy.

## Installation

```bash
composer require setono/doctrine-orm-trait
```

## Usage

```php
<?php
use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;

final class YourClass
{
    /**
     * Include this trait to use the getManager() and getRepository() methods below
     */
    use ORMTrait;
    
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    
    public function someMethod(): void
    {
        /**
         * $entity<T> is an entity managed by Doctrine or a class-string representing an entity managed by Doctrine
         */
        $entity = ;
        
        /** @var \Doctrine\ORM\EntityRepository<T> $repository */
        $repository = $this->getRepository($entity);
        
        /**
         * @var \Doctrine\ORM\EntityManagerInterface $manager 
         */
        $manager = $this->getManager($entity);
        
        $manager->persist($entity);
        $manager->flush();
    }
}
```

[ico-version]: https://poser.pugx.org/setono/doctrine-orm-trait/v/stable
[ico-license]: https://poser.pugx.org/setono/doctrine-orm-trait/license
[ico-github-actions]: https://github.com/Setono/doctrine-orm-trait/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/doctrine-orm-trait/graph/badge.svg
[ico-infection]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fdoctrine-orm-trait%2Fmaster

[link-packagist]: https://packagist.org/packages/setono/doctrine-orm-trait
[link-github-actions]: https://github.com/Setono/doctrine-orm-trait/actions
[link-code-coverage]: https://codecov.io/gh/Setono/doctrine-orm-trait
[link-infection]: https://dashboard.stryker-mutator.io/reports/github.com/Setono/doctrine-orm-trait/master
