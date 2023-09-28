<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class myjenissurat extends Core
{
	var $Submit, $Action, $Do, $Id, $idStatus, $DetailAdmin, $getTahun;
	public function __construct()
	{
		parent::__construct();
		
		//Load General Process
		include '../inc/general_admin.php';

		$this->LoadModule("Jenissurat");

		$this->Template->assign("Signature", "master");

		// $this->Module->Auth->verifyAdmin(array('superadmin'), "", $this->DetailAdmin);
		if ($this->DetailAdmin['jabatan_slug'] != 'superadmin'){
			echo $this->Template->ShowAdmin("404.html");
			die();
		}
		ob_clean();
	}
		
	function main()
	{
		echo $this->Template->ShowAdmin("unitkerja/unitkerja_index.html");
	}

	function loaddata()
	{
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
			$searchQuery = " AND (name like '%".$searchValue."%')";
		}
		
		//Total Records without Filtering
		$records = $this->Db->sql_query_array("select count(*) as total from jenis_surat");
		$totalRecords = $records['total'];
		
		//Total Record with filtering
		$records = $this->Db->sql_query_array("select count(*) as total from jenis_surat where id!='0'".$searchQuery);
		$totalRecordsWithFilter = $records['total'];
		
		//Fetch Records
		$columnName = "";
		$orderBy = ($columnName=="")?" order by id asc":" order by ".$columnName." ".$columnSortOrder;
		$limitBy = ($row=="")?"":" limit ".$row.",".$rowperpage;
		
		$sqlQuery = "select * from jenis_surat where id!='0'".$searchQuery.$orderBy.$limitBy;
			
		$sqlRecord = $this->Db->sql_query($sqlQuery);
		while ($row = $this->Db->sql_array($sqlRecord))
		{
			$navButton = "<a href=\"javascript:editdata(".$row['id'].")\"><i class='fas fa-pen-square'></i></a>&nbsp;&nbsp;
			<a href=\"javascript:deletedata(".$row['id'].")\"><i class='fas fa-trash-alt'></i></a>";

			$data[] = array(
				"name" => $row['name'],
				"navButton" => $navButton,
			);
		}
		
		//Response
		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => intval($totalRecordsWithFilter),
			"iTotalDisplayRecords" => intval($totalRecords),
			"aaData" => (($data)?$data:array())
		);
		
		echo json_encode($response);
	}

	function submit()
	{
		$name = $_POST['name'];

		$Action = $_POST['action'];
		switch ($Action)
		{
			case "add":
				if ($name!="")
				{
					if ($this->Module->Jenissurat->add(array(
						'name' => $name
					)))
					{	
						$Return = array('status' => 'success',
							'message' => $this->Template->showMessage('success', 'Jenis surat telah di tambahkan'), 
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
				if ($name!="")
				{					
					$UpdateField = array(
						'name' => $name,
					);

					if ($this->Module->Jenissurat->update($UpdateField,$this->Id))
						{
							$Return = array('status' => 'success',
							'message' => $this->Template->showMessage('success', 'Jenis surat telah di perbaharui'), 
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

    function add()
	{
		echo $this->Template->ShowAdmin("unitkerja/unitkerja_add.html");
	}

	function edit()
	{
		$this->Template->assign("Detail", $this->Module->Jenissurat->detail($this->Id));
		echo $this->Template->ShowAdmin("unitkerja/unitkerja_edit.html");
	}

	function delete()
	{
		if ($this->Id!="")
		{
			if ($this->Module->Jenissurat->delete($this->Id))
			{
				$Return = array('status' => 'success',
				'message' => $this->Template->showMessage('success', 'Jenis surat telah di hapus'), 
				'data' => ''
				);
			}
		}
		else
		{
			$Return = array('status' => 'error',
			'message' => $this->Template->showMessage('error', 'Ops! ID Jenis surat tidak valid'), 
			'data' => ''
			);			
		}

		echo json_encode($Return);
	}

}

?>