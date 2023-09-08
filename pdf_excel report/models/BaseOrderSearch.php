<?php


namespace app\modules\reports\models\orders;


use yii\base\InvalidConfigException;
use yii\base\Model;

abstract class BaseOrderSearch extends Model
{
    public $city_id;
    public $position_id;
    public $type = 'today';
    public $first_date;
    public $second_date;
    public $access_city_list;
    public $access_position_list;
    public $tenantCompanyIds;

    protected $isSetTime = false;

    public function init()
    {
        if (empty($this->access_position_list)) {
            throw new InvalidConfigException('The access position list must not be set');
        }

        if (empty($this->city_id)) {
            $this->city_id = current($this->access_city_list);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'in', 'range' => ['today', 'yesterday', 'month', 'period']],
            [['first_date', 'second_date'], 'safe'],
            [['city_id'], 'in', 'range' => $this->access_city_list],
            [['position_id'], 'in', 'range' => $this->getPositionList(), 'allowArray' => true],
            [['tenantCompanyIds'], 'each', 'rule' => ['integer']],
        ];
    }

    public function afterValidate()
    {
        parent::afterValidate();
        $this->setPeriod();
    }

    abstract protected function search();

    protected function getCityId()
    {
        return $this->city_id ? $this->city_id : current($this->access_city_list);
    }

    /**
     * @return array
     */
    protected function getPositionList()
    {
        return $this->position_id ? $this->position_id : $this->access_position_list;
    }

    private function setPeriod()
    {
        if (!$this->isSetTime) {
            switch ($this->type) {
                case 'period':
                    break;
                case 'yesterday':
                    $this->first_date = $this->second_date = date("d.m.Y", (time() - 24 * 60 * 60));
                    break;
                case 'month':
                    $this->first_date = mktime(0, 0, 0, date("n"), 1);
                    $this->second_date = time();
                    break;
                case 'today':
                default:
                    $this->first_date = $this->second_date = date("d.m.Y", time());
                    break;
            }

            $this->first_date = (int)app()->formatter->asTimestamp($this->first_date);
            $this->second_date = (int)app()->formatter->asTimestamp($this->second_date) + 24 * 60 * 60;

            $this->isSetTime = true;
        }
    }
}