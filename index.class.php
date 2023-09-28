<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class index extends Core
{
	function __construct()
	{
		parent::__construct();
		
		$this->LoadModule("Paging");
		$this->Module->Paging->setPaging(20,10,"&laquo; Prev","Next &raquo;");
		$this->LoadModule("Paket");
		
		//Load General Process
		include '../inc/general.php';
		echo "<script>location.href=\"".$this->Config['admin']['url']."\"</script>";
		die();
	}

	function main()
	{
		$dataTahun = ($_GET['tahun'])?$_GET['tahun']:date("Y");
		$this->Template->assign("getTahun", $dataTahun);
		
		$this->Template->assign("sumAPBD", $this->Db->sql_query_array("SELECT SUM(pagu) AS total FROM lpse_paket_sirup WHERE (tanggal_awal_pengadaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (JSON_CONTAINS(paket_anggaran, '{\"sumber_dana\":1}'))"));
		// $this->Template->assign("sumAPBD", $this->Db->sql_query_array("SELECT SUM(pagu_rup) AS total FROM paket_pekerjaan WHERE (awal_pekerjaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (sumber_dana LIKE '%apbd%' OR sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%APBD,%')"));
		
		$this->Template->assign("sumNonAPBN", $this->Db->sql_query_array("SELECT SUM(pagu) AS total FROM lpse_paket_sirup WHERE (tanggal_awal_pengadaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (!JSON_CONTAINS(paket_anggaran, '{\"sumber_dana\":1}'))"));
		// $this->Template->assign("sumNonAPBN", $this->Db->sql_query_array("SELECT SUM(pagu_rup) AS total FROM paket_pekerjaan WHERE (awal_pekerjaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (sumber_dana NOT LIKE '%apbd%' OR sumber_dana IS NULL)"));

		$this->Template->assign("sumTender", $this->Db->sql_query_array("SELECT SUM(pagu) AS total FROM lpse_paket_sirup WHERE (tanggal_awal_pengadaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (metode_pengadaan='20' OR metode_pengadaan='14' OR metode_pengadaan='13')"));
		// $this->Template->assign("sumTender", $this->Db->sql_query_array("SELECT SUM(pagu_rup) AS total FROM paket_pekerjaan WHERE (awal_pekerjaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND metode_pemilihan='seleksi'"));

		$this->Template->assign("sumNonTender", $this->Db->sql_query_array("SELECT SUM(pagu) AS total FROM lpse_paket_sirup WHERE (tanggal_awal_pengadaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (metode_pengadaan!='20' AND metode_pengadaan!='14' AND metode_pengadaan!='13')"));
		// $this->Template->assign("sumNonTender", $this->Db->sql_query_array("SELECT SUM(pagu_rup) AS total FROM paket_pekerjaan WHERE (awal_pekerjaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND metode_pemilihan!='seleksi'"));
		
		$this->Template->assign("sumFisik", $this->Db->sql_query_array("SELECT SUM(pagu) AS total FROM lpse_paket_sirup WHERE (tanggal_awal_pengadaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (nama LIKE '%pengadaan%' OR nama LIKE '%pembangunan%')"));
		//$this->Template->assign("sumFisik", $this->Db->sql_query_array("SELECT SUM(pagu_rup) AS total FROM paket_pekerjaan WHERE (awal_pekerjaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (nama_paket LIKE '%pengadaan%' OR nama_paket LIKE '%pembangunan%')"));
		
		$this->Template->assign("sumNonFisik", $this->Db->sql_query_array("SELECT SUM(pagu) AS total FROM lpse_paket_sirup WHERE (tanggal_awal_pengadaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (nama NOT LIKE '%pengadaan%' AND nama NOT LIKE '%pembangunan%')"));
		//$this->Template->assign("sumNonFisik", $this->Db->sql_query_array("SELECT SUM(pagu_rup) AS total FROM paket_pekerjaan WHERE (awal_pekerjaan BETWEEN '".$dataTahun."-01-01' AND '".$dataTahun."-12-31') AND (nama_paket NOT LIKE '%pengadaan%' AND nama_paket NOT LIKE '%pembangunan%')"));


		$this->Template->assign("sum_Seleksi", $this->Module->Paket->sum_paket("seleksi", $dataTahun));
		$this->Template->assign("sum_Purchasing", $this->Module->Paket->sum_paket("e-purchasing", $dataTahun));
		$this->Template->assign("sum_PL", $this->Module->Paket->sum_paket("pl", $dataTahun));

		//$this->Template->assign("sumNonTender", $this->Db->sql_query_array("SELECT SUM(nilai_pagu) AS total_pagu FROM data_rpp WHERE tahun='".$dataTahun."' AND metode!='tender'"));

		echo $this->Template->Show("index.html");
	}
}

?>