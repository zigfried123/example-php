<?php

use app\modules\budget\models\CostItem;
use app\modules\budget\resources\ResourceCostItem;
use app\modules\budget\services\CostItemService;
use Codeception\Test\Unit;
use yii\db\Connection;

class BudgetCostItemServiceTest extends Unit
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
    private $name = 'Канцелярия';

    /**
     * @var ResourceCostItem $firstResource
     */
    private $firstResource;

    /**
     * @var CostItemService $service
     */
    protected $service;

    /** @var Connection */
    private $dbConnection;

    /** @var array */
    private $allResources;

    protected function _inject(CostItemService $service)
    {
        $this->service = $service;
    }

    protected function setUp()
    {
        $this->dbConnection = Yii::$app->dbPlanfixSync;
        $this->dbConnection->beginTransaction();
        $this->dbConnection->createCommand('truncate table ' . CostItem::$table)->execute();
        $this->firstResource = new ResourceCostItem($this->key, $this->code, $this->name);
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
        $model = CostItem::find()->where(['key' => $this->key])->one();
        $result = $this->service->delete($model);
        $this->assertTrue($result);
    }

    public function testExtractNew()
    {
        $this->addAllResourcesToDB();
        $this->assertEquals($this->service->extractNew([$this->firstResource]), []);
        $resourceForAddOne = new ResourceCostItem(-1000, '-105', 'Спецодежда');
        $resourceForAddTwo = new ResourceCostItem(-2000, '-106', 'Маски');

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
        $resourceForDelete= new ResourceCostItem(-9000, '-105', 'Новая статья затрат');

        $this->assertCount(4, $this->service->extractDelete([$resourceForDelete]));

    }

    public function testExtractUpdate()
    {
        $this->addAllResourcesToDB();
        $newName = 'Новая статья затрат';
        $changeResource = new ResourceCostItem($this->key, $this->code, $newName);
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
        $data = require __DIR__ . './../_data/BudgetCostItemsData.php';

        foreach ($data as $item) {
            $this->allResources[] = new ResourceCostItem(
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
