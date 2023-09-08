<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 20.04.2018
 * Time: 17:09
 */

namespace app\modules\v1\models\factory\subclasses;

use app\modules\v1\models\factory\subclasses\traits\TemplatesTrait;

class ClientOrderReport
{
    use TemplatesTrait;

    protected function getContent()
    {
        return <<<EOT
<body marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" style="background-color:#f9f7f7;  font-family:Arial,serif; margin:0; padding:0 0; min-width: 100%; -webkit-text-size-adjust:none; -ms-text-size-adjust:none;">
<!--[if !mso]><!-- -->
<img style="min-width:640px; display:block; margin:0; padding:0" class="mobileOff" width="640" height="1" src="http://foto.gootax.ru/img/spacer.gif">
<!--<![endif]-->

<table class="container" width="100%" cellpadding="0" cellspacing="0" border="0" align="center" bgcolor="#f9f7f7" st-sortable="body1" style="background-color:#f9f7f7;">
    <tbody><tr>
        <td height="20" style="font-size:10px; line-height:10px;"></td>
        <!-- Spacer -->
    </tr>
    <tr>
        <td width="100%" valign="top" align="center">
            <table width="640" class="container" cellpadding="0" cellspacing="0" border="0" align="center" st-sortable="body">
                <tbody><tr>
                    <td width="100%" valign="top" align="center">
                        <!-- START HEADER -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" st-sortable="header">
                            <tbody><tr>
                                <td width="100%" valign="top" align="center">
                                    <!-- Start Wrapper  -->
                                    <table width="640" cellpadding="0" cellspacing="0" border="0" class="wrapper" bgcolor="#FFFFFF" style="border-width: 0 0 1px 0; border-color: #f6f5f3; border-style: solid;">
                                        <tbody><tr>
                                            <td align="center">
                                                <!-- Start Container  -->
                                                <table width="640" cellpadding="0" cellspacing="0" border="0" class="container">
                                                    <tbody><tr>
                                                        <td height="20" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>
                                                    <tr>
                                                        <td width="320" class="mobile" style="padding-left:25px;">
                                                            <img src="http://foto.gootax.ru/img/logo.png" width="200" height="50" style="margin:0; padding:0; border:none; display:block;" border="0" class="centerClass" alt="" st-image="image">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="20" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>
                                                </tbody></table>
                                                <!-- Start Container  -->
                                            </td>
                                        </tr>
                                    </tbody></table>
                                    <!-- End Wrapper  -->
                                </td>
                            </tr>
                        </tbody></table>
                        <!-- END HEADER -->

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" align="left" st-sortable="full-text">
                            <tbody><tr>
                                <td width="100%" valign="top" align="left">
                                    <!-- Start Wrapper  -->
                                    <table width="640" cellpadding="0" cellspacing="0" border="0" class="wrapper" bgcolor="#ffffff">
                                        <tbody><tr>
                                            <td colspan="2" align="left" style="padding-left: 25px;">
                                                <h1 style="font-size: 28px; font-weight: 400; margin-bottom: 0;">Отчёт о поездке</h1>
                                                <p style="color: #898989; margin-top: 8px;">Заказ №123456 от 6 марта, 2018 в 14:40</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="20" style="font-size:10px; line-height:10px;"></td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="padding-left: 35px; width: 25px;">
                                                <img src="http://foto.gootax.ru/img/dot.png" width="14" height="14" style="margin:0; padding:0; border:none;" border="0" class="centerClass" alt="" st-image="image">
                                            </td>
                                            <td align="left" style="padding-right: 35px; width: 400px;">
                                                ул. Школьная, 11
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" align="left" style="padding-left: 40px;">
                                                <font color="909090">|</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="padding-left: 35px; width: 25px;">
                                                <img src="http://foto.gootax.ru/img/dot.png" width="14" height="14" style="margin:0; padding:0; border:none;" border="0" class="centerClass" alt="" st-image="image">
                                            </td>
                                            <td align="left" style="padding-right: 35px; width: 400px;">
                                                ул. Школьная, 11
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" align="left" style="padding-left: 40px;">
                                                <font color="909090">|</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="padding-left: 35px; width: 25px;">
                                                <img src="http://foto.gootax.ru/img/dot.png" width="14" height="14" style="margin:0; padding:0; border:none;" border="0" class="centerClass" alt="" st-image="image">
                                            </td>
                                            <td align="left" style="padding-right: 35px; width: 400px;">
                                                ул. Школьная, 11
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" align="left" style="padding-left: 40px;">
                                                <font color="909090">|</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="padding-left: 35px; width: 25px;">
                                                <img src="http://foto.gootax.ru/img/dot.png" width="14" height="14" style="margin:0; padding:0; border:none;" border="0" class="centerClass" alt="" st-image="image">
                                            </td>
                                            <td align="left" style="padding-right: 35px; width: 400px;">
                                                ул. Школьная, 11
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="30" style="font-size:10px; line-height:10px;"></td>
                                            <!-- Spacer -->
                                        </tr>
                                    </tbody></table>

                                    <table width="640" cellpadding="0" cellspacing="0" border="0" class="wrapper" bgcolor="#ffffff">
                                        <tbody><tr>
                                            <td align="left" style="padding-left: 25px; padding-right: 25px;">
                                                <p style="color: #898989;">Тариф</p>
                                                Эконом
                                            </td>
                                            <td align="left" style="padding-left: 25px; padding-right: 25px;">
                                                <p style="color: #898989;">Время поездки</p>
                                                15 мин., 50 с
                                            </td>
                                            <td align="left" style="padding-left: 25px; padding-right: 25px;">
                                                <p style="color: #898989;">Стоимость</p>
                                                103 RUB
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="30" style="font-size:10px; line-height:10px;"></td>
                                            <!-- Spacer -->
                                        </tr>
                                        <tr>
                                            <td align="left" colspan="3" style="padding-left: 12px; padding-right: 12px;">
                                                <table width="616" cellpadding="0" cellspacing="0" border="0" class="wrapper" bgcolor="#efefef">
                                                    <tbody><tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <b>Детализация поездки</b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>

                                                    <tr>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            Начальная стоимость
                                                        </td>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <font color="6e6e6e"></font>
                                                        </td>
                                                        <td align="right" style="padding-left: 15px; padding-right: 15px;">
                                                            60.0 Р
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>

                                                    <tr>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            Наценка за удаленность
                                                        </td>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <font color="6e6e6e">0.0 км</font>
                                                        </td>
                                                        <td align="right" style="padding-left: 15px; padding-right: 15px;">
                                                            60.0 Р
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>

                                                    <tr>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            Бесплатно
                                                        </td>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <font color="6e6e6e">0.0 км</font>
                                                        </td>
                                                        <td align="right" style="padding-left: 15px; padding-right: 15px;">
                                                            60.0 Р
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>

                                                    <tr>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            Бесплатно
                                                        </td>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <font color="6e6e6e">0.0 км</font>
                                                        </td>
                                                        <td align="right" style="padding-left: 15px; padding-right: 15px;">
                                                            60.0 Р
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>

                                                    <tr>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            Загород 1 м/70 Р
                                                        </td>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <font color="6e6e6e">23 м, 50 с</font>
                                                        </td>
                                                        <td align="right" style="padding-left: 15px; padding-right: 15px;">
                                                            60.0 Р
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>

                                                    <tr>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            Мин. стоимость
                                                        </td>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <font color="6e6e6e"></font>
                                                        </td>
                                                        <td align="right" style="padding-left: 15px; padding-right: 15px;">
                                                            60.0 Р
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>

                                                    <tr>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            Итого без скидки
                                                        </td>
                                                        <td align="left" style="padding-left: 15px; padding-right: 15px;">
                                                            <font color="6e6e6e"></font>
                                                        </td>
                                                        <td align="right" style="padding-left: 15px; padding-right: 15px;">
                                                            60.0 Р
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10" style="font-size:10px; line-height:10px;"></td>
                                                        <!-- Spacer -->
                                                    </tr>


                                                </tbody></table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="20" style="font-size:10px; line-height:10px;"></td>
                                            <!-- Spacer -->
                                        </tr>
                                        <tr>
                                            <td align="left" colspan="3">
                                                <table width="640" cellpadding="0" cellspacing="0" border="0" class="wrapper" bgcolor="#ffffff">
                                                    <tbody><tr>
                                                        <td align="left" style="padding-left: 25px; padding-right: 25px;" width="33.33%" valign="top">
                                                            <p style="color: #898989;">Компания</p>
                                                            <p style="line-height: 1.7; margin-top: 0;">
                                                                “Такси Восток”<br>
                                                                +73412310932<br>
                                                                <a href="mailto:support@gootax.pro">support@gootax.pro</a><br>
                                                                <a href="https://www.gootax.pro/">gootax.pro</a>
                                                            </p>
                                                        </td>
                                                        <td align="left" style="padding-left: 25px; padding-right: 25px;" width="33.33%" valign="top">
                                                            <p style="color: #898989;">Водитель</p>
                                                            <p style="line-height: 1.7; margin-top: 0;">
                                                                Фефилов Сергей Владимирович<br>
                                                                +79199040170
                                                            </p>
                                                        </td>
                                                        <td align="left" style="padding-left: 25px; padding-right: 25px;" width="33.33%" valign="top">
                                                            <p style="color: #898989;">Автомобиль</p>
                                                            <p style="line-height: 1.7; margin-top: 0;">
                                                                VolksWagen Tiguan,<br>Н535ОР, синий
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </tbody></table>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>
            </tbody></table>
        </td>
    </tr>
    <tr>
        <td height="20" style="font-size:10px; line-height:10px;"></td>
        <!-- Spacer -->
    </tr>
</tbody></table>
<!-- END FULL-TEXT -->



</body>
EOT;

    }

}