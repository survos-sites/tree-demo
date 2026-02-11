# tree-demo

Demo app for hierarchical data with:

- Symfony 8
- Doctrine ORM + Gedmo Nested Set
- `survos/tree-bundle`
- API Platform + jsTree UI

## Quick Start

```bash
git clone git@github.com:survos-sites/tree-demo.git
cd tree-demo
composer install
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n
bin/console app:import-topics
bin/console app:load-directory-files
symfony server:start -d
```

## Stack Notes (PHP 8.4)

- This project assumes PHP 8.4, attributes, property hooks / asymetric visibility
- Prefer concise entities over generated boilerplate getters/setters.
- For tree entities, use `Survos\Tree\Traits\TreeTrait` when your PK is the default `id`.

## Doctrine Extensions Setup

Install:

```bash
composer req stof/doctrine-extensions-bundle
```

Enable tree behavior:

```yaml
# config/packages/stof_doctrine_extensions.yaml
stof_doctrine_extensions:
  default_locale: en_US
  orm:
    default:
      sluggable: true
      tree: true
```

## Minimal Tree Entity (Attribute Style)

Use `TreeTrait` and keep the entity lean. No generated boilerplate required.

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\Tree\Traits\TreeTrait;
use Survos\Tree\TreeInterface;

#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity]
final class Topic implements TreeInterface
{
    use TreeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Gedmo\Slug(fields: ['name'])]
    public string $code;

    #[ORM\Column(length: 255)]
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

## Repository

Use Gedmo nested repository:

```php
<?php

namespace App\Repository;

use App\Entity\Topic;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

final class TopicRepository extends NestedTreeRepository
{
}
```

## About `self::` vs `static::` (PHP 8.4)

- Use `self::` when you want to bind to the current class implementation.
- Use `static::` when you explicitly want late static binding (subclass override behavior).
- In `final` classes, `self::` is usually clearer and equivalent in practice.
- In non-final base classes and reusable traits, prefer `static::` only when extensibility is intentional.

## Theme Direction (Current Recommendation)

Short version: standardize on `survos/bootstrap-bundle` + Tabler theme.

Practical guidance:

1. Keep a single shell layout from bootstrap-bundle.
2. Use Twig components for repeatable UI chunks (cards, trees, nav blocks).
3. Avoid full page-level component nesting for every view; that tends to become hard to reason about.
4. Migrate legacy templates gradually: shell first, then extract repeated fragments to components.

This gives a stable base theme while still using components where they are most useful.

## Relevant Links

- Demo: https://tree.survos.com
- Nested sets explainer: https://drib.tech/programming/hierarchical-data-relational-databases-symfony-4-doctrine
