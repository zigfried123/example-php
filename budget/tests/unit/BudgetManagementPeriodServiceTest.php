<?php

use app\modules\budget\models\ManagementPeriod;
use app\modules\budget\resources\ResourceManagementPeriod;
use app\modules\budget\services\ManagementPeriodService;
use Codeception\Test\Unit;
use yii\db\Connection;

class BudgetManagementPeriodServiceTest extends Unit
{
    /** @var int int */
    private $key = -201;
    /**
     * @var string $name
     */
    private $name = 'Январь';

    private $date = '01-01-2020';

    /**
     * @var ResourceManagementPeriod $firstResource
     */
    private $firstResource;

    /**
     * @var ManagementPeriodService $service
     */
    protected $service;

    /** @var Connection */
    private $dbConnection;

    /** @var array */
    private $allResources;

    protected function _inject(ManagementPeriodService $service)
    {
        $this->service = $service;
    }

    protected function setUp()
    {
        $this->dbConnection = Yii::$app->dbPlanfixSync;
        $this->dbConnection->beginTransaction();
        $this->dbConnection->createCommand('truncate table ' . ManagementPeriod::$table)->execute();
        $this->firstResource = new ResourceManagementPeriod($this->key, $this->date, $this->name);
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
        $model = ManagementPeriod::find()->where(['key' => $this->key])->one();
        $result = $this->service->delete($model);
        $this->assertTrue($result);
    }

    public function testExtractNew()
    {
        $this->addAllResourcesToDB();
        $this->assertEquals($this->service->extractNew([$this->firstResource]), []);
        $resourceForAddOne = new ResourceManagementPeriod(-214, '01-05-2020','Май');
        $resourceForAddTwo = new ResourceManagementPeriod(-215, '01-06-2020','Июнь');

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
        $resourceForDelete = new ResourceManagementPeriod(-206, '01-05-2020','Май');

        $this->assertCount(4, $this->service->extractDelete([$resourceForDelete]));

    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->dbConnection->transaction->rollBack();
    }

    private function getResources()
    {
        $data = require __DIR__ . './../_data/BudgetManagementPeriodData.php';

        foreach ($data as $item) {
            $this->allResources[] = new ResourceManagementPeriod(
                $item['item']['key'],
                $item['item']['date'],
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
