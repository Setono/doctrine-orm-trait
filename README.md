# Doctrine ORM Trait

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Mutation testing][ico-infection]][link-infection]

A tiny, dependency-light trait that gives your services convenient, type-safe access to Doctrine's
entity managers and repositories.

It's built for the common pattern of [injecting the `ManagerRegistry` instead of an `EntityManager`
directly](https://matthiasnoback.nl/2014/05/inject-the-manager-registry-instead-of-the-entity-manager/):
once you have the registry, this trait does the work of resolving the right manager and repository for
a given entity — including the static type juggling — so you don't have to repeat it in every service.

## Installation

```bash
composer require setono/doctrine-orm-trait
```

## Usage

Use the trait and assign the injected `ManagerRegistry` to the `$managerRegistry` property in your
constructor. That's the only wiring required:

```php
<?php

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;

final class OrderProcessor
{
    use ORMTrait;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function process(Order $order): void
    {
        $manager = $this->getManager($order);

        $manager->persist($order);
        $manager->flush();
    }
}
```

The trait exposes two `protected` methods.

### `getManager()` — resolve the entity manager

Pass an entity instance or a class-string to get the manager responsible for that entity. Resolved
managers are memoized, so repeated calls are cheap:

```php
$manager = $this->getManager($order);      // from an instance
$manager = $this->getManager(Order::class); // from a class-string
```

Call it without arguments to get the **default** entity manager. If more than one manager is registered,
this throws an `\InvalidArgumentException` — pass an entity instead so the trait knows which one you mean:

```php
$manager = $this->getManager();
```

### `getRepository()` — resolve the repository

Like `getManager()`, this accepts an entity instance or a class-string and returns the matching
`EntityRepository`:

```php
$repository = $this->getRepository(Order::class);

$orders = $repository->findBy(['status' => 'pending']);
```

### Typed repositories

If you have a custom repository class, pass it as the second argument. The trait asserts the repository
is an instance of that type (throwing an `\InvalidArgumentException` otherwise) **and** narrows the
return type for static analysis, so your IDE and PHPStan know about the repository's own methods:

```php
// $repository is statically typed as OrderRepository
$repository = $this->getRepository(Order::class, OrderRepository::class);

$orders = $repository->findPendingOrders();
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
