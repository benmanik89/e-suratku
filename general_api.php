<?php
if (!($this->API_Access()))
{
	echo $this->Template->showAPI(array('status' => 'You can not access this system'));
	die();
}
?>