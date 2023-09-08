<?php

namespace app\models\request;

use app\models\data\MerlionData;
use app\models\handlers\MerlionCategoriesHandler;
use app\models\handlers\MerlionProductsHandler;
use app\models\handlers\VendorCategoriesHandler;
use app\models\handlers\VendorProductsHandler;
use app\models\helpers\MerlionHelper;
use app\models\helpers\VendorHelper;
use app\models\services\MerlionCategoriesService;
use app\models\services\MerlionProductsService;
use app\models\services\MerlionService;
use app\models\services\VendorCategoriesService;
use app\models\services\VendorProductsService;
use app\models\services\VendorService;

/**
 * Class MerlionRequest
 * @package app\models\request
 * @author Maxim Protodyakonov <zigfried123@mail.ru>
 * @property MerlionService $_service
 * @property \SoapClient $_soapClient
 * @property MerlionProductsHandler $_productsHandler
 * @property MerlionCategoriesHandler $_categoriesHandler
 * @property MerlionHelper $_helper
 * @property MerlionCategoriesService $_categoriesService
 */
class MerlionRequest extends VendorRequest
{
    private $_service;
    private $_soapClient;
    private $_productsHandler;
    private $_categoriesHandler;
    private $_helper;
    private $_categoriesService;

    public function init()
    {

        ini_set('memory_limit','4096M');

        $this->_helper = VendorHelper::getInstance(MerlionHelper::class);
        $this->_service = VendorService::getInstance(MerlionService::class);
        $this->_productsHandler = VendorProductsHandler::getInstance(MerlionProductsHandler::class);
        $this->_categoriesHandler = VendorCategoriesHandler::getInstance(MerlionCategoriesHandler::class);
        $this->_categoriesHandler->setService(VendorCategoriesService::getInstance(MerlionCategoriesService::class));
        $this->_categoriesService = VendorCategoriesService::getInstance(MerlionCategoriesService::class);
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function execute()
    {
        if (empty($this->_soapClient)) {
            $this->_soapClient = new \SoapClient(MerlionData::URL, MerlionData::PARAMS);
        }

        $categories = $this->_soapClient->getCatalog('All');

        $categories = $this->_helper->getCategories($categories);

        $this->_categoriesHandler->handle($categories);

        $products = $this->getProducts();

        $prices = $this->getProductsPrice(array_keys($products));

        $this->_helper->mapItems($products, $prices);

        $this->_productsHandler->setService(VendorProductsService::getInstance(MerlionProductsService::class));

        $this->_productsHandler->handle($products);

    }

    private function getProducts()
    {
        $rowsPerPage = 500;

        $catCodes = $this->_categoriesService->getCodesFromCategories();

        $data = [];

        $i = 0;

        foreach ($catCodes as $catCode) {

            for($page=1;$page<=10;$page++) {

                sleep(1);

                $products = $this->_soapClient->getItems($catCode, '', '', $page);

                if (count($products->item) <= 1) break;

                foreach ($products->item as $product) {
                    $product = (array)$product;

                    if(trim($product['Vendor_part']) == 'QE49Q6FNAUXRU'){
                        var_dump($product);
                        //file_put_contents('QE49Q6FNAUXRU',json_encode($product, JSON_UNESCAPED_UNICODE), FILE_APPEND);
                    }

                    if (empty($product)) continue;

                    $id = $product['No'];
                    $data[$catCode][$id] = $product;
                }

                echo $catCode . PHP_EOL;

            }

        }

        return $data;

    }

    private function getProductsPrice($catCodes = [], $prices = [])
    {
        foreach ($catCodes as $catCode) {
            $products = $this->_soapClient->getItemsAvail($catCode, 'С/В');

            if (empty($products->item)) continue;

            foreach ($products->item as $product) {
                if (empty($product)) continue;
                $product = (array)$product;

                $id = $product['No'];
                $prices[$catCode][$id] = $product;

                if($id == '1073882'){
                    var_dump($product);
                    file_put_contents('QE49Q6FNAUXRU',json_encode($product, JSON_UNESCAPED_UNICODE), FILE_APPEND);
                }
            }

            sleep(1);
        }

        return $prices;
    }


}