<?php

namespace app\modules\budget\services;

use app\modules\budget\models\Currency;
use app\modules\budget\repositories\CurrencyRepository;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\resources\ResourceCurrency;
use app\modules\budget\services\exceptions\ServiceException;

class CurrencyService
{
    /**
     * @var CurrencyRepository $currencies
     */
    private $currencies;

    /**
     * CurrencyService constructor.
     * @param CurrencyRepository $currencies
     */
    public function __construct(CurrencyRepository $currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * @param ResourceCurrency $resource
     * @return bool
     * @throws ServiceException
     */
    public function add(ResourceCurrency $resource): bool
    {
        try {
            $this->currencies->save(
                new Currency([
                    'key' => $resource->getKey(),
                    'code' => $resource->getCode(),
                    'name' => $resource->getName()
                ])
            );
            return true;
        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param Currency $model
     * @return bool
     * @throws ServiceException
     */
    public function update(Currency $model): bool
    {
        try {
            $this->currencies->save($model);
            return true;

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param Currency $model
     * @return bool
     * @throws ServiceException
     */
    public function delete(Currency $model): bool
    {
        try {
            $this->currencies->delete($model);
            return true;

        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ResourceCurrency[] $resources
     * @return ResourceCurrency[]
     */
    public function extractNew(array $resources): array
    {
        $result = [];
        $modelKeys = $this->currencies->getKeys();
        foreach ($resources as $resource) {
            if (!in_array($resource->getKey(), $modelKeys)) {
                $result[] = $resource;
            }
        }

        return $result;
    }

    /**
     * @param ResourceCurrency[] $resources
     * @return Currency[]
     */
    public function extractDelete(array $resources): array
    {
        $keysForDelete = [];
        foreach ($resources as $resource) {
            $keysForDelete[] = $resource->getKey();
        }

        $result = $this->currencies->getAll();
        foreach ($result as $key => $model) {

            if (in_array($model->key, $keysForDelete)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @param ResourceCurrency[] $resources
     * @return array
     */
    public function extractUpdate(array $resources): array
    {
        $result = [];
        $models = $this->currencies->getAll();

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
