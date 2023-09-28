<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class Auth extends Core
{
	var $Db, $userAuth, $DetailAdmin;

	public function __construct()
	{
		parent::__construct();
	}
	
	function addAuthAdmin($userAuth, $DetailAdmin)
	{
		$this->userAuth = $userAuth;
		$this->DetailAdmin = $DetailAdmin;
	}

	function checkUsername($vUsername)
	{
		$Baca=$this->Db->sql_query_row("SELECT id FROM cpadmin WHERE vUsername='".$vUsername."'");
		if ($Baca[0]=="") 
			return false;
		else
			return true;
	}

	function checkPermit($vUsername)
	{
		$Baca=$this->Db->sql_query_array("SELECT id FROM cpadmin WHERE vUsername='".$vUsername."' AND isLogin='1'");
		if ($Baca['id']!="") 
			return true;
		else 
			return false;
	}

	function getEmail($vUsername)
	{
		$Baca = $this->Db->sql_query_row("SELECT vEmail FROM cpadmin WHERE vUsername='".$vUsername."'");
		return $Baca[0];
	}

	function checkPassword($Username,$Password)
	{
		$Baca=$this->Db->sql_query_array("SELECT cPassword FROM cpadmin WHERE vUsername='".$Username."'");
		if (md5($Password)==$Baca['cPassword'])
			return true;
		else
			return false;
	}

	//liat sessionnya apakah betul nih session
	function checkSession($Username,$thepage=NULL)
	{
		$Username=trim($Username);
		$me=true;
		//jika spesifikasi pages tidak ada
		if ($thepage==NULL)
		{
			if ($Username=="")
				$me=false;
			else
			{
				if ($this->checkUsername($Username)==false)
					$me=false;
			}
		}
		else
		{
			//jika membutuhkan spesifikasi pages
			$me=false;
			if ($Username!="admin")
			{
				$Baca = $this->detailAdmin(NULL,$Username);
				$Compar = explode(",", $Baca[vRestriction]);
				for ($i=0;$i<count($Compar);$i++)
				{
					if ($Compar[$i]==$thepage)
					{
						$me=true;
						break;
					}
				}
			}
			else
				$me=true;
		}
		
		if ($me==false)
			die("<font face=verdana size=2 color=red>Ops! Your session has been expired or you are not authenticate to access this page<br />Please <b><a href=\"".$this->Config['base']['url'].$this->Config['index']['page'].$this->Config['base']['admin']."\">login</a></b> first</font>");

		return $me;
	}

	//kudu update last login nya dia
	function updateLastLogin($Username)
	{
		return $this->Db->sql_query("UPDATE cpadmin SET dLastLogin='".date("Y-n-d h:i:s")."' WHERE vUsername='".$Username."'");
	}

	//kudu dapetin last login nya si username
	function getLastLogin($Username)
	{
		$Baca=$this->Db->sql_query_array("SELECT DATE_FORMAT(dLastLogin, '%W, %d %M %Y - %h:%i:%s') AS dateLogin FROM cpadmin WHERE vUsername='".$Username."'");
		return $Baca[dateLogin];
	}

	function updatePassword($Username, $Password)
	{
		return $this->Db->sql_query("UPDATE cpadmin SET cPassword='".md5($Password)."' WHERE vUsername='".$Username."'");
	}
	
	function updateName($vName,$Username)
	{
		return $this->Db->sql_query("UPDATE cpadmin SET vName='".$vName."' WHERE vUsername='".$Username."'");
	}
	
	function updateEmail($Username,$vEmail)
	{
		return $this->Db->sql_query("UPDATE cpadmin SET vEmail='".$vEmail."' WHERE vUsername='".$Username."'");
	}

	function resetkelompok($id_kelompok)
	{
		return $this->Db->sql_query("UPDATE cpadmin SET id_kelompok='0' WHERE id_kelompok='".$id_kelompok."'");
	}
	
	function detailAdmin($id,$vUsername=NULL)
	{
		if ($vUsername==NULL)
			return $this->Db->sql_query_array("SELECT * FROM cpadmin WHERE id='".$id."'");
		else
			return $this->Db->sql_query_array("SELECT * FROM cpadmin WHERE vUsername='".$vUsername."'");
	}

	function detailAdminByEmail($vEmail)
	{
		return $this->Db->sql_query_array("SELECT * FROM cpadmin WHERE vEmail='".$vEmail."'");
	}
	
	function deleteUser($id)
	{
		$Baca = $this->detailAdmin($id);
		if ($Baca['vUsername']!="admin")
		{
			$this->Db->sql_query("DELETE FROM cpadmin WHERE id='".$id."'");
			return true;
		}
		else
			return false;
	}
	
	function adduser($Data)
	{
		return $this->Db->add($Data, "cpadmin");
	}
	
	function updateUser($Data,$Id)
	{
		return $this->Db->update($Data, $Id, "cpadmin");
	}

	function updateRole($Data, $Id)
	{
		return $this->Db->update($Data, $Id, "cpadmin");
	}
				
	function listAdmin()
	{
		$this->LoadModule('Jabatan');
		$baca = $this->Db->sql_query("SELECT * FROM cpadmin ORDER BY id ASC");
		$i=0;
		while ($Baca=$this->Db->sql_array($baca))
		{
			$jabatan = $this->Module->Jabatan->detail($Baca['id_jabatan']);
			$Data[$i] = array (		
				'No' => ($i+1),
				'Item' => $Baca,
				'jabatan' => $jabatan['name']
			);
			$i++;
		}
		return $Data;
	}

	function listAdminByKelompok($id_kelompok)
	{
		$baca = $this->Db->sql_query("SELECT * FROM cpadmin WHERE id_kelompok='".$id_kelompok."' ORDER BY id ASC");
		$i=0;
		while ($Baca=$this->Db->sql_array($baca))
		{
			$Data[$i] = array (		
				'No' => ($i+1),
				'Item' => $Baca
			);
			$i++;
		}
		return $Data;
	}

	function listAdminBySlug($slug)
	{
		$detailSlug = $this->detailslug($slug);
		$baca = $this->Db->sql_query("SELECT * FROM cpadmin WHERE id_jabatan='".$detailSlug['id']."' ORDER BY id ASC");
		$i=0;
		while ($Baca=$this->Db->sql_array($baca))
		{
			$Data[$i] = array (		
				'No' => ($i+1),
				'Item' => $Baca
			);
			$i++;
		}
		return $Data;
	}

	function detailslug($slug)
	{
		return $this->Db->sql_query_array("SELECT * FROM jabatan WHERE slug='".$slug."'");
	}

	private function dashboard()
	{
		echo "<script>location.href='".$this->Config['base']['url'].$this->Config['index']['page'].$this->Config['base']['admin']."/dashboard';</script>";
		die();
	}

	function verifyAdmin($slug, $username="", $DetailAdmin)
	{
		if (!(empty($slug)))
		{
			$progress = false;
			for ($i=0;$i<=count($slug);$i++)
			{
				if ($slug[$i] == $DetailAdmin['slug'])
				{
					$progress = true;
					break;
				}
			}

			if ($progress==false)
			{
				if (($username!="") AND ($DetailAdmin['vUsername']==$username))
					$progress = true;
				else
					$this->dashboard();
			}
		}
		else
		{
			if ($username=="")
			{
				$this->dashboard();
			}
			else
			{
				if ($DetailAdmin['vUsername']!=$username)
				{
					$this->dashboard();
				}
			}
		}
	}
		
	function listAnggotaOpd($Id)
	{
		$baca = $this->Db->sql_query("SELECT * FROM cpadmin WHERE id_opd='".$Id."' ORDER BY id ASC");
		$i=0;
		while ($Baca=$this->Db->sql_array($baca))
		{
			$Data[$i] = array (		
				'No' => ($i+1),
				'Item' => $Baca
			);
			$i++;
		}
		return $Data;
	}

}
?>