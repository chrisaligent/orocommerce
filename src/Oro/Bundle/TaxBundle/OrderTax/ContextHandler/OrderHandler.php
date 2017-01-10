<?php

namespace Oro\Bundle\TaxBundle\OrderTax\ContextHandler;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\TaxBundle\Entity\AccountTaxCode;
use Oro\Bundle\TaxBundle\Entity\Repository\AccountTaxCodeRepository;
use Oro\Bundle\TaxBundle\Event\ContextEvent;
use Oro\Bundle\TaxBundle\Model\Taxable;
use Oro\Bundle\TaxBundle\Model\TaxCodeInterface;

class OrderHandler
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var TaxCodeInterface[]
     */
    protected $taxCodes = [];

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param ContextEvent $contextEvent
     */
    public function onContextEvent(ContextEvent $contextEvent)
    {
        $order = $contextEvent->getMappingObject();
        $context = $contextEvent->getContext();

        if (!$order instanceof Order) {
            return;
        }

        $context->offsetSet(Taxable::ACCOUNT_TAX_CODE, $this->getAccountTaxCode($order));
    }

    /**
     * @param Order $order
     * @return null|string
     */
    protected function getAccountTaxCode(Order $order)
    {
        $cacheKey  = $this->getCacheTaxCodeKey(TaxCodeInterface::TYPE_ACCOUNT, $order);
        $cachedTaxCode = $this->getCachedTaxCode($cacheKey);

        if ($cachedTaxCode !== false) {
            return $cachedTaxCode;
        }

        $taxCode = null;
        $account = null;

        if ($order->getAccount()) {
            $taxCode = $this->getTaxCode(TaxCodeInterface::TYPE_ACCOUNT, $order->getAccount());
        }

        if (!$taxCode && $order->getAccount() && $order->getAccount()->getGroup()) {
            $taxCode = $this->getTaxCode(TaxCodeInterface::TYPE_ACCOUNT_GROUP, $order->getAccount()->getGroup());
        }

        $this->taxCodes[$cacheKey] = $taxCode;

        return $taxCode;
    }

    /**
     * @param string $type
     * @param object $object
     * @return string|null
     */
    protected function getTaxCode($type, $object)
    {
        $taxCode = $this->getRepository()->findOneByEntity($type, $object);

        return $taxCode ? $taxCode->getCode() : null;
    }

    /**
     * @return AccountTaxCodeRepository
     */
    protected function getRepository()
    {
        return $this->doctrineHelper->getEntityRepositoryForClass(AccountTaxCode::class);
    }

    /**
     * @param string $type
     * @param Order $order
     * @return string
     */
    protected function getCacheTaxCodeKey($type, Order $order)
    {
        $id = $order->getId() ?: spl_object_hash($order);

        return implode(':', [$type, $id]);
    }

    /**
     * @param string $cacheKey
     * @return null|TaxCodeInterface
     */
    protected function getCachedTaxCode($cacheKey)
    {
        if (!array_key_exists($cacheKey, $this->taxCodes)) {
            return false;
        }

        return $this->taxCodes[$cacheKey];
    }
}
