<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 16.04.2018
 * Time: 14:04
 */

namespace app\modules\v1\controllers;

use app\modules\v1\components\gearman\Gearman;
use app\modules\v1\models\factory\EmailTemplate;
use yii\web\Controller;
use \Yii;

class EmailController extends Controller
{

    public function actionSend()
    {
        $queryParams = array_merge(Yii::$app->request->get(),Yii::$app->request->post());

        $emailTemplate = new EmailTemplate($queryParams);

        $emailTemplate->setBody();

        $params = $emailTemplate->getParams();

        try {
            Yii::$app->gearman->doBackground(Gearman::EMAIL_TASK, $params);
        } catch (\yii\base\ErrorException $exc) {
            $mailer = Yii::$app->mailer;

            $message = $mailer->compose('toTenantUsers', ['body' => $params['BODY']])
                ->setFrom(getenv('MAIL_SUPPORT_USERNAME'))
                ->setTo($params['TO'])
                ->setSubject($params['SUBJECT']);

            if (isset($params['DATA']['FILES'])) {
                foreach ($params['DATA']['FILES'] as $file) {
                    $message->attach($file);
                }
            }

            return $message->send();
        }

        return false;

    }

}