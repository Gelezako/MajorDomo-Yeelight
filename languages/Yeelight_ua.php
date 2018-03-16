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
'YE_SCRIPT_NAME'=>'Назва сценарію',
'YE_APP_ABOUT' => 'Про модуль',
'YE_APP_CLOSE' => 'Закрити',
'YE_APP_MODULE' => 'Модуль',
'YE_APP_PROJ' => 'Проект у',
'YE_APP_DISCUS' => 'Обговорення модуля на форумі',
'YE_APP_DONATE' => 'Підтримати розробку і розвиток модуля:',
'YE_APP_DONATE2' => 'Сторінка для доната у:',
'YE_APP_DONATE3' => 'Внутрішній рахунок у',
'YE_APP_Author' => 'Автор',
'YE_APP_NAME' => 'Yeelight',
'YE_APP_TITLE' => 'Пристрої Yeelight',
'YE_APP_THANKS' => 'Подяки',
'YE_APP_NOTFOUND' => 'Пристрої не знайдені',
'YE_APP_IPNOTFOUND' => 'Невідомий ip',
'YE_APP_USED' => 'Використання у ваших скриптах',
'YE_APP_BRIG' => 'Яскравість',
'YE_APP_HELP_TEXT' => 'Для ламп які підтримують RGB колір',


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
