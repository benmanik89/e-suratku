<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty Short modifier plugin
 *
 * Type:     modifier<br>
 * Name:     no value<br>
 * Purpose:  Short String
 * @param string
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_short($string)
{
	$Space = explode(" ",$string);
	$Temp = "";
	for ($i=0;$i<count($Space);$i++)
	{
		$Temp .= $Space[$i];
		if ($i % 2)
			$Temp .= "<br />";
		else
			$Temp .= "&nbsp;";
	}
    return $Temp;
}

/* vim: set expandtab: */

?>
