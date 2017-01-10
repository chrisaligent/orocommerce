<?php

namespace Oro\Bundle\SaleBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SaleBundle\Form\Type\QuoteType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @dbIsolation
 */
class AjaxQuoteControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                'Oro\Bundle\SaleBundle\Tests\Functional\DataFixtures\LoadQuoteData'
            ]
        );
    }

    /**
     * @dataProvider getRelatedDataActionDataProvider
     *
     * @param string $account
     * @param string|null $accountUser
     */
    public function testGetRelatedDataAction($account, $accountUser = null)
    {
        /** @var Account $order */
        $accountEntity = $this->getReference($account);

        /** @var CustomerUser $order */
        $accountUserEntity = $accountUser ? $this->getReference($accountUser) : null;

        $this->client->request(
            'GET',
            $this->getUrl('oro_quote_related_data'),
            [
                QuoteType::NAME => [
                    'account' => $accountEntity->getId(),
                    'accountUser' => $accountUserEntity ? $accountUserEntity->getId() : null
                ]
            ]
        );

        $response = $this->client->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('shippingAddress', $result);
        $this->assertArrayHasKey('accountPaymentTerm', $result);
        $this->assertArrayHasKey('accountGroupPaymentTerm', $result);
    }

    /**
     * @return array
     */
    public function getRelatedDataActionDataProvider()
    {
        return [
            [
                'account' => 'sale-account1',
                'accountUser' => 'sale-account1-user1@example.com'
            ],
            [
                'account' => 'sale-account1',
                'accountUser' => null
            ]
        ];
    }

    public function testGetRelatedDataActionException()
    {
        /** @var CustomerUser $accountUser1 */
        $accountUser1 = $this->getReference('sale-account1-user1@example.com');

        /** @var CustomerUser $accountUser2 */
        $accountUser2 = $this->getReference('sale-account2-user1@example.com');

        $this->client->request(
            'GET',
            $this->getUrl('oro_quote_related_data'),
            [
                QuoteType::NAME => [
                    'account' => $accountUser1->getAccount()->getId(),
                    'accountUser' => $accountUser2->getId(),
                ]
            ]
        );

        $response = $this->client->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertResponseStatusCodeEquals($response, 400);
    }

    public function testEntryPoint()
    {
        $this->client->request('GET', $this->getUrl('oro_quote_entry_point'));
        $response = $this->client->getResponse();

        static::assertInstanceOf(JsonResponse::class, $response);
    }
}
