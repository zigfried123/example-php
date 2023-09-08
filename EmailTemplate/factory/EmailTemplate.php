<?php

namespace app\modules\v1\models\factory;

use app\modules\v1\models\factory\subclasses\ChatMessage;
use app\modules\v1\models\factory\subclasses\ClientOrderReport;
use app\modules\v1\models\factory\subclasses\ConfirmUserEmail;
use app\modules\v1\models\factory\subclasses\PasswordResetToken;
use app\modules\v1\models\factory\subclasses\PaymentNotice;
use app\modules\v1\models\factory\subclasses\Register;
use yii\db\Exception;

class EmailTemplate
{
    private $queryParams;
    private $body;
    private $type;
    private $from;
    private $password;
    private $to;
    private $subject;

    public function __construct($queryParams)
    {
        $this->queryParams = $queryParams;
        foreach ($queryParams as $key => $val) {
            $this->$key = $val;
        }
    }

    public function getParams()
    {
        return [
            'FROM'     => $this->from,
            'PASSWORD' => $this->password,
            'TO'       => $this->to,
            'SUBJECT'  => $this->subject,
            'BODY'     => $this->body,
        ];
    }

    public function setBody()
    {
        $obj = null;

        switch ($this->type) {
            case 'confirmUserEmail':
                $obj = new ConfirmUserEmail();
                break;
            case 'passwordResetToken':
                $obj = new PasswordResetToken();
                break;
            case 'chatMessage':
                $obj = new ChatMessage();
                break;
            case 'paymentNotice':
                $obj = new PaymentNotice();
                break;
            case 'register':
                $obj = new Register();
                break;
            case 'clientOrderReport':
                $obj = new ClientOrderReport();
                break;
            default:
                throw new Exception('Unknown type');
        }

        $this->body = $obj->getBody($this->queryParams);
    }

}