<?php

use app\modules\budget\models\Currency;
use app\modules\budget\resources\ResourceCurrency;
use app\modules\budget\services\CurrencyService;
use Codeception\Test\Unit;
use yii\db\Connection;

class BudgetCurrencyServiceTest extends Unit
{
    /**
     * @var int
     */
    private $key = -201;

    /**
     * @var string $code
     */
    private $code = '-101';

    /**
     * @var string $name
     */
    private $name = 'Рубли';

    /**
     * @var ResourceCurrency $firstResource
     */
    private $firstResource;

    /**
     * @var CurrencyService $service
     */
    protected $service;

    /** @var Connection */
    private $dbConnection;

    /** @var array */
    private $allResources;

    protected function _inject(CurrencyService $service)
    {
        $this->service = $service;
    }

    protected function setUp()
    {
        $this->dbConnection = Yii::$app->dbPlanfixSync;
        $this->dbConnection->beginTransaction();
        $this->dbConnection->createCommand('truncate table ' . Currency::$table)->execute();
        $this->firstResource = new ResourceCurrency($this->key, $this->code, $this->name);
        $this->getResources();

        return parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function testAdd()
    {
        $result = $this->service->add($this->allResources[0]);
        $this->assertEquals($this->allResources[0]->getKey(), $this->key);
        $this->assertEquals($this->allResources[0]->getCode(), $this->code);
        $this->assertEquals($this->allResources[0]->getName(), $this->name);
        $this->assertTrue($result);

        $this->assertNotEquals($this->allResources[1]->getKey(), $this->key);
    }

    public function testDelete()
    {
        $this->addAllResourcesToDB();
        $model = Currency::find()->where(['key' => $this->key])->one();
        $result = $this->service->delete($model);
        $this->assertTrue($result);
    }

    public function testExtractNew()
    {
        $this->addAllResourcesToDB();
        $this->assertEquals($this->service->extractNew([$this->firstResource]), []);
        $resourceForAddOne = new ResourceCurrency(-205, '-105', 'Тугрик');
        $resourceForAddTwo = new ResourceCurrency(-206, '-106', 'Гривна');

        $this->assertEquals(
            $this->service->extractNew([$resourceForAddOne, $resourceForAddTwo]),
            [$resourceForAddOne, $resourceForAddTwo]
        );
    }

    public function testExtractDelete()
    {
        $this->addAllResourcesToDB();
        $this->assertCount(
            2,
            $this->service->extractDelete([
                $this->allResources[0],
                $this->allResources[1],
            ]));
        $resourceForDelete= new ResourceCurrency(-205, '-105', 'Тугрик');

        $this->assertCount(4, $this->service->extractDelete([$resourceForDelete]));

    }

    public function testExtractUpdate()
    {
        $this->addAllResourcesToDB();
        $newName = 'Фунты';
        $changeResource = new ResourceCurrency($this->key, $this->code, $newName);
        $result = $this->service->extractUpdate([$changeResource]);
        $this->assertArrayHasKey($this->key, $result);
        $this->assertEquals($result[$this->key]['resource'], $changeResource);
        $model = $result[$this->key]['model'];

        $this->assertEquals($model->name, $this->name);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->dbConnection->transaction->rollBack();
    }


    private function getResources()
    {
        $data = require __DIR__ . './../_data/BudgetСurrencyData.php';

        foreach ($data as $item) {
            $this->allResources[] = new ResourceCurrency(
                $item['item']['key'],
                $item['item']['code'],
                $item['item']['name']
            );
        }
    }


    private function addAllResourcesToDB()
    {
        foreach ($this->allResources as $item) {
            $this->service->add($item);
        }
    }
}
