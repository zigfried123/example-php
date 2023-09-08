<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.04.2018
 * Time: 9:01
 */

namespace app\modules\v1\models;

use app\modules\v1\models\decorator\TagCompanyName;
use app\modules\v1\models\decorator\TagContent;
use app\modules\v1\models\decorator\TagLastName;
use app\modules\v1\models\decorator\TagMiddleName;
use app\modules\v1\models\decorator\TagName;
use \Yii;

class BodyGenerator
{
    private $emailSettings;
    private $data;
    private $content;
    private $setting;
    private $lang;

    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }

        $this->setEmailSettings();
    }

    private function setEmailSettings()
    {
        $this->emailSettings = (new EmailSettings())->find()->where(['tenant_id' => $this->data['tenantId']])->one();

        if(!$this->checkEmailSettingsToUse()){
            $this->emailSettings = (new DefaultEmailSettings())->find()->one();
            $this->translateDefaultTemplate();
        }
    }

    private function translateDefaultTemplate()
    {
        $this->emailSettings->template = Yii::t('email',trim($this->emailSettings->template),[],$this->lang);
    }

    private function checkEmailSettingsToUse()
    {
        return !($this->isEmptyEmailSettings() || $this->isFalseIndividualSettings() || $this->setting === 'default');
    }

    private function isFalseIndividualSettings()
    {
        return !$this->emailSettings->individual_settings;
    }

    private function isEmptyEmailSettings()
    {
        return !isset($this->emailSettings);
    }

    public function execute()
    {
        return $this->formEndBody();
    }

    /**
     * replace all tags and set final body
     */
    private function formEndBody()
    {
        $this->data['content'] = $this->content;

        $decorator = new TagContent(new TagLastName(new TagName(new TagMiddleName(new TagCompanyName()))));
        return $decorator->replace($this->emailSettings->template,$this->data);
    }
}