<?php

namespace Oro\Bundle\PricingBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Oro\Bundle\PricingBundle\Entity\ProductPrice;

class ProductPriceDeleteOperationTest extends ActionTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(['Oro\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductPrices']);
    }

    public function testDelete()
    {
        /** @var ProductPrice $productPrice */
        $productPrice = $this->getReference('product_price.1');

        $this->assertExecuteOperation(
            'DELETE',
            $productPrice->getId(),
            $this->getContainer()->getParameter('oro_pricing.entity.product_price.class'),
            ['datagrid' => 'price-list-product-prices-grid']
        );

        $this->assertEquals(
            [
                'success' => true,
                'message' => '',
                'messages' => [],
                'refreshGrid' => [
                    'price-list-product-prices-grid'
                ],
                'flashMessages' => [
                    'success' => ['Product Price deleted']
                ],
                'pageReload' => true
            ],
            json_decode($this->client->getResponse()->getContent(), true)
        );
    }
}
