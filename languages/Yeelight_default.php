<?php
/**
 * Russian language file for ExchangeRates module
 *
 * @package ExchangeRates
 * @author Alex Sokolov <admin@gelezako.com> http://blog.gelezako.com
 * @version 1.0
 *
 **/

$dictionary = array(
/* general */
'YE_SCRIPT_NAME'=>'Название сценария',
'YE_APP_ABOUT' => 'Про модуль',
'YE_APP_CLOSE' => 'Закрыть',
'YE_APP_MODULE' => 'Модуль',
'YE_APP_PROJ' => 'Проект в',
'YE_APP_DISCUS' => 'Обсуждение модуля на форуме',
'YE_APP_DONATE' => 'Поддержать разработку и развитие модуля:',
'YE_APP_DONATE2' => 'Страничка для доната в',
'YE_APP_DONATE3' => 'Внутренний счет в',
'YE_APP_Author' => 'Автор',
'YE_APP_NAME' => 'Yeelight',
'YE_APP_TITLE' => 'Устройства Yeelight',
'YE_APP_THANKS' => 'Благодарности',
'YE_APP_NOTFOUND' => 'Устройства не найдены',
'YE_APP_IPNOTFOUND' => 'Неизвестный ip',
'YE_APP_USED' => 'Использование в ваших скриптах',
'YE_APP_BRIG' => 'Яркость',
'YE_APP_HELP_TEXT' => 'Для ламп поддерживающих RGB цвет',


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
