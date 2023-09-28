<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class Api extends Core
{
	public function __construct()
	{
		parent::__construct();
	}

	//API Module
	function listAPI()
	{
		$baca = $this->Db->sql_query("SELECT * FROM cpapi");
		$i=0;
		while ($Baca = $this->Db->sql_array($baca))
		{
			$Data [$i] = array(	'No' => ($i+1), 'Item' => $Baca);
			$i++;
		}
		return $Data;
	}
	
	function detailAPI($Id)
	{
		return $this->Db->sql_query_array("SELECT * FROM cpapi WHERE id='".$Id."'");
	}
	
	function detailByAPI($vAPI)
	{
		return $this->Db->sql_query_array("SELECT * FROM cpapi WHERE vAPI='".$vAPI."'");
	}
	
	function updateAPI($Data, $Id)
	{
		return $this->Db->sql_query("UPDATE cpapi SET vAPI='".$Data[0]."', dCreated='".$Data[1]."' WHERE id='".$Id."'");
	}

	function addAPI($Data)
	{
		return $this->Db->sql_query("INSERT INTO cpapi VALUES (NULL,'".$Data[0]."','".$Data[1]."','".date("Y-m-d H:i:s")."')");
	}

	function deleteAPI($Id)
	{
		return $this->Db->sql_query("DELETE FROM cpapi WHERE id='".$Id."'");
	}
	
	function newAPI()
	{
		return md5(rand(0,9).rand(0,9).date("YmdHis").rand(0,9).rand(0,9));
	}
	
}
?>