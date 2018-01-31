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
'ER_SCRIPT_NAME'=>'Название сценария',
'ER_APP_ABOUT' => 'Про модуль',
'ER_APP_CLOSE' => 'Закрыть',
'ER_APP_MODULE' => 'Модуль',
'ER_APP_PROJ' => 'Проект в',
'ER_APP_DISCUS' => 'Обсуждение модуля на форуме',
'ER_APP_DONATE' => 'Поддержать разработку и развитие модуля:',
'ER_APP_DONATE2' => 'Страничка для доната в',
'ER_APP_DONATE3' => 'Внутренний счет в',
'ER_APP_Author' => 'Автор',
'ER_APP_NAME' => 'Устройства Yeelight',
'ER_APP_THANKS' => 'Благодарности',
'ER_APP_NOTFOUND' => 'Устройства не найдены',
'ER_APP_IPNOTFOUND' => 'Неизвестный ip',
'ER_APP_USED' => 'Использование в ваших скриптах',
'ER_APP_BRIG' => 'Яркость',
'ER_APP_HELP_TEXT' => 'Для ламп поддерживающих RGB цвет',


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
