<?php

namespace app\modules\budget\services;

use app\modules\budget\models\PaymentSystem;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\repositories\PaymentSystemsRepository;
use app\modules\budget\resources\ResourcePaymentSystem;
use app\modules\budget\services\exceptions\ServiceException;

class PaymentSystemService
{
    /**
     * @var PaymentSystemsRepository $paymentSystems
     */
    private $paymentSystems;

    /**
     * PaymentSystemService constructor.
     * @param PaymentSystemsRepository $paymentSystems
     */
    public function __construct(PaymentSystemsRepository $paymentSystems)
    {
        $this->paymentSystems = $paymentSystems;
    }


    /**
     * @param ResourcePaymentSystem $resource
     * @return bool
     * @throws ServiceException
     */
    public function add(ResourcePaymentSystem $resource): bool
    {
        try {
            $this->paymentSystems->save(
                new PaymentSystem([
                    'key' => $resource->getKey(),
                    'name' => $resource->getName()
                ])
            );
            return true;
        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param PaymentSystem $model
     * @return bool
     * @throws ServiceException
     */
    public function update(PaymentSystem $model): bool
    {
        try {
            $this->paymentSystems->save($model);
            return true;

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param PaymentSystem $model
     * @return bool
     * @throws ServiceException
     */
    public function delete(PaymentSystem $model): bool
    {
        try {
            $this->paymentSystems->delete($model);
            return true;

        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ResourcePaymentSystem[] $resources
     * @return ResourcePaymentSystem[]
     */
    public function extractNew(array $resources): array
    {
        $result = [];
        $modelKeys = $this->paymentSystems->getKeys();
        foreach ($resources as $resource) {
            if (!in_array($resource->getKey(), $modelKeys)) {
                $result[] = $resource;
            }
        }

        return $result;
    }

    /**
     * @param ResourcePaymentSystem[] $resources
     * @return PaymentSystem[]
     */
    public function extractDelete(array $resources): array
    {
        $keysForDelete = [];
        foreach ($resources as $resource) {
            $keysForDelete[] = $resource->getKey();
        }

        $result = $this->paymentSystems->getAll();
        foreach ($result as $key => $model) {

            if (in_array($model->key, $keysForDelete)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @param ResourcePaymentSystem[] $resources
     * @return array
     */
    public function extractUpdate(array $resources): array
    {
        $result = [];
        $models = $this->paymentSystems->getAll();

        foreach ($resources as $resource) {
            foreach ($models as $model) {
                if ($model->key === $resource->getKey()) {
                    if ($resource->getName() !== $model->name) {
                        $result[$resource->getKey()] = ['model' => $model, 'resource' => $resource];
                    }
                }
            }
        }
        return $result;
    }
}
