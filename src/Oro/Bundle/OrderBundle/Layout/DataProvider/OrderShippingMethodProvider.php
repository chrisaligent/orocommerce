<?php

namespace Oro\Bundle\OrderBundle\Layout\DataProvider;

use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;

class OrderShippingMethodProvider
{
    /**
     * @var ShippingMethodLabelFormatter
     */
    protected $shippingMethodLabelFormatter;

    /**
     * @param ShippingMethodLabelFormatter $shippingMethodLabelFormatter
     */
    public function __construct(ShippingMethodLabelFormatter $shippingMethodLabelFormatter)
    {
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(Order $order)
    {
        $methodLabel = $this->shippingMethodLabelFormatter->formatShippingMethodLabel($order->getShippingMethod());

        $methodTypeLabel = $this->shippingMethodLabelFormatter->formatShippingMethodTypeLabel(
            $order->getShippingMethod(),
            $order->getShippingMethodType()
        );

        return implode(', ', array_filter([$methodLabel, $methodTypeLabel]));
    }
}
