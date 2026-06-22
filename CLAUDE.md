# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A single-file library (`src/ORMTrait.php`) exposing `Setono\Doctrine\ORMTrait`. The trait is meant to be used by classes that inject `Doctrine\Persistence\ManagerRegistry` (rather than an `EntityManager` directly) and need convenient, type-safe access to entity managers and repositories.

The whole public surface is two protected methods:
- `getManager(object|string|null $obj = null): EntityManagerInterface` — resolves the manager for an entity class/instance via the registry. With no argument it returns the *default* manager, but throws if more than one manager is registered (forcing the caller to disambiguate). Resolved managers are memoized in `$this->managers`, keyed by class-string (the `null`/default case is keyed by `''`).
- `getRepository(object|string $obj, ?string $expectedType = null): EntityRepository` — returns the repository for an entity. The optional `$expectedType` is a `class-string` that is both runtime-asserted (`instanceof`) and encoded in the return type via a conditional return template, so static analysis narrows the type.

A consuming class must assign `$this->managerRegistry` (a `readonly` property declared in the trait) in its constructor — see `README.md` for the usage example.

## Commands

The user's shell aliases (`ca`, `cf`, `cfca`, `phpunit`, `infection`) map to these. Run the raw forms if aliases are unavailable:

- `composer analyse` — PHPStan static analysis (config in `phpstan.dist.neon`, `level: max`, strictest)
- `composer check-style` — ECS dry-run (coding standard check)
- `composer fix-style` — ECS auto-fix
- `composer phpunit` — run the test suite
- `vendor/bin/phpunit --filter it_returns_repository` — run a single test by method name
- `vendor/bin/infection` — mutation testing (CI requires **100% MSI and 100% covered MSI**)
- `vendor/bin/composer-dependency-analyser` — verify declared composer deps match actual usage

CI (`.github/workflows/build.yaml`) additionally runs `composer validate --strict` and `composer normalize --dry-run`.

## Conventions & constraints

- **PHP `>=8.1`.** Code uses readonly properties, `first-class` enum-free syntax, `$obj::class`. Do not use 8.2+-only features — the coding-standards/static-analysis matrix pins 8.1 as the floor.
- **Multi-version Doctrine support is the point.** Deps allow `doctrine/orm ^2.8 || ^3.1` and `doctrine/persistence ^1.3 || ^2.5 || ^3.1 || ^4.1`. Any change must work across that whole range — CI runs `lowest` and `highest` dependency resolutions.
- **Coding standard** comes from `sylius-labs/coding-standard` (imported in `ecs.php`). Run `composer fix-style` before committing rather than hand-formatting.
- **Dev tooling is inlined, not a meta-package.** The individual tools that `setono/code-quality-pack` v3.x bundles are listed directly in `require-dev` (PHPStan + `phpstan-strict-rules`/`-phpunit`/`-webmozart-assert`/`jangregor/phpstan-prophecy` auto-registered via `phpstan/extension-installer`, plus ECS, Rector, Infection, composer-normalize, composer-dependency-analyser). Versions are capped by caret to the highest that still resolves on PHP 8.1 (e.g. `phpunit ^10.5`, `infection ^0.27.11`), so bumping the PHP floor is what unlocks newer majors.
- **Tests** (`tests/ORMTraitTest.php`) live in the same `Setono\Doctrine` namespace as `src/` (see the `autoload-dev` PSR-4 mapping). They use Prophecy (`phpspec/prophecy-phpunit`) for mocking the `ManagerRegistry`/managers, and define a concrete `ConcreteService extends ManagerTraitAware` that exposes the protected trait methods publicly for testing.
- Because of the 100% MSI requirement, every branch and thrown exception in `ORMTrait` must be covered by a test.
