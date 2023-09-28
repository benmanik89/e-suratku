<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class myjabatan extends Core
{
	var $Submit, $Action, $Do, $Id, $idStatus, $DetailAdmin, $getTahun;
	public function __construct()
	{
		parent::__construct();
		
		//Load General Process
		include '../inc/general_admin.php';

		$this->LoadModule("Jabatan");
		$this->Template->assign("Signature", "master");
		if ($this->DetailAdmin['jabatan_slug'] != 'superadmin'){
			echo $this->Template->ShowAdmin("404.html");
			die();
		}
		ob_clean();
	}
		
	function main()
	{
		echo $this->Template->ShowAdmin("jabatan/jabatan_index.html");
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
		$records = $this->Db->sql_query_array("select count(*) as total from jabatan");
		$totalRecords = $records['total'];
		
		//Total Record with filtering
		$records = $this->Db->sql_query_array("select count(*) as total from jabatan where id!='0'".$searchQuery);
		$totalRecordsWithFilter = $records['total'];
		
		//Fetch Records
		$columnName = "";
		$orderBy = ($columnName=="")?" order by id asc":" order by ".$columnName." ".$columnSortOrder;
		$limitBy = ($row=="")?"":" limit ".$row.",".$rowperpage;
		
		$sqlQuery = "select * from jabatan where id!='0'".$searchQuery.$orderBy.$limitBy;
			
		$sqlRecord = $this->Db->sql_query($sqlQuery);
		while ($row = $this->Db->sql_array($sqlRecord))
		{
			$navButton = "<a href=\"javascript:editdata(".$row['id'].")\"><i class='fas fa-pen-square'></i></a>&nbsp;&nbsp;
			<a href=\"javascript:deletedata(".$row['id'].")\"><i class='fas fa-trash-alt'></i></a>";
			
			$data[] = array(
				"name" => $row['name'],
				"slug" => $row['slug'],
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
		$name = $_POST['name'];
		$slug = ($_POST['slug']=="")?preg_replace("# #","-",strtolower(preg_replace("/[^a-zA-Z0-9\-\s]/", "", $name))):preg_replace("# #","-",strtolower(preg_replace("/[^a-zA-Z0-9\-\s]/", "", $_POST['slug'])));

		$Action = $_POST['action'];

		switch ($Action)
		{
			case "add":
				if ($name!="")
				{
					if ($this->Module->Jabatan->add(array(
						'name' => $name,
						'slug' => $slug,
					)))
					{	
						$Return = array('status' => 'success',
						'message' => $this->Template->showMessage('success', 'Data jabatan telah di tambahkan'), 
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
						'slug' => $slug,
					);

					if ($this->Module->Jabatan->update($UpdateField,$this->Id))
						{
							$Return = array('status' => 'success',
							'message' => $this->Template->showMessage('success', 'Data jabatan telah di perbaharui'), 
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
		echo $this->Template->ShowAdmin("jabatan/jabatan_add.html");
	}

	function edit()
	{
		$this->Template->assign("Detail", $this->Module->Jabatan->detail($this->Id));
		echo $this->Template->ShowAdmin("jabatan/jabatan_edit.html");
	}

	function delete()
	{
		if ($this->Id!="")
		{
			if ($this->Module->Jabatan->delete($this->Id))
			{
				$Return = array('status' => 'success',
				'message' => $this->Template->showMessage('success', 'Data jabatan telah di hapus'), 
				'data' => ''
				);
			}
		}
		else
		{
			$Return = array('status' => 'error',
			'message' => $this->Template->showMessage('error', 'Ops! ID jabatan tidak valid'), 
			'data' => ''
			);			
		}

		echo json_encode($Return);
	}

}

?>