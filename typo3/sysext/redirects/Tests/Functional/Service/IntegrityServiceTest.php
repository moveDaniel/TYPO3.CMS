<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Redirects\Tests\Unit\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Redirects\Service\IntegrityService;
use TYPO3\CMS\Redirects\Service\RedirectCacheService;
use TYPO3\CMS\Redirects\Service\RedirectService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class IntegrityServiceTest extends FunctionalTestCase
{
    /**
     * @var bool Reset singletons created by subject
     */
    protected $resetSingletonInstances = true;

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['redirects'];

    /**
     * @var IntegrityService
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new IntegrityService(
            new RedirectService(
                new RedirectCacheService(),
                $this->prophesize(LinkService::class)->reveal()
            ),
            $this->prophesizeSiteFinder()->reveal()
        );
    }

    /**
     * @test
     */
    public function conflictingRedirectsAreFoundForDefinedSiteOnly(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SimplePages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/sys_redirect.csv');

        $expectedConflictes = [
            [
                'uri' => 'https://example.com/',
                'redirect' => [
                    'source_host' => 'example.com',
                    'source_path' => '/',
                ],
            ],
            [
                'uri' => 'https://example.com/about-us/we-are-here',
                'redirect' => [
                    'source_host' => '*',
                    'source_path' => '/about-us/we-are-here',
                ],
            ],
            [
                'uri' => 'https://example.com/contact',
                'redirect' => [
                    'source_host' => 'example.com',
                    'source_path' => '/contact',
                ],
            ],
        ];

        $this->assertExpectedPathsFromGenerator($expectedConflictes, $this->subject->findConflictingRedirects('simple-page'));
    }

    /**
     * @test
     */
    public function conflictingRedirectsAreFoundForLocalizedPages(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LocalizedPages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/sys_redirect.csv');

        $expectedConflictes = [
            [
                'uri' => 'https://another.example.com/about-us/we-are-here',
                'redirect' => [
                    'source_host' => '*',
                    'source_path' => '/about-us/we-are-here',
                ],
            ],
            [
                'uri' => 'https://another.example.com/de/merkmale',
                'redirect' => [
                    'source_host' => 'another.example.com',
                    'source_path' => '/de/merkmale',
                ],
            ],
        ];

        $this->assertExpectedPathsFromGenerator($expectedConflictes, $this->subject->findConflictingRedirects('localized-page'));
    }

    /**
     * @test
     */
    public function conflictingRedirectsAreFoundForAllSites(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SimplePages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LocalizedPages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/sys_redirect.csv');

        $expectedConflictes = [
            [
                'uri' => 'https://example.com/',
                'redirect' => [
                    'source_host' => 'example.com',
                    'source_path' => '/',
                ],
            ],
            [
                'uri' => 'https://example.com/about-us/we-are-here',
                'redirect' => [
                    'source_host' => '*',
                    'source_path' => '/about-us/we-are-here',
                ],
            ],
            [
                'uri' => 'https://another.example.com/about-us/we-are-here',
                'redirect' => [
                    'source_host' => '*',
                    'source_path' => '/about-us/we-are-here',
                ],
            ],
            [
                'uri' => 'https://another.example.com/de/merkmale',
                'redirect' => [
                    'source_host' => 'another.example.com',
                    'source_path' => '/de/merkmale',
                ],
            ],
            [
                'uri' => 'https://example.com/contact',
                'redirect' => [
                    'source_host' => 'example.com',
                    'source_path' => '/contact',
                ],
            ],
        ];

        $this->assertExpectedPathsFromGenerator($expectedConflictes, $this->subject->findConflictingRedirects());
    }

    private function assertExpectedPathsFromGenerator(array $expectedConflictes, \Generator $generator): void
    {
        $matches = 0;
        foreach ($generator as $reportedConflict) {
            $this->assertContains($reportedConflict, $expectedConflictes);
            $matches++;
        }
        $this->assertSame(count($expectedConflictes), $matches);
    }

    private function prophesizeSiteFinder(): ObjectProphecy
    {
        $siteFinderProphecy = $this->prophesize(SiteFinder::class);

        $simpleSite = new Site('simple-page', 1, [
            'base' => 'https://example.com',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'United States',
                    'locale' => 'en_US.UTF-8',
                ],
            ]
        ]);
        $localizedSite = new Site('localized-page', 100, [
            'base' => 'https://another.example.com',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'United States',
                    'locale' => 'en_US.UTF-8',
                ],
                [
                    'base' => '/de/',
                    'languageId' => 1,
                    'title' => 'DE',
                    'locale' => 'de_DE.UTF-8',
                ]
            ]
        ]);

        $siteFinderProphecy->getSiteByIdentifier('simple-page')->willReturn($simpleSite);
        $siteFinderProphecy->getSiteByIdentifier('localized-page')->willReturn($localizedSite);
        $siteFinderProphecy->getAllSites()->willReturn([$simpleSite, $localizedSite]);

        return $siteFinderProphecy;
    }
}
