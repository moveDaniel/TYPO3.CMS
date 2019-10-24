<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Extbase\Tests\Unit\Persistence\Generic;

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
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Backend;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\BackendInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class BackendTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function insertRelationInRelationtableSetsMmMatchFieldsInRow()
    {
        /* \TYPO3\CMS\Extbase\Persistence\Generic\Backend|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\TestingFramework\Core\AccessibleObjectInterface */
        $fixture = $this->getAccessibleMock(Backend::class, ['dummy'], [], '', false);
        /* \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper|\PHPUnit_Framework_MockObject_MockObject */
        $dataMapFactory = $this->createMock(DataMapFactory::class);
        /* \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap|\PHPUnit_Framework_MockObject_MockObject */
        $dataMap = $this->createMock(DataMap::class);
        /* \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap|\PHPUnit_Framework_MockObject_MockObject */
        $columnMap = $this->createMock(ColumnMap::class);
        /* \TYPO3\CMS\Extbase\Persistence\Generic\Storage\BackendInterface|\PHPUnit_Framework_MockObject_MockObject */
        $storageBackend = $this->createMock(BackendInterface::class);
        /* \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface|\PHPUnit_Framework_MockObject_MockObject */
        $domainObject = $this->createMock(DomainObjectInterface::class);

        $mmMatchFields = [
            'identifier' => 'myTable:myField',
        ];

        $expectedRow = [
            'identifier' => 'myTable:myField',
            '' => 0
        ];

        $columnMap
            ->expects(self::once())
            ->method('getRelationTableName')
            ->will(self::returnValue('myTable'));
        $columnMap
            ->expects(self::once())
            ->method('getRelationTableMatchFields')
            ->will(self::returnValue($mmMatchFields));
        $columnMap
            ->expects(self::any())
            ->method('getChildSortByFieldName')
            ->will(self::returnValue(''));
        $dataMap
            ->expects(self::any())
            ->method('getColumnMap')
            ->will(self::returnValue($columnMap));
        $dataMapFactory
            ->expects(self::any())
            ->method('buildDataMap')
            ->will(self::returnValue($dataMap));
        $storageBackend
            ->expects(self::once())
            ->method('addRow')
            ->with('myTable', $expectedRow, true);

        $fixture->_set('dataMapFactory', $dataMapFactory);
        $fixture->_set('storageBackend', $storageBackend);
        $fixture->_call('insertRelationInRelationtable', $domainObject, $domainObject, '');
    }

    /**
     * @test
     */
    public function getIdentifierByObjectReturnsIdentifierForNonLazyObject()
    {
        $fakeUuid = 'fakeUuid';
        $configurationManager = $this->createMock(ConfigurationManagerInterface::class);
        $session = $this->getMockBuilder('stdClass')
            ->setMethods(['getIdentifierByObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $object = new \stdClass();

        $referenceIndexProphecy = $this->prophesize(ReferenceIndex::class);
        GeneralUtility::addInstance(ReferenceIndex::class, $referenceIndexProphecy->reveal());

        $session->expects(self::once())->method('getIdentifierByObject')->with($object)->will(self::returnValue($fakeUuid));

        /** @var Backend $backend */
        $backend = $this->getAccessibleMock(Backend::class, ['dummy'], [$configurationManager], '', false);
        $backend->_set('session', $session);

        self::assertEquals($backend->getIdentifierByObject($object), $fakeUuid);
    }

    /**
     * @test
     */
    public function getIdentifierByObjectReturnsIdentifierForLazyObject()
    {
        $fakeUuid = 'fakeUuid';
        $configurationManager = $this->createMock(ConfigurationManagerInterface::class);
        $parentObject = new \stdClass();
        $proxy = $this->getMockBuilder(LazyLoadingProxy::class)
            ->setMethods(['_loadRealInstance'])
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $session = $this->getMockBuilder('stdClass')
            ->setMethods(['getIdentifierByObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $object = new \stdClass();

        $referenceIndexProphecy = $this->prophesize(ReferenceIndex::class);
        GeneralUtility::addInstance(ReferenceIndex::class, $referenceIndexProphecy->reveal());

        $proxy->expects(self::once())->method('_loadRealInstance')->will(self::returnValue($object));
        $session->expects(self::once())->method('getIdentifierByObject')->with($object)->will(self::returnValue($fakeUuid));

        /** @var Backend $backend */
        $backend = $this->getAccessibleMock(Backend::class, ['dummy'], [$configurationManager], '', false);
        $backend->_set('session', $session);

        self::assertEquals($backend->getIdentifierByObject($proxy), $fakeUuid);
    }
}
