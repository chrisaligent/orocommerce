<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

class AccountUserRelationsProvider
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param ConfigManager $configManager
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(ConfigManager $configManager, DoctrineHelper $doctrineHelper)
    {
        $this->configManager = $configManager;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param CustomerUser|null $accountUser
     * @return null|Account
     */
    public function getAccount(CustomerUser $accountUser = null)
    {
        if ($accountUser) {
            return $accountUser->getAccount();
        }

        return null;
    }

    /**
     * @param CustomerUser|null $accountUser
     * @return null|CustomerGroup
     */
    public function getAccountGroup(CustomerUser $accountUser = null)
    {
        if ($accountUser) {
            $account = $this->getAccount($accountUser);
            if ($account) {
                return $account->getGroup();
            }
        } else {
            $anonymousGroupId = $this->configManager->get('oro_customer.anonymous_account_group');

            if ($anonymousGroupId) {
                return $this->doctrineHelper->getEntityReference(
                    'OroCustomerBundle:CustomerGroup',
                    $anonymousGroupId
                );
            }
        }

        return null;
    }

    /**
     * @param CustomerUser|null $accountUser
     * @return null|Account
     */
    public function getAccountIncludingEmpty(CustomerUser $accountUser = null)
    {
        $account = $this->getAccount($accountUser);
        if (!$account) {
            $account = new Account();
            $account->setGroup($this->getAccountGroup($accountUser));
        }
        
        return $account;
    }
}
