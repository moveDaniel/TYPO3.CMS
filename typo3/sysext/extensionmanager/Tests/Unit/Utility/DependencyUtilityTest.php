<?php
namespace TYPO3\CMS\Extensionmanager\Tests\Unit\Utility;

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

use TYPO3\CMS\Extensionmanager\Domain\Model\Dependency;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException;
use TYPO3\CMS\Extensionmanager\Utility\DependencyUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test for DependencyUtility
 */
class DependencyUtilityTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface::class)->getMock();
    }

    /**
     * @test
     */
    public function checkTypo3DependencyThrowsExceptionIfVersionNumberIsTooLow()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('15.0.0'));
        $dependencyMock->setIdentifier('typo3');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        $this->expectException(ExtensionManagerException::class);
        $this->expectExceptionCode(1399144499);
        $dependencyUtility->_call('checkTypo3Dependency', $dependencyMock);
    }

    /**
     * @test
     */
    public function checkTypo3DependencyThrowsExceptionIfVersionNumberIsTooHigh()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('3.0.0'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $dependencyMock->setIdentifier('typo3');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        $this->expectException(ExtensionManagerException::class);
        $this->expectExceptionCode(1399144521);
        $dependencyUtility->_call('checkTypo3Dependency', $dependencyMock);
    }

    /**
     * @test
     */
    public function checkTypo3DependencyThrowsExceptionIfIdentifierIsNotTypo3()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->setIdentifier('123');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        $this->expectException(ExtensionManagerException::class);
        $this->expectExceptionCode(1399144551);
        $dependencyUtility->_call('checkTypo3Dependency', $dependencyMock);
    }

    /**
     * @test
     */
    public function checkTypo3DependencyReturnsTrueIfVersionNumberIsInRange()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('15.0.0'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $dependencyMock->setIdentifier('typo3');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertTrue($dependencyUtility->_call('checkTypo3Dependency', $dependencyMock));
    }

    /**
     * @test
     */
    public function checkTypo3DependencyCanHandleEmptyVersionHighestVersion()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue(''));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $dependencyMock->setIdentifier('typo3');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertTrue($dependencyUtility->_call('checkTypo3Dependency', $dependencyMock));
    }

    /**
     * @test
     */
    public function checkTypo3DependencyCanHandleEmptyVersionLowestVersion()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('15.0.0'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue(''));
        $dependencyMock->setIdentifier('typo3');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertTrue($dependencyUtility->_call('checkTypo3Dependency', $dependencyMock));
    }

    /**
     * @test
     */
    public function checkPhpDependencyThrowsExceptionIfVersionNumberIsTooLow()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('15.0.0'));
        $dependencyMock->setIdentifier('php');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        $this->expectException(ExtensionManagerException::class);
        $this->expectExceptionCode(1377977857);
        $dependencyUtility->_call('checkPhpDependency', $dependencyMock);
    }

    /**
     * @test
     */
    public function checkPhpDependencyThrowsExceptionIfVersionNumberIsTooHigh()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('3.0.0'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $dependencyMock->setIdentifier('php');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        $this->expectException(ExtensionManagerException::class);
        $this->expectExceptionCode(1377977856);
        $dependencyUtility->_call('checkPhpDependency', $dependencyMock);
    }

    /**
     * @test
     */
    public function checkPhpDependencyThrowsExceptionIfIdentifierIsNotTypo3()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->setIdentifier('123');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        $this->expectException(ExtensionManagerException::class);
        $this->expectExceptionCode(1377977858);
        $dependencyUtility->_call('checkPhpDependency', $dependencyMock);
    }

    /**
     * @test
     */
    public function checkPhpDependencyReturnsTrueIfVersionNumberIsInRange()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('15.0.0'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $dependencyMock->setIdentifier('php');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertTrue($dependencyUtility->_call('checkPhpDependency', $dependencyMock));
    }

    /**
     * @test
     */
    public function checkPhpDependencyCanHandleEmptyVersionHighestVersion()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue(''));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $dependencyMock->setIdentifier('php');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertTrue($dependencyUtility->_call('checkPhpDependency', $dependencyMock));
    }

    /**
     * @test
     */
    public function checkPhpDependencyCanHandleEmptyVersionLowestVersion()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('15.0.0'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue(''));
        $dependencyMock->setIdentifier('php');
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertTrue($dependencyUtility->_call('checkPhpDependency', $dependencyMock));
    }

    /**
     * @test
     */
    public function checkDependenciesCallsMethodToCheckPhpDependencies()
    {
        /** @var Extension $extensionMock */
        $extensionMock = $this->getMockBuilder(Extension::class)
            ->setMethods(['dummy'])
            ->getMock();
        /** @var Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->setIdentifier('php');
        $dependencyStorage = new \SplObjectStorage();
        $dependencyStorage->attach($dependencyMock);
        $extensionMock->setDependencies($dependencyStorage);
        /** @var \PHPUnit_Framework_MockObject_MockObject|DependencyUtility $dependencyUtility */
        $dependencyUtility = $this->getMockBuilder(DependencyUtility::class)
            ->setMethods(['checkPhpDependency', 'checkTypo3Dependency'])
            ->getMock();
        $dependencyUtility->expects(self::atLeastOnce())->method('checkPhpDependency');
        $dependencyUtility->checkDependencies($extensionMock);
    }

    /**
     * @test
     */
    public function checkDependenciesCallsMethodToCheckTypo3Dependencies()
    {
        /** @var Extension $extensionMock */
        $extensionMock = $this->getMockBuilder(Extension::class)
            ->setMethods(['dummy'])
            ->getMock();
        /** @var Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->setIdentifier('typo3');
        $dependencyStorage = new \SplObjectStorage();
        $dependencyStorage->attach($dependencyMock);
        $extensionMock->setDependencies($dependencyStorage);
        /** @var \PHPUnit_Framework_MockObject_MockObject|DependencyUtility $dependencyUtility */
        $dependencyUtility = $this->getMockBuilder(DependencyUtility::class)
            ->setMethods(['checkPhpDependency', 'checkTypo3Dependency'])
            ->getMock();

        $dependencyUtility->expects(self::atLeastOnce())->method('checkTypo3Dependency');
        $dependencyUtility->checkDependencies($extensionMock);
    }

    /**
     * @test
     */
    public function isVersionCompatibleReturnsTrueForCompatibleVersion()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('15.0.0'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $version = '3.3.3';
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertTrue($dependencyUtility->_call('isVersionCompatible', $version, $dependencyMock));
    }

    /**
     * @test
     */
    public function isVersionCompatibleReturnsFalseForIncompatibleVersion()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Dependency $dependencyMock */
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::atLeastOnce())->method('getHighestVersion')->will(self::returnValue('1.0.1'));
        $dependencyMock->expects(self::atLeastOnce())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $version = '3.3.3';
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);

        self::assertFalse($dependencyUtility->_call('isVersionCompatible', $version, $dependencyMock));
    }

    /**
     * @test
     */
    public function isDependentExtensionAvailableReturnsTrueIfExtensionIsAvailable()
    {
        $availableExtensions = [
            'dummy' => [],
            'foo' => [],
            'bar' => []
        ];
        $listUtilityMock = $this->getMockBuilder(\TYPO3\CMS\Extensionmanager\Utility\ListUtility::class)
            ->setMethods(['getAvailableExtensions'])
            ->getMock();
        $listUtilityMock->expects(self::atLeastOnce())->method('getAvailableExtensions')->will(self::returnValue($availableExtensions));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);
        $dependencyUtility->_set('listUtility', $listUtilityMock);

        self::assertTrue($dependencyUtility->_call('isDependentExtensionAvailable', 'dummy'));
    }

    /**
     * @test
     */
    public function isDependentExtensionAvailableReturnsFalseIfExtensionIsNotAvailable()
    {
        $availableExtensions = [
            'dummy' => [],
            'foo' => [],
            'bar' => []
        ];
        $listUtilityMock = $this->getMockBuilder(\TYPO3\CMS\Extensionmanager\Utility\ListUtility::class)
            ->setMethods(['getAvailableExtensions'])
            ->getMock();
        $listUtilityMock->expects(self::atLeastOnce())->method('getAvailableExtensions')->will(self::returnValue($availableExtensions));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);
        $dependencyUtility->_set('listUtility', $listUtilityMock);

        self::assertFalse($dependencyUtility->_call('isDependentExtensionAvailable', '42'));
    }

    /**
     * @test
     */
    public function isAvailableVersionCompatibleCallsIsVersionCompatibleWithExtensionVersion()
    {
        $emConfUtility = $this->getMockBuilder(\TYPO3\CMS\Extensionmanager\Utility\EmConfUtility::class)
            ->setMethods(['includeEmConf'])
            ->getMock();
        $emConfUtility->expects(self::once())->method('includeEmConf')->will(self::returnValue([
            'key' => 'dummy',
            'version' => '1.0.0'
        ]));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['setAvailableExtensions', 'isVersionCompatible']);
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getIdentifier'])
            ->getMock();
        $dependencyMock->expects(self::once())->method('getIdentifier')->will(self::returnValue('dummy'));
        $dependencyUtility->_set('emConfUtility', $emConfUtility);
        $dependencyUtility->_set('availableExtensions', [
            'dummy' => [
                'foo' => '42'
            ]
        ]);
        $dependencyUtility->expects(self::once())->method('setAvailableExtensions');
        $dependencyUtility->expects(self::once())->method('isVersionCompatible')->with('1.0.0', self::anything());
        $dependencyUtility->_call('isAvailableVersionCompatible', $dependencyMock);
    }

    /**
     * @test
     */
    public function isExtensionDownloadableFromTerReturnsTrueIfOneVersionExists()
    {
        $extensionRepositoryMock = $this->getMockBuilder(ExtensionRepository::class)
            ->setMethods(['countByExtensionKey'])
            ->setConstructorArgs([$this->objectManagerMock])
            ->getMock();
        $extensionRepositoryMock->expects(self::once())->method('countByExtensionKey')->with('test123')->will(self::returnValue(1));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);
        $dependencyUtility->_set('extensionRepository', $extensionRepositoryMock);
        $count = $dependencyUtility->_call('isExtensionDownloadableFromTer', 'test123');

        self::assertTrue($count);
    }

    /**
     * @test
     */
    public function isExtensionDownloadableFromTerReturnsFalseIfNoVersionExists()
    {
        $extensionRepositoryMock = $this->getMockBuilder(ExtensionRepository::class)
            ->setMethods(['countByExtensionKey'])
            ->setConstructorArgs([$this->objectManagerMock])
            ->getMock();
        $extensionRepositoryMock->expects(self::once())->method('countByExtensionKey')->with('test123')->will(self::returnValue(0));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);
        $dependencyUtility->_set('extensionRepository', $extensionRepositoryMock);
        $count = $dependencyUtility->_call('isExtensionDownloadableFromTer', 'test123');

        self::assertFalse($count);
    }

    /**
     * @test
     */
    public function isDownloadableVersionCompatibleReturnsTrueIfCompatibleVersionExists()
    {
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getIdentifier', 'getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::once())->method('getIdentifier')->will(self::returnValue('dummy'));
        $dependencyMock->expects(self::once())->method('getHighestVersion')->will(self::returnValue('10.0.0'));
        $dependencyMock->expects(self::once())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $extensionRepositoryMock = $this->getMockBuilder(ExtensionRepository::class)
            ->setMethods(['countByVersionRangeAndExtensionKey'])
            ->setConstructorArgs([$this->objectManagerMock])
            ->getMock();
        $extensionRepositoryMock->expects(self::once())->method('countByVersionRangeAndExtensionKey')->with('dummy', 1000000, 10000000)->will(self::returnValue(2));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);
        $dependencyUtility->_set('extensionRepository', $extensionRepositoryMock);
        $count = $dependencyUtility->_call('isDownloadableVersionCompatible', $dependencyMock);

        self::assertTrue($count);
    }

    /**
     * @test
     */
    public function isDownloadableVersionCompatibleReturnsFalseIfIncompatibleVersionExists()
    {
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getIdentifier'])
            ->getMock();
        $dependencyMock->expects(self::once())->method('getIdentifier')->will(self::returnValue('dummy'));
        $extensionRepositoryMock = $this->getMockBuilder(ExtensionRepository::class)
            ->setMethods(['countByVersionRangeAndExtensionKey'])
            ->setConstructorArgs([$this->objectManagerMock])
            ->getMock();
        $extensionRepositoryMock->expects(self::once())->method('countByVersionRangeAndExtensionKey')->with('dummy', 1000000, 2000000)->will(self::returnValue(0));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['getLowestAndHighestIntegerVersions']);
        $dependencyUtility->_set('extensionRepository', $extensionRepositoryMock);
        $dependencyUtility->expects(self::once())->method('getLowestAndHighestIntegerVersions')->will(self::returnValue([
            'lowestIntegerVersion' => 1000000,
            'highestIntegerVersion' => 2000000
        ]));
        $count = $dependencyUtility->_call('isDownloadableVersionCompatible', $dependencyMock);

        self::assertFalse($count);
    }

    /**
     * @test
     */
    public function getLowestAndHighestIntegerVersionsReturnsArrayWithVersions()
    {
        $expectedVersions = [
            'lowestIntegerVersion' => 1000000,
            'highestIntegerVersion' => 2000000
        ];

        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getHighestVersion', 'getLowestVersion'])
            ->getMock();
        $dependencyMock->expects(self::once())->method('getHighestVersion')->will(self::returnValue('2.0.0'));
        $dependencyMock->expects(self::once())->method('getLowestVersion')->will(self::returnValue('1.0.0'));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['dummy']);
        $versions = $dependencyUtility->_call('getLowestAndHighestIntegerVersions', $dependencyMock);

        self::assertSame($expectedVersions, $versions);
    }

    /**
     * @test
     */
    public function getLatestCompatibleExtensionByIntegerVersionDependencyWillReturnExtensionModelOfLatestExtension()
    {
        $extension1 = new Extension();
        $extension1->setExtensionKey('foo');
        $extension1->setVersion('1.0.0');
        $extension2 = new Extension();
        $extension2->setExtensionKey('bar');
        $extension2->setVersion('1.0.42');

        $myStorage = new \TYPO3\CMS\Extensionmanager\Tests\Unit\Fixtures\LatestCompatibleExtensionObjectStorageFixture();
        $myStorage->extensions[] = $extension1;
        $myStorage->extensions[] = $extension2;
        $dependencyMock = $this->getMockBuilder(Dependency::class)
            ->setMethods(['getIdentifier'])
            ->getMock();
        $dependencyMock->expects(self::once())->method('getIdentifier')->will(self::returnValue('foobar'));
        $dependencyUtility = $this->getAccessibleMock(DependencyUtility::class, ['getLowestAndHighestIntegerVersions']);
        $dependencyUtility->expects(self::once())->method('getLowestAndHighestIntegerVersions')->will(self::returnValue([
            'lowestIntegerVersion' => 1000000,
            'highestIntegerVersion' => 2000000
        ]));
        $extensionRepositoryMock = $this->getMockBuilder(ExtensionRepository::class)
            ->setMethods(['findByVersionRangeAndExtensionKeyOrderedByVersion'])
            ->setConstructorArgs([$this->objectManagerMock])
            ->getMock();
        $extensionRepositoryMock->expects(self::once())->method('findByVersionRangeAndExtensionKeyOrderedByVersion')->with('foobar', 1000000, 2000000)->will(self::returnValue($myStorage));
        $dependencyUtility->_set('extensionRepository', $extensionRepositoryMock);
        $extension = $dependencyUtility->_call('getLatestCompatibleExtensionByIntegerVersionDependency', $dependencyMock);

        self::assertInstanceOf(Extension::class, $extension);
        self::assertSame('foo', $extension->getExtensionKey());
    }

    /**
     * @test
     */
    public function filterYoungestVersionOfExtensionListFiltersAListToLatestVersion()
    {
        // foo2 should be kept
        $foo1 = new Extension();
        $foo1->setExtensionKey('foo');
        $foo1->setVersion('1.0.0');
        $foo2 = new Extension();
        $foo2->setExtensionKey('foo');
        $foo2->setVersion('1.0.1');

        // bar1 should be kept
        $bar1 = new Extension();
        $bar1->setExtensionKey('bar');
        $bar1->setVersion('1.1.2');
        $bar2 = new Extension();
        $bar2->setExtensionKey('bar');
        $bar2->setVersion('1.1.1');
        $bar3 = new Extension();
        $bar3->setExtensionKey('bar');
        $bar3->setVersion('1.0.3');

        $input = [$foo1, $foo2, $bar1, $bar2, $bar3];
        self::assertEquals(['foo' => $foo2, 'bar' => $bar1], (new DependencyUtility())->filterYoungestVersionOfExtensionList($input, true));
    }

    /**
     * @test
     */
    public function filterYoungestVersionOfExtensionListFiltersAListToLatestVersionWithOnlyCompatibleExtensions()
    {
        $suitableDependency = new Dependency();
        $suitableDependency->setIdentifier('typo3');
        $suitableDependency->setLowestVersion('3.6.1');

        $suitableDependencies = new \SplObjectStorage();
        $suitableDependencies->attach($suitableDependency);

        $unsuitableDependency = new Dependency();
        $unsuitableDependency->setIdentifier('typo3');
        $unsuitableDependency->setHighestVersion('4.3.0');

        $unsuitableDependencies = new \SplObjectStorage();
        $unsuitableDependencies->attach($unsuitableDependency);

        // foo1 should be kept
        $foo1 = new Extension();
        $foo1->setExtensionKey('foo');
        $foo1->setVersion('1.0.0');
        $foo1->setDependencies($suitableDependencies);

        $foo2 = new Extension();
        $foo2->setExtensionKey('foo');
        $foo2->setVersion('1.0.1');
        $foo2->setDependencies($unsuitableDependencies);

        // bar2 should be kept
        $bar1 = new Extension();
        $bar1->setExtensionKey('bar');
        $bar1->setVersion('1.1.2');
        $bar1->setDependencies($unsuitableDependencies);

        $bar2 = new Extension();
        $bar2->setExtensionKey('bar');
        $bar2->setVersion('1.1.1');
        $bar2->setDependencies($suitableDependencies);

        $input = [$foo1, $foo2, $bar1, $bar2];
        self::assertEquals(['foo' => $foo1, 'bar' => $bar2], (new DependencyUtility())->filterYoungestVersionOfExtensionList($input, false));
    }
}
