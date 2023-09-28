<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class myaccount extends Core
{
	var $Submit, $Action, $Id, $DetailAdmin, $Username, $Do, $Show, $getTahun, $dirUser;

	public function __construct()
	{
		parent::__construct();

		//Load General Process
		include '../inc/general_admin.php';
		$this->LoadModule("Jabatan");
		$this->LoadModule("Paging");
		$this->LoadModule("Kelompok");

		$this->dirUser = $this->Config['user']['dir'];
		$this->Pile->fileDestination = $this->dirUser;
		$this->Template->assign("dirUser", $this->dirUser);


		$this->Module->Paging->setPaging(20,10,"&laquo; Prev","Next &raquo;");

		$this->Template->assign("Signature", "user");
		ob_clean();
	}
	
	//Search Website
	function main()
	{	
		echo $this->Template->ShowAdmin("account/account_index.html");
	}

	function changepassword()
	{
		if ($this->Submit)
		{
			switch($this->Action)
			{
				case "changepass":
					$oPassword=trim($_POST['oPassword']);
					$nPassword=trim($_POST['nPassword']);
					$rPassword=trim($_POST['rPassword']);
					
					if (($oPassword!="") AND ($nPassword!="") AND ($rPassword!=""))
					{
						if ($nPassword==$rPassword)
						{
							if ($this->Module->Auth->checkPassword($this->Username,$oPassword))
							{
								if ($this->Module->Auth->updatePassword($this->Username, $nPassword))
								{
									$Return = array('status' => 'success',
										'message' => $this->Template->showMessage('success', 'Password anda telah diperbaharui'), 
										'data' => ''
									);
								}
							}
							else
							{
								$Return = array('status' => 'error',
								'message' => $this->Template->showMessage('error', 'Ops! Password lama anda tidak benar'), 
								'data' => ''
								);				
							}
						}
						else
						{
							$Return = array('status' => 'error',
							'message' => $this->Template->showMessage('error', 'Ops! Password tidak diulang dengan baik'), 
							'data' => ''
							);	
						}
					}
					else
					{
						$Return = array('status' => 'error',
						'message' => $this->Template->showMessage('error', 'Ops! Data form isian password tidak boleh kosong'), 
						'data' => ''
						);	
					}
				break;
			}
		}

		echo json_encode($Return);
	}

	function admin()
	{
		if ($this->DetailAdmin['jabatan_slug'] == 'superadmin'){
			$this->Template->assign("listJabatan", $this->Module->Jabatan->listAll());
			echo $this->Template->ShowAdmin("account/useradmin_index.html");
		} else {
			echo $this->Template->ShowAdmin("404.html");
		}
	}

	function loadadmin()
	{
		$id_jabatan = $_GET['jabatan'];
		$_JABATAN = (($id_jabatan>0) AND ($id_jabatan!=""))?" AND id_jabatan='".$id_jabatan."'":"";

		$draw = $_POST['draw'];
		$row = $_POST['start'];
		$rowperpage = $_POST['length'];
		
		$columnIndex = $_POST['order'][0]['column'];
		$columnName = $_POST['columns'][$columnIndex]['data'];
		
		$columnSortOrder = $_POST['order'][0]['dir'];
		$searchValue = $_POST['search']['value'];
		
		//Search
		$searchQuery = "";
		if ($searchValue != '')
		{
			$searchQuery = " AND (
				(vUsername like '%".$searchValue."%') OR 
				(vName like '%".$searchValue."%') OR 
				(vEmail like '%".$searchValue."%') OR 
				(vNip like '%".$searchValue."%')
			)";
		}
		
		//Total Records without Filtering
		$records = $this->Db->sql_query_array("select count(*) as total from cpadmin WHERE id!='0'".$_JABATAN.$searchQuery);
		$totalRecords = $records['total'];
		
		//Total Record with filtering
		$records = $this->Db->sql_query_array("select count(*) as total from cpadmin where id!='0'".$searchQuery.$_JABATAN);
		$totalRecordsWithFilter = $records['total'];
		
		//Fetch Records
		$orderBy = ($columnName=="")?" order by id desc":" order by ".$columnName." ".$columnSortOrder;
		$limitBy = ($row=="")?"":" limit ".$row.",".$rowperpage;
		
		$sqlQuery = "select * from cpadmin where id!='0'".$searchQuery.$_JABATAN.$orderBy.$limitBy;
			
		$sqlRecord = $this->Db->sql_query($sqlQuery);
		while ($row = $this->Db->sql_array($sqlRecord))
		{
			$navButton = "<a href=\"javascript:editdata(".$row['id'].")\"><i class='fas fa-pen-square'></i></a>";
			if ($row['vUsername']!="admin")
			{
				$navButton .= "&nbsp;&nbsp;<a href=\"javascript:deletedata(".$row['id'].")\"><i class='fas fa-trash-alt'></i></a>";
			}
			$detailJabatan = $this->Module->Jabatan->detail($row['id_jabatan']);

			$status = ($row['isLogin']=="1")?"<a href=\"javascript:status(".$row['id'].",'0')\"><span class=\"badge badge-success\">ON</span></a>":"<a href=\"javascript:status(".$row['id'].",'1')\"><span class=\"badge badge-secondary\">OFF</span></a>";

			$dLastlogin = date("d M y - H:i:s", strtotime($row['dLastlogin']));
			$data[] = array(
				"vUsername" => "<span class=\"fs--1\">".$status."</span>&nbsp;&nbsp;".$this->Template->no_value($row['vUsername']),
				"vName" => $this->Template->no_value($row['vName']),
				"vNip" => $this->Template->no_value($row['vNip']),
				"vMobile" => $this->Template->no_value($row['vMobile']),
				"vEmail" => $this->Template->no_value($row['vEmail']),
				"id_jabatan" => $this->Template->no_value($detailJabatan['name']),
				"dLastLogin" => $this->Template->no_value($dLastlogin),
				"navButton" => $navButton,
			);
		}
		
		//Response
		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecordsWithFilter,
			"iTotalDisplayRecords" => $totalRecords,
			"aaData" => (($data)?$data:array())
		);
		
		echo json_encode($response);
	}

	function submit()
	{
		$vUsername = $_POST['vUsername'];
		$cPassword = $_POST['cPassword'];
		$vEmail = $_POST['vEmail'];
		$vName = $_POST['vName'];
		$vNip = $_POST['vNip'];
		$vMobile = $_POST['vMobile'];
		$id_jabatan = $_POST['id_jabatan'];
		$isLogin = ($_POST['isLogin']=="yes")?"1":"0";
		$vGambar = $this->Pile->simpanFile($_FILES['vGambar'], "user_".date('YmdHis').rand(0,9).rand(0,9).rand(0,9));
		$Action = $_POST['action'];
		switch ($Action)
		{
			case "add":
				if (($vUsername!="") AND ($vName!=""))
				{
					if ($this->Module->Auth->adduser(array(
						'vUsername' => $vUsername,
						'cPassword' => md5($cPassword),
						'vEmail' => $vEmail,
						'dLastLogin' => date("Y-m-d H:i:s"),
						'vName' => $vName,
						'vNip' => $vNip,
						'vMobile' => $vMobile,
						'vGambar' => $vGambar,
						'id_jabatan' => $id_jabatan,
						'isLogin' => $isLogin
					)))
					{	
						$Return = array('status' => 'success',
						'message' => $this->Template->showMessage('success', 'Data user telah di tambahkan'), 
						'data' => ''
						);
					}
					else
					{
						$Return = array('status' => 'error',
						'message' => $this->Template->showMessage('error', 'Ops! Ada error pada database'), 
						'data' => ''
						);
					}
				}
				else
				{
					$Return = array(
						'status' => 'error',
						'message' => $this->Template->showMessage('error', 'Data form isian tidak lengkap'), 
						'data' => ''
					);
				}
			break;
			case "update":
				if (($vUsername!="") AND ($vName!=""))
				{					
					$UpdateField = array(
						'vUsername' => $vUsername,
						'vEmail' => $vEmail,
						'vName' => $vName,
						'vNip' => $vNip,
						'vMobile' => $vMobile,
						'id_jabatan' => $id_jabatan,
						'isLogin' => $isLogin
					);

					if ($cPassword!="")
					{
						$UpdateField = array_merge($UpdateField,array('cPassword' => md5($cPassword)));
					}
					if ($vGambar != "") {
						$detail = $this->Module->Auth->detailAdmin($this->Id);
						$this->Pile->deleteOldFile($detail['vGambar']);
						$UpdateField = array_merge($UpdateField, array('vGambar'=> $vGambar));
					}

					if ($this->Module->Auth->updateUser($UpdateField,$this->Id))
						{
							$Return = array('status' => 'success',
							'message' => $this->Template->showMessage('success', 'Data user telah di perbaharui'), 
							'data' => ''
							);
						}
						else
						{
							$Return = array('status' => 'error',
							'message' => $this->Template->showMessage('error', 'Ops! Ada error pada database'), 
							'data' => ''
							);
						}
				}
				else
				{
					$Return = array('status' => 'error',
					'message' => $this->Template->showMessage('error', 'Ops! Data form isian tidak lengkap'), 
					'data' => ''
					);
				}
			break;
		}

		echo json_encode($Return);
	}

	function detail()
	{

		$Detail = $this->Module->Auth->detailAdmin($this->Id);
		$this->Template->assign("Detail", $Detail);

		$this->Template->assign("vAuth", json_decode($Detail['vAuth'], true));
		$this->Template->assign("vDir", json_decode($Detail['vDir'], true));

		echo $this->Template->ShowAdmin("account/useradmin_detail.html");
	}

	function add()
	{
		$this->Template->assign("listJabatan", $this->Module->Jabatan->listAll());
		echo $this->Template->ShowAdmin("account/useradmin_add.html");
	}

	function edit()
	{
		$this->Template->assign("listJabatan", $this->Module->Jabatan->listAll());
		$this->Template->assign("Detail", $this->Module->Auth->detailAdmin($this->Id));
		echo $this->Template->ShowAdmin("account/useradmin_edit.html");
	}

	function status()
	{
		if ($this->Id!="")
		{
			$status = $_GET['status'];
			if ($this->Module->Auth->updateUser(array('isLogin' => $status),$this->Id))
			{
				$Return = array('status' => 'success',
				'message' => $this->Template->showMessage('success', 'Status user telah di perbaharui'), 
				'data' => ''
				);
			}
		}
		else
		{
			$Return = array('status' => 'error',
			'message' => $this->Template->showMessage('error', 'Ops! ID user tidak valid'), 
			'data' => ''
			);			
		}

		echo json_encode($Return);
	}

	function delete()
	{
		if ($this->Id!="")
		{
			if ($this->Module->Auth->deleteUser($this->Id))
			{
				$Return = array('status' => 'success',
				'message' => $this->Template->showMessage('success', 'Data user telah di hapus'), 
				'data' => ''
				);
			}
		}
		else
		{
			$Return = array('status' => 'error',
			'message' => $this->Template->showMessage('error', 'Ops! ID user tidak valid'), 
			'data' => ''
			);			
		}

		echo json_encode($Return);
	}

	// function getdata()
	// {
	// 	$response = file_get_contents('pokja.csv');
	// 	$pokja_ = explode("\n",$response);

	// 	for ($i=0;$i<count($pokja_);$i++)
	// 	{
	// 		$Data = explode(";",$pokja_[$i]);
	// 		//echo $Data[0]."<br />".$Data[1]."<br />".$Data[2]."<br />".$Data[3]."<br />".$Data[4];
	// 		// echo "<br />----------------<br />";

	// 		$detailBySlug = $this->Module->Jabatan->detailname(trim($Data[3]));
	// 		$username = preg_replace("# #","-",strtolower(preg_replace("/[^a-zA-Z0-9\-\s]/", "", $Data[2])));

	// 		if ($Data[0]!="")
	// 		{
	// 			$dataExport = array(
	// 				'vUsername' => $username,
	// 				'cPassword' => md5('12345'),
	// 				'vEmail' => 'email@email.com',
	// 				'dLastlogin' => date("Y-m-d H:i:s"),
	// 				'vName' => $Data[0],
	// 				'vNip' => $Data[1],
	// 				'vMobile' => '0811111',
	// 				'id_opd' => '0',
	// 				'id_jabatan' => $detailBySlug['id'],
	// 				'isLogin' => '0'
	// 			);

	// 			$this->Module->Auth->adduser($dataExport);
	// 		}

	// 		print_r($dataExport);
	// 		echo "<br />----------------<br />";
	// 	}

	// 	//echo "Import berhasil";
	// }

}

?>