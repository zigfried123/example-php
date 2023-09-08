<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.04.2018
 * Time: 10:13
 */

namespace app\modules\v1\models;

use \Yii;


class EmailSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_email_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tenant_id', 'individual_settings', 'mail_server'], 'integer'],
            [['sender_name'], 'string', 'max' => 50],
            ['password', 'string'],
            [['template', 'params', 'description'], 'string'],
            [
                'template',
                'match',
                'pattern' => '/#CONTENT#/',
                'message' => Yii::t('message', 'Please add #CONTENT# in field'),
            ],
            ['sender_email', 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'setting_id' => 'Setting ID',
            'tenant_id' => 'Tenant ID',
            'individual_settings' => Yii::t('setting', 'Individual settings'),
            'mail_server' => Yii::t('setting', 'Mail server'),
            'sender_name' => Yii::t('setting', 'Sender name'),
            'sender_email' => 'Sender Email',
            'password' => 'Password',
            'template' => 'Template',
        ];
    }

}