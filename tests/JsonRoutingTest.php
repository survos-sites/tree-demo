<?php

declare(strict_types=1);

namespace App\Tests;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Filesystem\Filesystem;

final class JsonRoutingTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testWarmupAndImportsWorkWithoutFosOrApplicationAliases(): void
    {
        $projectDir = dirname(__DIR__);
        $generatedDir = $projectDir.'/var/js_twig_bundle/generated';
        // Reproduce a fresh checkout instead of passing on a stale route dump.
        (new Filesystem())->remove($generatedDir);
        self::bootKernel();
        self::getContainer()->get('cache_warmer')->warmUp(self::$kernel->getCacheDir());

        self::assertFalse(class_exists('FOS\\JsRoutingBundle\\FOSJsRoutingBundle'));
        self::assertFileExists($generatedDir.'/routes.json');
        self::assertFileDoesNotExist($generatedDir.'/fos_routes.js');
        $data = json_decode(file_get_contents($generatedDir.'/routes.json'), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('app_homepage', $data['routes']);
        self::assertArrayHasKey('topic_tree_api', $data['routes']);
        self::assertArrayNotHasKey('app_load_topics', $data['routes']);
        foreach (['topic_show', 'topic_new', 'topic_edit', 'topic_delete'] as $removed) {
            self::assertNull(self::getContainer()->get('router')->getRouteCollection()->get($removed));
        }

        $entries = require $projectDir.'/importmap.php';
        foreach (array_keys($entries) as $entry) {
            self::assertStringNotContainsString('fos-routing', $entry);
            self::assertStringNotContainsString('@survos/js-twig/', $entry);
        }

        $mapper = self::getContainer()->get(AssetMapperInterface::class);
        $runtime = $mapper->getAsset('@survos/js-twig/routing.js');
        self::assertNotNull($runtime);
        self::assertStringContainsString('await routesPromise', $runtime->content);
        self::assertContains('@survos/js-twig/generated/routes.json', array_column($runtime->getJavaScriptImports(), 'assetLogicalPath'));

        // Verify app, tree-bundle, and an unchanged legacy consumer all discover
        // the runtime without a root importmap entry, through mono symlinks.
        foreach ([
            'controllers/twig_browser_demo_controller.js',
            '@survos/tree/src/controllers/api_tree_controller.js',
            '@survos/api-grid/src/controllers/api_grid_controller.js',
        ] as $logicalPath) {
            $asset = $mapper->getAsset($logicalPath);
            self::assertNotNull($asset, $logicalPath);
            self::assertContains('@survos/js-twig/routing.js', array_column($asset->getJavaScriptImports(), 'assetLogicalPath'), $logicalPath);
        }
    }
}
