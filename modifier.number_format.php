<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty Number Format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     number format<br>
 * Purpose:  number format english
 * @link http://www.duaon.com
 * @author   duaon <mailbox at duaon dot com>
 * @param string
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_number_format($string)
{
    return number_format($string);
}

/* vim: set expandtab: */

?>
