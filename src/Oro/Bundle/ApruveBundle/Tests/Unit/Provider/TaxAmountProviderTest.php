<?php

namespace Oro\Bundle\ApruveBundle\Tests\Unit;

use Oro\Bundle\ApruveBundle\Provider\TaxAmountProvider;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\TaxBundle\Exception\TaxationDisabledException;
use Oro\Bundle\TaxBundle\Manager\TaxManager;
use Oro\Bundle\TaxBundle\Mapper\UnmappableArgumentException;
use Psr\Log\LoggerInterface;

class TaxAmountProviderTest extends \PHPUnit_Framework_TestCase
{
    const AMOUNT = 10.0;
    const AMOUNT_NEGLIGIBLE = 0.000001;

    /**
     * @var \stdClass|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceEntity;

    /**
     * @var PaymentContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentContext;

    /**
     * @var TaxManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxManager;

    /**
     * @var TaxAmountProvider
     */
    private $provider;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->sourceEntity = $this->createMock(\stdClass::class);

        $this->paymentContext = $this->createMock(PaymentContextInterface::class);
        $this->paymentContext
            ->method('getSourceEntity')
            ->willReturn($this->sourceEntity);

        $this->taxManager = $this->createMock(TaxManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->provider = new TaxAmountProvider($this->taxManager);
        $this->provider->setLogger($this->logger);
    }

    /**
     * @dataProvider getTaxAmountDataProvider
     *
     * @param float $taxAmount
     * @param float $expectedAmount
     */
    public function testGetTaxAmount($taxAmount, $expectedAmount)
    {
        // Oro\Bundle\TaxBundle\Model\ResultElement is final and cannot be mocked.
        $taxResultElement = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getTaxAmount'])
            ->getMock();
        $taxResultElement
            ->method('getTaxAmount')
            ->willReturn($taxAmount);

        // Oro\Bundle\TaxBundle\Model\Result is final and cannot be mocked.
        $taxResult = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getTotal'])
            ->getMock();
        $taxResult
            ->method('getTotal')
            ->willReturn($taxResultElement);

        $this->taxManager
            ->method('loadTax')
            ->with($this->sourceEntity)
            ->willReturn($taxResult);

        $actual = $this->provider->getTaxAmount($this->paymentContext);

        static::assertSame($expectedAmount, $actual);
    }

    /**
     * @return array
     */
    public function getTaxAmountDataProvider()
    {
        return [
            [self::AMOUNT, self::AMOUNT],
            [self::AMOUNT_NEGLIGIBLE, 0.0],
        ];
    }

    public function testGetTaxAmountIfTaxationIsDisabled()
    {
        $this->taxManager
            ->method('loadTax')
            ->with($this->sourceEntity)
            ->willThrowException(new TaxationDisabledException());

        $actual = $this->provider->getTaxAmount($this->paymentContext);
        static::assertSame(0.0, $actual);
    }

    public function testGetTaxAmountIfIsNotMappable()
    {
        $this->taxManager
            ->method('loadTax')
            ->with($this->sourceEntity)
            ->willThrowException(new UnmappableArgumentException());

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                $this->isType('string'),
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->isEmpty()
                )
            );

        $actual = $this->provider->getTaxAmount($this->paymentContext);
        static::assertSame(0.0, $actual);
    }

    public function testGetTaxAmountIfEntityIsInvalid()
    {
        $this->taxManager
            ->method('loadTax')
            ->with($this->sourceEntity)
            ->willThrowException(new \InvalidArgumentException());

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                $this->isType('string'),
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->isEmpty()
                )
            );

        $actual = $this->provider->getTaxAmount($this->paymentContext);
        static::assertSame(0.0, $actual);
    }
}
