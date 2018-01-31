<?php
/**
 * Ukraine language file for Yeelight module
 *
 * @package Yeelight
 * @author Alex Sokolov <admin@gelezako.com> http://blog.gelezako.com
 * @version 0.1
 *
 **/

$dictionary = array(
/* general */
'ER_SCRIPT_NAME'=>'Назва сценарію',
'ER_APP_ABOUT' => 'Про модуль',
'ER_APP_CLOSE' => 'Закрити',
'ER_APP_MODULE' => 'Модуль',
'ER_APP_PROJ' => 'Проект у',
'ER_APP_DISCUS' => 'Обговорення модуля на форумі',
'ER_APP_DONATE' => 'Підтримати розробку і розвиток модуля:',
'ER_APP_DONATE2' => 'Сторінка для доната у:',
'ER_APP_DONATE3' => 'Внутрішній рахунок у',
'ER_APP_Author' => 'Автор',
'ER_APP_NAME' => 'Пристрої Yeelight',
'ER_APP_TITLE' => 'Виберіть тип валюти,<br>який хочете отримати',
'ER_APP_THANKS' => 'Подяки',
'ER_APP_NOTFOUND' => 'Пристрої не знайдені',
'ER_APP_IPNOTFOUND' => 'Невідомий ip',
'ER_APP_USED' => 'Використання у ваших скриптах',
'ER_APP_BRIG' => 'Яскравість',
'ER_APP_HELP_TEXT' => 'Для ламп які підтримують RGB колір',


/* end module names */
);

foreach ($dictionary as $k=>$v)
{
   if (!defined('LANG_' . $k))
   {
      define('LANG_' . $k, $v);
   }
}

?>
