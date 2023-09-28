<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

	//File Settings
	$config['file']['path']	= "upload";
	$config['file']['allowed_types'] = "image/pjpeg|image/jpg|image/jpeg|image/gif|image/x-icon|image/png"; //allowed file types
	$config['file']['max_size']	= "1000"; //in Kb
	$config['file']['allowed_overwrite'] = true;
	
	//Photo Thumbnail size (width,height)
	$config['photo']['thumbnail'] = array(128,128); //140,100
	//Temporary Directory
	$config['temp']['dir'] = "upload/temp/";
	//Check Images
	$config['check']['images'] = "no";
	//Pages Dir
	$config['page']['dir'] = "upload/pagesdir/";
	//Content Dir
	$config['content']['dir'] = "upload/contentdir/";
	//Images Dir
	$config['gambar']['dir'] = "upload/gambar/";
	//Member Banner Directory
	$config['upload']['banner'] = "upload/bannerdir/";
	//Banner Directory
	$config['upload']['bannerdir'] = "upload/mainbanner/";
	//Gallery Directory
	$config['upload']['photodir'] = "upload/gambar/";
	//Upload Dir
	$config['upload']['dir'] = "upload/upload/";
	//Sertifikat Dir
	$config['sertifikat']['image'] = "upload/sertifikat/";
	//Document Dir
	$config['document']['dir'] = "upload/doc/";
	//User Dir
	$config['user']['dir'] = "upload/user/";
	//Data Backup
	$config['data']['backup'] = "../backup/";

	//Images FileType
	$config['images']['filetype']="image/png,image/pjpeg,image/jpg,image/jpeg,image/gif,application/x-shockwave-flash,image/x-icon,image/svg+xml,image/svg";
	//Document FileType
	$config['doc']['filetype']="image/png,image/pjpeg,image/jpg,image/jpeg,image/gif,application/x-shockwave-flash,image/x-icon,image/svg+xml,image/svg,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/zip,application/x-zip-compressed,multipart/x-zip,application/x-compressed,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation";
	
	//max size of icon picture than can be uploaded is: 200 Kb
	$config['file']['max_icon_size'] = 100000000;
	//max size of file that can be uploaded is: 2 Mb
	$config['file']['max_file_size'] = 100000000;
	//max size of file for database uploaded is: 8 Mb
	$config['file']['max_file_backup'] = 100000000;
	//Photo Thumbnail size (width,height)
	$config['photo']['thumbnail'] = array(121,96); //140,100
	//Content
	$config['content']['thumbnail'] = array(400,200);
	//Icon Thumbnail
	$config['icon']['thumbnail'] = array(100,100);

	//Member File Type
	$config['member']['filetype'] = "image/pjpeg|image/jpg|image/jpeg|image/gif|image/x-icon|image/png|text/plain|application/zip|application/x-shockwave-flash|application/msword|application/vnd.openxmlformats-officedocument.wordprocessingml.document|application/vnd.ms-powerpoint|application/vnd.openxmlformats-officedocument.presentationml.presentation|application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|application/vnd.ms-excel";
	//Member File Max Size
	$config['member']['filemax'] = "3000";

?>