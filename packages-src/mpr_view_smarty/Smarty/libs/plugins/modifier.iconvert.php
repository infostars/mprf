<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Файл:         modifier.convert.php
 * Тип:          modifier
 * Имя:          convert
 * Назначение:   Конвертация из одной кодировки в другую функцией iconv или mb_convert_encoding.
 * Использование в шаблоне:   {$templ_var|convert:'UTF-8':'CP1251':false}
 * -------------------------------------------------------------
 */
function smarty_modifier_convert( $string, $from = 'CP1251', $to = 'UTF-8', $mb = false )
{
    if (!$mb){
        $conv_string = iconv($from, $to, $string);
    } else {
        $conv_string = mb_convert_encoding($string, $to, $from);
    }
    return $conv_string;
}