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

Short version: standardize on `survos/tabler-bundle`.

Practical guidance:

1. Keep a single shell layout from tabler-bundle.
2. Use Twig components for repeatable UI chunks (cards, trees, nav blocks).
3. Avoid full page-level component nesting for every view; that tends to become hard to reason about.
4. Migrate legacy templates gradually: shell first, then extract repeated fragments to components.

This gives a stable base theme while still using components where they are most useful.

## Testing JSTree with a Local Copy

If you want to test a local development version of jstree-esm, add a path mapping
to your Symfony `asset_mapper.yaml`:

```yaml
framework:
  asset_mapper:
    paths:
      '/path/to/local/jstree': '@tacman1123/jstree-esm'
```
This allows Symfony to load the assets from your local directory instead of the CDN.

## Relevant Links

- Demo: https://tree.survos.com
- Nested sets explainer: https://drib.tech/programming/hierarchical-data-relational-databases-symfony-4-doctrine

## Local table playground

The working checkout is `/Users/tac/sites/tree-demo`. The local `.env.local`
selects PostgreSQL on `127.0.0.1:5434`, database `tree_demo`; it contains local
credentials and is ignored by Git. The bundled IPTC dataset has been imported
(1,370 topics).

Open `/playground/topics` to compare UX DataTables, Simple DataTables, and plain
HTML using the same dataset and Twig blocks. The page links to the existing
HTML tree and API grid. Symfony 8.1 and UX DataTables 1.0 are installed. The app uses published
`survos/simple-datatables-bundle` 2.31.7 and `survos/kit-bundle` releases;
no workstation-specific Composer paths are required.

```bash
cd ~/sites/tree-demo
php -S 127.0.0.1:8770 -t public public/index.php
# http://127.0.0.1:8770/playground/topics
```

The three playground modes and search were verified in Chrome against the real
Symfony AssetMapper build. The HTML tree and API grid now use the Tabler shell. Container lint and
PostgreSQL mapping/schema validation pass after the dependency upgrade.


### Tabler and asset setup

The app extends `@SurvosTabler/layout/base.html.twig`, with menu entries registered
through `MenuEvent::NAVBAR_MENU`. Tabler 1.4.0 uses the pinned raw ESM distribution
in `assets/vendor-patched/` so Bootstrap dropdown and collapse data APIs work.

The importmap is maintained explicitly (`extra.symfony/flex.synchronize_package_json`
is false), including DataTables 3-compatible extensions and the generated js-twig
routing module. After dependency changes, clear Symfony's cache before testing
AssetMapper URLs. Run `composer install` to install assets and warm the route module.
The upstream Flex recipe currently uses an older bundle class name; the corrected
class and route resource are checked into this app together with `symfony.lock`.


## Dokku deployment

The app is `tree-demo` on `fsn1`, served at https://tree-demo.survos.com with
PostgreSQL 18 service `tree-demo-db`. The Heroku PHP buildpack installs PHP 8.5
and its required extensions. Secrets and the production database URL are set
in Dokku config. Production protects data-writing and file-source routes.

The predeploy script compiles AssetMapper assets and runs migrations. On a new
database, import the bundled 1,370-topic dataset once:

```bash
ssh -o BatchMode=yes fsn1 run tree-demo php bin/console app:import-topics
```

Before each deployment, run `php bin/console doctrine:schema:validate` locally
against PostgreSQL, commit changes, then push the same commit to origin and Dokku.
