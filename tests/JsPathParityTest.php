<?php

declare(strict_types=1);

namespace App\Tests;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * The browser-side path() must return what Symfony's UrlGenerator returns for the same
 * route and parameters. The expected values come from the real router; the actual values
 * come from assets/router.js (symfony/ux-router) reading the cache-warmed var/routes. Server-free, so it runs
 * without a dev server or a browser.
 */
final class JsPathParityTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /** @return array<string, array{route: string, params: array<string, mixed>}> */
    private static function cases(): array
    {
        return [
            'static route' => ['route' => 'app_homepage', 'params' => []],
            'static route, other' => ['route' => 'topic_tree_api', 'params' => []],
            'extra params become the query (demo template does this)' => ['route' => 'app_homepage', 'params' => ['containerId' => 7]],
            'query with spaces' => ['route' => 'app_inventory', 'params' => ['q' => 'a b', 'page' => 2]],
            'query with arrays' => ['route' => 'app_inventory', 'params' => ['tags' => ['x', 'y']]],
            'optional _format omitted' => ['route' => 'app_tree_json', 'params' => []],
            'optional _format given (demo controller does this)' => ['route' => 'app_tree_json', 'params' => ['_format' => 'json']],
            'required variable' => ['route' => 'building_locations', 'params' => ['buildingId' => 3]],
            'required variable plus format' => ['route' => 'building_locations', 'params' => ['buildingId' => 3, '_format' => 'json']],
            'required variable plus query' => ['route' => 'building_locations', 'params' => ['buildingId' => 3, 'page' => 2]],
            'missing required variable throws' => ['route' => 'building_locations', 'params' => []],
        ];
    }

    public function testBrowserPathMatchesSymfonyUrlGenerator(): void
    {
        if (null === (new \Symfony\Component\Process\ExecutableFinder())->find('node')) {
            self::markTestSkipped('node is not installed.');
        }

        $projectDir = dirname(__DIR__);
        // Reproduce a fresh checkout instead of passing on a stale route dump.
        (new Filesystem())->remove($projectDir.'/var/routes');
        self::bootKernel();
        $warmer = self::getContainer()->get('cache_warmer');
        $warmer->enableOptionalWarmers();
        $warmer->warmUp(self::$kernel->getCacheDir());

        $router = self::getContainer()->get('router');
        $expected = [];
        foreach (self::cases() as $id => $case) {
            try {
                $expected[$id] = $router->generate($case['route'], $case['params']);
            } catch (\Throwable) {
                $expected[$id] = ['error' => true];
            }
        }

        $casesFile = tempnam(sys_get_temp_dir(), 'path-cases-');
        file_put_contents($casesFile, json_encode(self::cases(), JSON_THROW_ON_ERROR));
        $process = new Process(
            ['node', '--import', __DIR__.'/js/loader.mjs', __DIR__.'/js/path_cases.mjs', $casesFile],
            $projectDir,
            [
                'UX_ROUTER_DIST' => $projectDir.'/vendor/symfony/ux-router/assets/dist/router_controller.js',
                'ROUTING_MODULE' => $projectDir.'/assets/router.js',
            ],
        );
        $process->mustRun();
        @unlink($casesFile);

        $actual = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        $mismatches = [];
        foreach ($expected as $id => $url) {
            if (is_array($url)) {
                if (!is_array($actual[$id])) {
                    $mismatches[] = sprintf('%s: PHP throws, JS returned %s', $id, json_encode($actual[$id]));
                }
                continue;
            }
            if ($url !== $actual[$id]) {
                $mismatches[] = sprintf('%s: PHP %s, JS %s', $id, $url, json_encode($actual[$id]));
            }
        }
        self::assertSame([], $mismatches, "JS path() differs from Symfony:\n".implode("\n", $mismatches));
    }
}
