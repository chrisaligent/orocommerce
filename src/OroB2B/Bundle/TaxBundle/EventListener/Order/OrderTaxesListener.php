<?php

namespace OroB2B\Bundle\TaxBundle\EventListener\Order;

use OroB2B\Bundle\OrderBundle\Event\OrderEvent;
use OroB2B\Bundle\TaxBundle\Manager\TaxManager;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use OroB2B\Bundle\TaxBundle\Model\Result;

class OrderTaxesListener
{
    /** @var TaxManager */
    protected $taxManager;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /**
     * @param TaxManager $taxManager
     * @param NumberFormatter $numberFormatter
     */
    public function __construct(TaxManager $taxManager, NumberFormatter $numberFormatter)
    {
        $this->taxManager = $taxManager;
        $this->numberFormatter = $numberFormatter;
    }

    /**
     * @param OrderEvent $event
     */
    public function onOrderEvent(OrderEvent $event)
    {
        $order = $event->getOrder();

        $result = $this->taxManager->getTax($order);
        $event->getData()->offsetSet('taxesTotal', $result->offsetGet(Result::TOTAL)->getArrayCopy());

        $taxesItems = [];
        /** @var \ArrayObject $lineItem */
        foreach ($result->offsetGet(Result::ITEMS) as $lineItem) {
            $taxesItem = [
                'unit' => $lineItem->offsetGet(Result::UNIT)->getArrayCopy(),
                'row' => $lineItem->offsetGet(Result::ROW)->getArrayCopy()
            ];

            $itemTaxes = $lineItem->offsetGet(Result::TAXES);
            if (!empty($itemTaxes)) {
                $taxes = [];
                /** @var \ArrayObject $itemTax */
                foreach ($itemTaxes as $itemTax) {
                    $tax = $itemTax->getArrayCopy();
                    $tax['rate'] = $this->numberFormatter->formatPercent($tax['rate']);
                    $taxes[] = $tax;
                }
                $taxesItem['taxes'] = $taxes;
            }

            $taxesItems[] = $taxesItem;
        }
        $event->getData()->offsetSet('taxesItems', $taxesItems);
    }
}
