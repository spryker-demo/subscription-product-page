<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerDemo\Yves\SubscriptionProductPage;

use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Client\Sales\SalesClientInterface;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerDemo\Client\SubscriptionProductOms\SubscriptionProductOmsClientInterface;
use SprykerDemo\Yves\SubscriptionProductPage\Form\FormFactory;
use SprykerDemo\Yves\SubscriptionProductPage\Reader\CustomerOrdersReader;

class SubscriptionProductPageFactory extends AbstractFactory
{
    /**
     * @return \SprykerDemo\Yves\SubscriptionProductPage\Form\FormFactory
     */
    public function createSubscriptionProductFormFactory(): FormFactory
    {
        return new FormFactory();
    }

    /**
     * @return \SprykerDemo\Yves\SubscriptionProductPage\Reader\CustomerOrdersReader
     */
    public function createOrderReader(): CustomerOrdersReader
    {
        return new CustomerOrdersReader(
            $this->getCustomerClient(),
            $this->getSalesClient(),
        );
    }

    /**
     * @return \SprykerDemo\Client\SubscriptionProductOms\SubscriptionProductOmsClientInterface
     */
    public function getSubscriptionProductOmsClient(): SubscriptionProductOmsClientInterface
    {
        return $this->getProvidedDependency(SubscriptionProductPageDependencyProvider::CLIENT_SUBSCRIPTION_PRODUCT_OMS);
    }

    /**
     * @return \Spryker\Client\Customer\CustomerClientInterface
     */
    public function getCustomerClient(): CustomerClientInterface
    {
        return $this->getProvidedDependency(SubscriptionProductPageDependencyProvider::CLIENT_CUSTOMER);
    }

    /**
     * @return \Spryker\Client\Sales\SalesClientInterface
     */
    public function getSalesClient(): SalesClientInterface
    {
        return $this->getProvidedDependency(SubscriptionProductPageDependencyProvider::CLIENT_SALES);
    }

    /**
     * @return \Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface
     */
    public function getGlossaryClient(): GlossaryStorageClientInterface
    {
        return $this->getProvidedDependency(SubscriptionProductPageDependencyProvider::CLIENT_GLOSSARY);
    }
}
