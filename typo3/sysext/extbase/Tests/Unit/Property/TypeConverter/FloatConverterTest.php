<?php
namespace TYPO3\CMS\Extbase\Tests\Unit\Property\TypeConverter;

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

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class FloatConverterTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Extbase\Property\TypeConverterInterface
     */
    protected $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new \TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter();
    }

    /**
     * @test
     */
    public function checkMetadata()
    {
        $this->assertEquals(['float', 'integer', 'string'], $this->converter->getSupportedSourceTypes(), 'Source types do not match');
        $this->assertEquals('float', $this->converter->getSupportedTargetType(), 'Target type does not match');
        $this->assertEquals(10, $this->converter->getPriority(), 'Priority does not match');
    }

    /**
     * @test
     */
    public function convertFromShouldCastTheStringToFloat()
    {
        $this->assertSame(1.5, $this->converter->convertFrom('1.5', 'float'));
    }

    /**
     * @test
     */
    public function convertFromReturnsNullIfEmptyStringSpecified()
    {
        $this->assertNull($this->converter->convertFrom('', 'float'));
    }

    /**
     * @test
     */
    public function convertFromShouldAcceptIntegers()
    {
        $this->assertSame((float)123, $this->converter->convertFrom(123, 'float'));
    }

    /**
     * @test
     */
    public function convertFromShouldRespectConfiguration()
    {
        $mockMappingConfiguration = $this->createMock(\TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface::class);
        $mockMappingConfiguration
            ->expects($this->at(0))
            ->method('getConfigurationValue')
            ->with(\TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter::class, \TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter::CONFIGURATION_THOUSANDS_SEPARATOR)
            ->willReturn('.');
        $mockMappingConfiguration
            ->expects($this->at(1))
            ->method('getConfigurationValue')
            ->with(\TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter::class, \TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter::CONFIGURATION_DECIMAL_POINT)
            ->willReturn(',');
        $this->assertSame(1024.42, $this->converter->convertFrom('1.024,42', 'float', [], $mockMappingConfiguration));
    }

    /**
     * @test
     */
    public function convertFromReturnsAnErrorIfSpecifiedStringIsNotNumeric()
    {
        $this->assertInstanceOf(\TYPO3\CMS\Extbase\Error\Error::class, $this->converter->convertFrom('not numeric', 'float'));
    }

    /**
     * @test
     */
    public function canConvertFromShouldReturnTrue()
    {
        $this->assertTrue($this->converter->canConvertFrom('1.5', 'float'));
    }

    /**
     * @test
     */
    public function canConvertFromShouldReturnTrueForAnEmptyValue()
    {
        $this->assertTrue($this->converter->canConvertFrom('', 'integer'));
    }

    /**
     * @test
     */
    public function canConvertFromShouldReturnTrueForANullValue()
    {
        $this->assertTrue($this->converter->canConvertFrom(null, 'integer'));
    }

    /**
     * @test
     */
    public function getSourceChildPropertiesToBeConvertedShouldReturnEmptyArray()
    {
        $this->assertEquals([], $this->converter->getSourceChildPropertiesToBeConverted('myString'));
    }
}
