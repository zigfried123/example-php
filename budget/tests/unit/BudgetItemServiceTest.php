<?php

use app\modules\budget\models\BudgetItem;
use app\modules\budget\resources\ResourceBudgetItem;
use app\modules\budget\services\BudgetItemService;
use Codeception\Test\Unit;
use yii\db\Connection;
use yii\base\Event;
use app\modules\budget\events\UpdateBudgetItemEvent;
use app\modules\budget\events\DeleteBudgetItemEvent;
use app\modules\budget\events\NewBudgetItemEvent;

class BudgetItemServiceTest extends Unit
{
    /** @var array $allResources */
    private $allResources = [];

    /**
     * @var BudgetItemService $service
     */
    protected $service;

    /** @var Connection */
    private $dbConnection;

    /**
     * @var ResourceBudgetItem
     */
    private $firstResource;

    /**
     * @var ResourceBudgetItem
     */
    private $changedResource;

    protected function _inject(BudgetItemService $itemService)
    {
        $this->service = $itemService;
    }

    protected function setUp()
    {
        $this->dbConnection = Yii::$app->dbPlanfixSync;

        $this->dbConnection->beginTransaction();

        $this->allResources = $this->getResources();

        $resource = current($this->allResources);

        $this->firstResource = new ResourceBudgetItem($resource->getYear(), $resource->getMonth(), $resource->getCostItemCode(), $resource->getDepartmentCode(), $resource->getValue());

        $newValue = 3;
        $resource = $this->firstResource;
        $this->changedResource = (new ResourceBudgetItem($resource->getYear(), $resource->getMonth(), $resource->getCostItemCode(), $resource->getDepartmentCode(), $newValue));

        return parent::setUp();
    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testExtractNewWithMatchingItems()
    {
        $this->addAllResourcesToDB();

        $this->assertEmpty($this->service->extractNew([$this->firstResource]));
    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testExtractNewWithNotMatchingItems()
    {
        $this->addAllResourcesToDB();

        $newResource = new ResourceBudgetItem(5, 7, 'hh', 'yy', 4);

        $addedItems = $this->service->extractNew([$newResource]);

        $this->assertNotEmpty($addedItems);

        $this->assertEquals($newResource, current($addedItems));
    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testCountExtractNewWithNotMatchingItems()
    {
        $this->addAllResourcesToDB();

        $newResource1 = new ResourceBudgetItem(5, 7, 'hh', 'yy', 4);
        $newResource2 = new ResourceBudgetItem(2, 4, 'ff', 'gg', 8);

        $this->assertCount(2, $this->service->extractNew([$newResource1, $newResource2]));
    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testExtractDeleteWithAllMatchingItems()
    {
        $this->addAllResourcesToDB();

        $this->assertEmpty($this->service->extractDelete($this->allResources));
    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testExtractDeleteWithAllNotMatchingItems()
    {
        $this->addAllResourcesToDB();

        $newResources = [];

        /**
         * @var ResourceBudgetItem $resource
         */

        for ($i = 0; $i <= count($this->allResources); $i++) {
            $newResource = new ResourceBudgetItem(1, 2, 'd', 'f', 5);
            $newResources[] = $newResource;
        }

        $this->assertCount(count($this->allResources), $this->service->extractDelete($newResources));
    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testExtractDeleteWithSomeMachingValue()
    {
        $this->addAllResourcesToDB();

        $resource = array_shift($this->allResources);

        $remainedModels = $this->service->extractDelete([$resource]);

        $isEquals = false;

        foreach ($remainedModels as $model) {
            $isEquals = $this->service->isEquals($resource, $model);
        }

        $this->assertFalse($isEquals, 'items has not deleted');

        $allResourcesWithoutDeleted = $this->allResources;

        $isEquals = true;

        $models = [];

        foreach ($remainedModels as $model) {
            $models[] = $model;
            foreach ($allResourcesWithoutDeleted as $resource) {
                if ($this->service->isEquals($resource, $model)) {
                    $isEquals = true;
                    break;
                }

                $isEquals = false;
            }
        }

        $this->assertTrue($isEquals, 'items not matches');

    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testCountExtractDeleteWithMatchingItems()
    {
        $this->addAllResourcesToDB();

        $resourceCount = 1;

        $modelCount = count($this->allResources);

        $totalCount = $modelCount - $resourceCount;

        $this->assertCount($totalCount, $this->service->extractDelete([$this->firstResource]));
    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testExtractUpdateWithNotMatchingValue()
    {
        $this->addAllResourcesToDB();

        $result = $this->service->extractUpdate([$this->changedResource]);

        $this->assertCount(1, $result);

        $this->assertEquals($this->changedResource, current($result)['itemFromResource']);

    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testExtractUpdateWithMatchingValue()
    {
        $this->addAllResourcesToDB();

        $resource = $this->firstResource;

        $result = $this->service->extractUpdate([$resource]);

        $this->assertEmpty($result);

    }

    /**
     * @throws \app\modules\budget\services\exceptions\NoUniqueDbIndexException
     */
    public function testExtractUpdateWithNotMatchingUniqueFields()
    {
        $changedResource = (new ResourceBudgetItem(5, 8, 'fs', 'lj', 9));

        $result = $this->service->extractUpdate([$changedResource]);

        $this->assertEmpty($result);
    }

    /**
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testAdd()
    {
        Event::on(BudgetItem::class, BudgetItem::EVENT_NEW_BUDGET_ITEM, function (NewBudgetItemEvent $event) {
            $this->assertNotEmpty($event->getBudgetItem());
        });

        $result = $this->service->add($this->firstResource);

        $this->assertTrue($result);

        /**
         * @var BudgetItem $result
         */
        $result = BudgetItem::find()->orderBy('id DESC')->one();

        $this->assertEquals($this->firstResource->getDepartmentCode(), $result->departmentCode);
        $this->assertEquals($this->firstResource->getCostItemCode(), $result->costItemCode);
        $this->assertEquals($this->firstResource->getMonth(), $result->month);
        $this->assertEquals($this->firstResource->getYear(), $result->year);
        $this->assertEquals($this->firstResource->getValue(), $result->value);
    }

    /**
     * @throws Throwable
     * @throws \app\modules\budget\services\exceptions\ServiceException
     * @throws \yii\db\StaleObjectException
     */
    public function testDelete()
    {
        $this->addAllResourcesToDB();

        /**
         * @var BudgetItem $model
         */
        $model = BudgetItem::find()->one();

        Event::on(BudgetItem::class, BudgetItem::EVENT_DELETE_BUDGET_ITEM, function (DeleteBudgetItemEvent $event) {
            $this->assertNotEmpty($event->getBudgetItem());
        });

        $result = $this->service->delete($model);
        $this->assertTrue($result);

        $deletedModel = BudgetItem::findOne($model->id);

        $this->assertNull($deletedModel);
    }

    /**
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    public function testUpdate()
    {
        $this->addAllResourcesToDB();

        /**
         * @var BudgetItem $model
         */
        $model = BudgetItem::find()->one();

        $model->value = 7;

        Event::on(BudgetItem::class, BudgetItem::EVENT_UPDATE_BUDGET_ITEM, function (UpdateBudgetItemEvent $event) {
            $this->assertNotEmpty($event->getBudgetItem());
            $this->assertNotEmpty($event->getBudgetItemOld());
        });

        $res = $this->service->update($model);

        $this->assertTrue($res);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->dbConnection->transaction->rollBack();
    }


    /**
     * @return ResourceBudgetItem[]
     */
    private function getResources(): array
    {
        $data = require __DIR__ . './../_data/BudgetItemData.php';

        foreach ($data as $item) {
            $this->allResources[] = new ResourceBudgetItem(
                $item['year'],
                $item['month'],
                $item['costItemCode'],
                $item['departmentCode'],
                $item['value']
            );
        }

        return $this->allResources;
    }

    /**
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    private function addAllResourcesToDB()
    {
        foreach ($this->allResources as $item) {
            $this->service->add($item);
        }
    }
}
