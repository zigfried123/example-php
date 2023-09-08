<?php

namespace app\models\request;

use app\models\common\Curl;
use app\models\data\OcsData;
use app\models\handlers\OcsProductsHandler;
use app\models\handlers\VendorProductsHandler;
use app\models\helpers\OcsHelper;
use app\models\helpers\VendorHelper;
use app\models\services\OcsProductsService;
use app\models\services\VendorProductsService;

/**
 * Class OcsRequest
 * @package app\models\request
 * @author Maxim Protodyakonov <zigfried123@mail.ru>
 * @property OcsHelper $_helper
 */
class OcsRequest extends VendorRequest
{
    private $_helper;

    public function __construct(array $config = [])
    {
        $this->_helper = VendorHelper::getInstance(OcsHelper::class);

        parent::__construct($config);
    }

    public function execute()
    {
        $categories = $this->getEntity('categories', OcsData::URL . '/GetCategories');

        $categories = array_map(function ($val) use ($categories) {

            $key = array_search($val['ParentCategoryID'], array_column($categories, 'CategoryID'));

            $val['ParentCategoryName'] = $categories[$key]['CategoryName'];

            return $val;
        }, $categories);


        try {
            \Yii::$app->dbHalva->createCommand()->batchInsert('ocs_category', ['code', 'name', 'parent_id', 'level', 'parent_name'], $categories)->execute();
        }catch (\Exception $e){
            echo $e->getMessage(); die;
        }


        $this->_helper->setCategories($categories);

        $products = $this->getEntity('products', OcsData::URL . '/GetProductAvailability');

        $handler = VendorProductsHandler::getInstance(OcsProductsHandler::class);

        /** @var OcsProductsHandler $handler */


        $handler->setService(VendorProductsService::getInstance(OcsProductsService::class));

        $handler->handle($products);
    }

    private function getEntity($entity, $url)
    {
        $headers = [
            "Content-Type: application/json; charset=utf-8"
        ];

        $curl = new Curl($url, json_encode(OcsData::POST_DATA), $headers);

        $response = json_decode($curl->execute(), true);

        /** @var OcsHelper $helper */
        if ($entity == 'products') {
            $data = $this->_helper->getProducts($response);
        } elseif ($entity == 'categories') {
            $data = $this->_helper->getCategories($response);
        }

        return $data;
    }

}