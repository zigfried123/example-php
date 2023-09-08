<?php

namespace app\modules\budget\bootstrap;

use app\components\GoogleApiComponent;
use app\modules\budget\clients\PlanfixClient;
use app\modules\budget\providers\contracts\BudgetDepartmentProviderInterface;
use app\modules\budget\providers\contracts\BudgetTaskProviderInterface;
use app\modules\budget\providers\contracts\CostItemProviderInterface;
use app\modules\budget\providers\contracts\CurrencyProviderInterface;
use app\modules\budget\providers\contracts\ManagementPeriodProviderInterface;
use app\modules\budget\providers\contracts\PaymentSystemProviderInterface;
use app\modules\budget\providers\contracts\ProjectProviderInterface;
use app\modules\budget\providers\PlanfixBudgetDepartmentProvider;
use app\modules\budget\providers\PlanfixBudgetTaskProvider;
use app\modules\budget\providers\PlanfixCostItemProvider;
use app\modules\budget\providers\PlanfixCurrencyProvider;
use app\modules\budget\providers\PlanfixManagementPeriodProvider;
use app\modules\budget\providers\PlanfixPaymentSystemProvider;
use app\modules\budget\providers\PlanfixProjectProvider;
use app\modules\budget\providers\PlanfixProvider;
use Yii;
use yii\base\BootstrapInterface;
use yii\di\Container;

class DIContainer implements BootstrapInterface
{
    /** @inheritdoc */
    public function bootstrap($app)
    {
        $handbookParams = Yii::$app->params['planfix']['config']['handbooks'];
        $taskTemplatesParams = Yii::$app->params['planfix']['config']['taskTemplates'];

        Yii::$container->setSingletons([
            GoogleApiComponent::class => function ($container, $params, $config) {
                $component = new GoogleApiComponent();
                return $component;
            },

            CostItemProviderInterface::class => function (Container $container) use($handbookParams): PlanfixCostItemProvider {
                /** @var PlanfixProvider $planfixProvider */
                $planfixProvider = $container->get(PlanfixProvider::class);

                return new PlanfixCostItemProvider(
                    $planfixProvider,
                    $handbookParams['costItem']['id'],
                    $handbookParams['costItem']['fieldNameId'],
                    $handbookParams['costItem']['fieldCodeId']
                );
            },

            BudgetDepartmentProviderInterface::class => function (Container $container) use($handbookParams): PlanfixBudgetDepartmentProvider {
                /** @var PlanfixProvider $planfixProvider */
                $planfixProvider = $container->get(PlanfixProvider::class);

                return new PlanfixBudgetDepartmentProvider(
                    $planfixProvider,
                    $handbookParams['department']['id'],
                    $handbookParams['department']['fieldNameId'],
                    $handbookParams['department']['fieldCodeId']
                );
            },

            CurrencyProviderInterface::class => function (Container $container) use($handbookParams): PlanfixCurrencyProvider {
                /** @var PlanfixProvider $planfixProvider */
                $planfixProvider = $container->get(PlanfixProvider::class);

                return new PlanfixCurrencyProvider(
                    $planfixProvider,
                    $handbookParams['currency']['id'],
                    $handbookParams['currency']['fieldNameId'],
                    $handbookParams['currency']['fieldCodeId']
                );
            },

            ManagementPeriodProviderInterface::class => function (Container $container) use($handbookParams): PlanfixManagementPeriodProvider {
                /** @var PlanfixProvider $planfixProvider */
                $planfixProvider = $container->get(PlanfixProvider::class);

                return new PlanfixManagementPeriodProvider(
                    $planfixProvider,
                    $handbookParams['managementPeriod']['id'],
                    $handbookParams['managementPeriod']['fieldNameId'],
                    $handbookParams['managementPeriod']['fieldDateId']
                );
            },

            PaymentSystemProviderInterface::class => function (Container $container) use($handbookParams): PlanfixPaymentSystemProvider {
                /** @var PlanfixProvider $planfixProvider */
                $planfixProvider = $container->get(PlanfixProvider::class);

                return new PlanfixPaymentSystemProvider(
                    $planfixProvider,
                    $handbookParams['paymentSystem']['id'],
                    $handbookParams['paymentSystem']['fieldNameId']
                );
            },

            ProjectProviderInterface::class => function (Container $container) use($handbookParams): PlanfixProjectProvider {
                /** @var PlanfixProvider $planfixProvider */
                $planfixProvider = $container->get(PlanfixProvider::class);

                return new PlanfixProjectProvider(
                    $planfixProvider,
                    $handbookParams['project']['id'],
                    $handbookParams['project']['fieldNameId'],
                    $handbookParams['project']['fieldCodeId']
                );
            },

            BudgetTaskProviderInterface::class => function (Container $container) use($taskTemplatesParams): PlanfixBudgetTaskProvider {
                /** @var PlanfixClient $planfixClient */
                $planfixClient = $container->get(PlanfixClient::class);

                return new PlanfixBudgetTaskProvider(
                    $planfixClient,
                    $taskTemplatesParams['budget']['fields']['paymentSystem'],
                    $taskTemplatesParams['budget']['fields']['department'],
                    $taskTemplatesParams['budget']['fields']['managementPeriod'],
                    $taskTemplatesParams['budget']['fields']['project'],
                    $taskTemplatesParams['budget']['fields']['itemCost'],
                    $taskTemplatesParams['budget']['fields']['currency'],
                    $taskTemplatesParams['budget']['fields']['amountPaid'],
                    $taskTemplatesParams['budget']['fields']['amountInCurrency'],
                    $taskTemplatesParams['budget']['fields']['amountInRub']
                );
            },
        ]);
    }
}
