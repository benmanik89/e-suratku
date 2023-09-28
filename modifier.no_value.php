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
 * Name:     no value<br>
 * Purpose:  Empty String
 * @link http://www.duaon.com
 * @author   duaon <mailbox at duaon dot com>
 * @param string
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_no_value($string)
{
    return (($string=="") OR ($string=="0000-00-00") OR ($string=="0000-00-00 00:00:00"))?"----":$string;
}

/* vim: set expandtab: */

?>
