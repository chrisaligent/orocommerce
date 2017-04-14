<?php

namespace Oro\Bundle\PaymentBundle\Context\Factory;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class TransactionPaymentContextFactory implements TransactionPaymentContextFactoryInterface
{
    /**
     * @var CompositeSupportsEntityPaymentContextFactory
     */
    private $compositeFactory;

    /**
     * @param CompositeSupportsEntityPaymentContextFactory $compositeFactory
     */
    public function __construct(CompositeSupportsEntityPaymentContextFactory $compositeFactory)
    {
        $this->compositeFactory = $compositeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(PaymentTransaction $transaction)
    {
        return $this->compositeFactory->create($transaction->getEntityClass(), $transaction->getEntityIdentifier());
    }
}
