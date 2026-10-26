<?php

declare(strict_types=1);

namespace App\Tests;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Filesystem\Filesystem;

final class UxRouterWiringTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testWarmupDumpsRoutesAndConsumersImportTheAppRouter(): void
    {
        $projectDir = dirname(__DIR__);
        // Reproduce a fresh checkout instead of passing on a stale route dump.
        (new Filesystem())->remove($projectDir.'/var/routes');
        self::bootKernel();
        $warmer = self::getContainer()->get('cache_warmer');
        $warmer->enableOptionalWarmers();
        $warmer->warmUp(self::$kernel->getCacheDir());

        self::assertFalse(class_exists('FOS\\JsRoutingBundle\\FOSJsRoutingBundle'));
        $dumped = file_get_contents($projectDir.'/var/routes/index.js');
        foreach (['app_homepage', 'topic_tree_api', 'app_tree_json'] as $route) {
            self::assertStringContainsString('"'.$route.'"', $dumped);
        }

        $entries = require $projectDir.'/importmap.php';
        self::assertArrayHasKey('router', $entries);
        self::assertArrayHasKey('@symfony/ux-router', $entries);
        foreach (array_keys($entries) as $entry) {
            self::assertStringNotContainsString('@survos/js-twig/', $entry);
        }

        // The app controller imports the router relatively; the bundles import it as 'router'.
        $mapper = self::getContainer()->get(AssetMapperInterface::class);
        $demo = $mapper->getAsset('controllers/twig_browser_demo_controller.js');
        self::assertNotNull($demo);
        self::assertStringContainsString("from '../router.js'", file_get_contents($demo->sourcePath));
        foreach ([
            '@survos/tree-bundle/src/controllers/api_tree_controller.js',
            '@survos/api-grid-bundle/src/controllers/api_grid_controller.js',
        ] as $logicalPath) {
            $asset = $mapper->getAsset($logicalPath);
            self::assertNotNull($asset, $logicalPath);
            self::assertMatchesRegularExpression('/import\(\s*[\'"]router[\'"]\s*\)/', file_get_contents($asset->sourcePath), $logicalPath);
            self::assertStringNotContainsString('js-twig', file_get_contents($asset->sourcePath), $logicalPath);
        }
    }
}
