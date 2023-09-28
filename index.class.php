<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class index extends Core
{
	public function __construct()
	{
		parent::__construct();
		include '../inc/general_api.php';
	}

	function main()
	{
		$Status = array('status' => '200 OK');
		echo $this->Template->showAPI($Status);
	}
}