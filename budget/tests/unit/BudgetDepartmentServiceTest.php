<?php
use app\modules\budget\models\BudgetDepartment;
use app\modules\budget\resources\ResourceBudgetDepartment;
use app\modules\budget\services\BudgetDepartmentService;
use Codeception\Test\Unit;
use yii\db\Connection;

class BudgetDepartmentServiceTest extends Unit
{
    /**
     * @var int
     */
    private $key = -201;

    /**
     * @var string $departmentCode
     */
    private $departmentCode = '-101';

    /**
     * @var string $departmentName
     */
    private $departmentName = 'Главный офис';

    private $firstResource;

    /** @var array $allResources */
    private $allResources = [];

    /**
     * @var BudgetDepartmentService $service
     */
    protected $service;

    /** @var Connection */
    private $dbConnection;

    protected function _inject(BudgetDepartmentService $departmentService)
    {
        $this->service = $departmentService;
    }

    protected function setUp()
    {
        $this->dbConnection = Yii::$app->dbPlanfixSync;
        $this->dbConnection->beginTransaction();
        $this->dbConnection->createCommand('truncate table ' . BudgetDepartment::$table)->execute();
        $this->firstResource = new ResourceBudgetDepartment($this->key, $this->departmentCode, $this->departmentName);
        $this->getResources();

        return parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function testAdd()
    {
        $result = $this->service->add($this->allResources[0]);

        $this->assertEquals($this->firstResource->getKey(), $this->key);
        $this->assertEquals($this->firstResource->getCode(), $this->departmentCode);
        $this->assertEquals($this->firstResource->getName(), $this->departmentName);
        $this->assertTrue($result);
    }

    public function testDelete()
    {
        $this->addAllResourcesToDB();

        $model = BudgetDepartment::find()->where(['key' => $this->key])->one();
        $result = $this->service->delete($model);
        $this->assertTrue($result);
    }

    public function testExtractNew()
    {
        $this->addAllResourcesToDB();
        $this->assertEquals($this->service->extractNew([$this->firstResource]), []);
        $resourceForAddOne = new ResourceBudgetDepartment(-205, '-105', 'Юридический');
        $resourceForAddTwo = new ResourceBudgetDepartment(-206, '-106', 'Хозблок');

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
        $resourceForDelete= new ResourceBudgetDepartment(-205, '-105', 'HQ');

        $this->assertCount(4, $this->service->extractDelete([$resourceForDelete]));

    }

    public function testExtractUpdate()
    {
        $this->addAllResourcesToDB();

        $newName = 'Heads office';
        $changeResource = new ResourceBudgetDepartment($this->key, $this->departmentCode, $newName);
        $result = $this->service->extractUpdate([$changeResource]);

        $this->assertArrayHasKey($this->key, $result);
        $this->assertEquals($result[$this->key]['resource'], $changeResource);
        $model = $result[$this->key]['model'];

        $this->assertEquals($model->name, $this->departmentName);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->dbConnection->transaction->rollBack();
    }


    private function getResources()
    {
        $data = require __DIR__ . './../_data/BudgetDepartmentData.php';

        foreach ($data as $item) {
            $this->allResources[] = new ResourceBudgetDepartment(
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
