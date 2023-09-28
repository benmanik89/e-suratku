<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class Db
{
	//Database function, Created by DUAONS
	var $dbHost, $dbUser, $dbPassword, $dbName, $dbStatus, $Result, $Config;
	
	function __construct()
	{
		global $config;
		$this->Config = $config;
		$this->dbHost=$config['db']['host'];
		$this->dbUser=$config['db']['user'];
		$this->dbPassword=$config['db']['pass'];
		$this->dbName=$config['db']['name'];
		//Set Flag
		$this->flag = 0;
		$this->insertUpdateFlag = false;
		$this->deleteFlag = false;
		$this->dbStatus=mysqli_connect($this->dbHost,$this->dbUser,$this->dbPassword,$this->dbName) or die("Could not connect: ". mysqli_error($this->dbStatus));
		return $this->dbStatus;
	}

	function sql_query_row($perintah){
		$data=mysqli_query($this->dbStatus,$perintah);
		return mysqli_fetch_row($data);
	}

	function sql_query_array($perintah){
		$data=mysqli_query($this->dbStatus,$perintah);
		return mysqli_fetch_array($data);
	}

	function sql_query($perintah){
		return mysqli_query($this->dbStatus,$perintah); // or die(mysqli_error($this->dbStatus));
	}

	function sql_row($perintah){
		return mysqli_fetch_row($perintah);
	}

	function sql_array($perintah){
		return mysqli_fetch_array($perintah);
	}

	function real_escape_string($String)
	{
		return mysqli_real_escape_string($String);
	}

	function dbCountRows($Table="",$Condition=NULL)
	{
		if ($Table!="")
		{
			$Temp=$this->sql_query_row("select count(*) from ".$Table.$Condition);
			return $Temp[0];
		}
		else
			return NULL;
	}

	function dbLastID($Table="",$Colom="")
	{
		if (($Table!="") AND ($Colom!="")){
			$Temp=$this->sql_query_array("SELECT ".$Colom." FROM ".$Table." ORDER BY ".$Colom." DESC");
			return $Temp[$Colom];
		}
		else
			return NULL;
	}

	function dbBagi($tableName,$Cond,$URL)
	{
		//bagi ke dalam beberapa halaman bergantung pada berapa baris per halaman--------
			$Total=$this->sql_query_row("select count(id) from ".$tableName.$Cond);
			$Bagi=ceil($Total[0]/$this->Config['data']['halaman']);
			$Bagi=($Bagi==0)?1:$Bagi;
			for ($i=0;$i<$Bagi;$i++){
				if ($i!=0) $ur=$ur+$this->Config['data']['halaman'];
				if ($i==0) $Urutan="<a class=\"global2\" href=\"".$URL."u=".($i)."\">".($i+1)."</a>";
				else
					$Urutan .= " - <a class=\"global2\" href=\"".$URL."u=".($ur)."\">".($i+1)."</a>";
			}
			return $Urutan;
		//-------------------------------------------
	}

	function filterText($Str)
	{  
		$StrArr = STR_SPLIT($Str); $NewStr = '';
		foreach ($StrArr AS $Char) {    
			$CharNo = ORD($Char);
			if ($CharNo == 163) { $NewStr .= $Char; continue; } // keep £ 
			if ($CharNo > 31 && $CharNo < 127) {
				$NewStr .= $Char;
			}
		}  
		return $NewStr;
	}

	function escape_str($str)	
	{
		return preg_replace("'","`",$str);
	}
	
	function numeric_only($str)
	{
		return preg_replace( '/[^0-9]/', '', $str);
	}

	function add($Data, $tableName)
	{
		//$Data = array('name' => 'lolo' , 'deg' => '100');
		$columns=array_keys($Data);
		$values=array_values($Data);
		$query="INSERT INTO ".$tableName." (".implode(',',$columns).") VALUES ('" . implode("', '", $values) . "')";
		//return $query;
		return $this->sql_query($query);
	}

	function update($Data, $Id, $tableName)
	{
		if (count($Data)<=0)
			return false;
		else
		{
			$columns=array_keys($Data);
			$values=array_values($Data);
			for ($i=0;$i<count($Data);$i++)
			{
				if ($j==0)
					$data_query = " SET ".$columns[$i]."='".$values[$i]."'";
				else
					$data_query .= ", ".$columns[$i]."='".$values[$i]."'";
				
				$j++;
			}
			$query="UPDATE ".$tableName.$data_query." WHERE id='".$Id."'";
			//return $query;
			return $this->sql_query($query);
		}
	}

	function console_log($data) {
		$output = $data;
		if (is_array($output))
			$output = implode(',', $output);

		echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
	}
	
}
?>