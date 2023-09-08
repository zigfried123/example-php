<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.04.2018
 * Time: 9:49
 */

namespace app\modules\v1\models;

use \Yii;

class DefaultEmailSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_default_email_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity', 'mail_server'], 'integer'],
            [['template', 'params', 'description'], 'string'],
            [['sender_name', 'sender_email'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'setting_id' => Yii::t('setting', 'Setting ID'),
            'activity' => Yii::t('setting', 'Activity'),
            'mail_server' => Yii::t('setting', 'Mail server'),
            'sender_name' => Yii::t('setting', 'Sender name'),
            'sender_email' => Yii::t('setting', 'Sender email'),
            'password' => Yii::t('setting', 'Password'),
            'template' => Yii::t('setting', 'Template'),
            'params' => Yii::t('setting', 'Params'),
            'description' => Yii::t('setting', 'Description'),
        ];
    }

}