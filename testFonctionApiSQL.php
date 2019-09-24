<?php
error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);
include_once("Model/apiSQL.php");
include_once("Model/apiPHP.php");

$mySql = new apiSQL('127.0.01', '5432', 'irbas_test', 'postgres', 'postgres');

//initialization
$typeMesure = 'flow_regime';
$arrayDate = array(array('year'=>'2005'),array('year'=>'2006'));
$arrayMeta = array('creation date'=>'01/02/2015','data owner'=>'testeur');

//create entity
$idSite = $mySql->insertGeo('site_test1', 'site');
$idCountry = $mySql->insertGeo('country_test1', 'country');
print($idSite.'<br />');
//create reltionship
$mySql->insertGeoInclusion($idCountry,$idSite);
//test measure existence
if(is_null($mySql->selectMesureGeo($idSite,$typeMesure,$arrayDate,$arrayMeta)))
{
	//insert measure
	$idMeasure= $mySql->insertMesureGeo($idSite,$typeMesure,'discrete');
	print($idMeasure.'<br />');
	//insert date to the measure
	print($mySql->insertMesureGeoDate($idMeasure,'2005','year','sampling_date_start').'<br />');
	print($mySql->insertMesureGeoDate($idMeasure,'2006','year','sampling_date_end').'<br />');
	//insert metadata
	print($mySql->insertMetadata('creation date',$idMeasure,'01/02/2015').'<br />');
	print($mySql->insertMetadata('data owner',$idMeasure,'testeur').'<br />');
}


$typeMesure = 'climate';
if(is_null($mySql->selectMesureGeo($idSite,$typeMesure,$arrayDate,$arrayMeta)))
{
	//insert measure
	$idMeasure= $mySql->insertMesureGeo($idSite,$typeMesure,'shiny');
	print($idMeasure.'<br />');
	//insert date to the measure
	print($mySql->insertMesureGeoDate($idMeasure,'2005','year','sampling_date_start').'<br />');
	print($mySql->insertMesureGeoDate($idMeasure,'2006','year','sampling_date_end').'<br />');
	//insert metadata
	print($mySql->insertMetadata('creation date',$idMeasure,'01/02/2015').'<br />');
	print($mySql->insertMetadata('data owner',$idMeasure,'testeur').'<br />');
}
?>
