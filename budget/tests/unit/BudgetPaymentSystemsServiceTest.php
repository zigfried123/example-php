<?php

use app\modules\budget\models\PaymentSystem;
use app\modules\budget\resources\ResourcePaymentSystem;
use app\modules\budget\services\PaymentSystemService;
use Codeception\Test\Unit;
use yii\db\Connection;

class BudgetPaymentSystemsTest extends Unit
{
    private $key = -201;
    /**
     * @var string $name
     */
    private $name = 'VISA';

    /**
     * @var ResourcePaymentSystem $firstResource
     */
    private $firstResource;

    /**
     * @var PaymentSystemService $service
     */
    protected $service;

    /** @var Connection */
    private $dbConnection;

    /** @var array */
    private $allResources;

    protected function _inject(PaymentSystemService $service)
    {
        $this->service = $service;
    }

    protected function setUp()
    {
        $this->dbConnection = Yii::$app->dbPlanfixSync;
        $this->dbConnection->beginTransaction();
        $this->dbConnection->createCommand('truncate table ' . PaymentSystem::$table)->execute();
        $this->firstResource = new ResourcePaymentSystem($this->key, $this->name);
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
        $this->assertEquals($this->allResources[0]->getName(), $this->name);
        $this->assertTrue($result);
    }

    public function testDelete()
    {
        $this->addAllResourcesToDB();
        $model = PaymentSystem::find()->where(['key' => $this->key])->one();
        $result = $this->service->delete($model);
        $this->assertTrue($result);
    }

    public function testExtractNew()
    {
        $this->addAllResourcesToDB();
        $this->assertEquals($this->service->extractNew([$this->firstResource]), []);
        $resourceForAddOne = new ResourcePaymentSystem(-1000, 'Dinners club');
        $resourceForAddTwo = new ResourcePaymentSystem(-2000, 'American Express');

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
        $resourceForDelete = new ResourcePaymentSystem(-3000, 'Dinners club');

        $this->assertCount(4, $this->service->extractDelete([$resourceForDelete]));

    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->dbConnection->transaction->rollBack();
    }

    private function getResources()
    {
        $data = require __DIR__ . './../_data/BudgetPaymentSystemData.php';

        foreach ($data as $item) {
            $this->allResources[] = new ResourcePaymentSystem(
                $item['item']['key'],
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
