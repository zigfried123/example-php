<?php


namespace app\modules\reports\models\orders;


use app\modules\tenant\models\User;

class StatisticSearch extends BaseOrderSearch
{
    /**
     * @return array|bool
     */
    public function search()
    {
        if (!$this->validate()) {
            return false;
        }

        if (app()->user->can(User::USER_ROLE_4)) {
            $this->tenantCompanyIds = [user()->tenant_company_id];
        }

        return OrderReport::getOrderStat($this->getCityId(), [$this->first_date, $this->second_date],
            $this->position_id, $this->tenantCompanyIds);
    }
}