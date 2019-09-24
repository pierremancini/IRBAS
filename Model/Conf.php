<?php

/**
* Class used to store configuration parameters for the web administrator
*/
class Conf
{

	//-- Common part --

	//Range of values allowed (in fauna and environment file)
	public static $measurementForks = array(
							'sampling_protocol_mesh_size' => array('min' => 0, 'max' => 10000),
							'number_samples' => array('min'=>1,'max'=>100),
							'quantitative_sampling_depth' => array('min'=>0,'max'=>100),
							'area_sampled' => array('min'=>0,'max'=>50),
							'volume_sampled' => array('min'=>0,'max'=>5),
							'time_per_sample' => array('min'=>0,'max'=>300000),
							'TN' => array('min'=>0,'max'=>100),
							'TKN' => array('min'=>0,'max'=>100),
							'TP' => array('min'=>0,'max'=>100),
							'TC' => array('min'=>0,'max'=>100),
							'TON' => array('min'=>0,'max'=>100),
							'TOP' => array('min'=>0,'max'=>100),
							'TOC' => array('min'=>0,'max'=>100),
							'DOP' => array('min'=>0,'max'=>100),
							'DOC' => array('min'=>0,'max'=>100),
							'NH4' => array('min'=>0,'max'=>100),
							'NOX' => array('min'=>0,'max'=>100),
							'NO2' => array('min'=>0,'max'=>100),
							'NO3' => array('min'=>0,'max'=>100),
							'SP' => array('min'=>0,'max'=>100),
							'DIP_SRP' => array('min'=>0,'max'=>100),
							'SO4' => array('min'=>0,'max'=>100),
							'Cl' => array('min'=>0,'max'=>100),
							'DL_TN'=> array('min'=>0,'max'=>100),
							'DL_TKN' => array('min'=>0,'max'=>100),
							'DL_TP' => array('min'=>0,'max'=>100),
							'DL_TC' => array('min'=>0,'max'=>100),
							'DL_TON' => array('min'=>0,'max'=>100),
							'DL_TOP' => array('min'=>0,'max'=>100),
							'DL_TOC' => array('min'=>0,'max'=>100),
							'DL_DOP' => array('min'=>0,'max'=>100),
							'DL_DOC' => array('min'=>0,'max'=>100),
							'DL_NH4' => array('min'=>0,'max'=>100),
							'DL_NOX' => array('min'=>0,'max'=>100),
							'DL_NO2' => array('min'=>0,'max'=>100),
							'DL_NO3' => array('min'=>0,'max'=>100),
							'DL_SP' => array('min'=>0,'max'=>100),
							'DL_DIP' => array('min'=>0,'max'=>100),
							'DL_SO4' => array('min'=>0,'max'=>100),
							'DL_Cl' => array('min'=>0,'max'=>100),
							'mean_depth_thalweg'=> array('min'=>0,'max'=>100),
							'mean_wetted_width'=> array('min'=>0,'max'=>10000),
							'SWGW_difference'=> array('min'=>-1000,'max'=>1000),
							'VHG'=> array('min'=>-1000,'max'=>1000),
							'pH'=> array('min'=>1 ,'max'=>14),
							'electrical_conductivity'=> array('min'=>0,'max'=>2000),
							'specific_conductance'=> array('min'=>0,'max'=>2000),
							'temperature'=> array('min'=>-50,'max'=>50),
							'dissolved_oxygen_saturated'=> array('min'=>0,'max'=>200),
							'dissolved_oxygen_ppm'=> array('min'=>0,'max'=>15),
							'Secchi'=> array('min'=>0,'max'=>100),
							'turbidity'=> array('min'=>0,'max'=>5000),
							'discharge'=> array('min'=>0,'max'=>500),
							'discharge_from_gauge'=> array('min'=>0,'max'=>500),
							'chlorophyll_fluorescence'=> array('min'=>0,'max'=>100),
							'alkalinity'=> array('min'=>0,'max'=>6),
							'hardness'=> array('min'=>0,'max'=>1000),
							'total_suspended_solids'=> array('min'=>0,'max'=>5000),
							'chlorophyll_concentration'=> array('min'=>0,'max'=>100),
							'fine_sediment'=> array('min'=>0,'max'=>100),
							'clay'=> array('min'=>0,'max'=>100),
							'silt'=> array('min'=>0,'max'=>100),
							'sand'=> array('min'=>0,'max'=>100),
							'gravel'=> array('min'=>0,'max'=>100),
							'pebble'=> array('min'=>0,'max'=>100),
							'cobble'=> array('min'=>0,'max'=>100),
							'boulder'=> array('min'=>0,'max'=>100),
							'bedrock'=> array('min'=>0,'max'=>100),
							'macrophytes'=> array('min'=>0,'max'=>100),
							'bare'=> array('min'=>0,'max'=>100),
							'detritus'=> array('min'=>0,'max'=>100),
							'canopy_cover'=> array('min'=>0,'max'=>100)
							);

	//-- Part MetaData --

	//Array used in the constructor of MetaData.php
	public static $metaDataMandatoryFields = array(
											'dataset_name'=>'dataSetName',
											'creation_date'=>'creationDate',
											'data_owner'=>'dataOwner',
											'data_provider'=>'dataProvider',
											'contact_name'=>'contactName',
											'contact_email'=>'eMail',
											'coder'=>'coder',
											'availability'=>'availability'
											);
	
	public static $metaDataVocabulary= array(
		'availability'=>array('0','1'),'reference_type'=>array('Journal article',
		'Book', 'Thesis', 'Conference proceedings', 'Personal communication', 'Newspaper article', 
		'Computer program', 'Book section', 'Magazine article', 'Edited book', 'Report', 'Map', 'Audiovisual',
		'MaterialArtwork', 'Patent', 'Electronic source', 'Bill', 'Case', 'Hearing', 'Manuscript',
		'Film or broadcast','Statute', 'Figure', 'Chart or table', 'Equation', 'Electronic article',
		'Electronic book','Online database','Generic', 'Government document', 'Conference paper',
		'Online multimedia','Classical works','Legal rule/ regulation','Unpublished work'
		));

	//-- Part Site --

	//In the file the colunm must be present even if there is the value may not be mandatory
	public static $siteMandatoryColumn = array('site_name',
	'location_name',
	'river_name',
	'catchment_name',
	'country',
	'lat_location',
	'long_location',
	'flow_regime',
	'flow_regime_timespan');

	// Vocabulary allowed in site columns
	public static $siteVocabulary = array('climate' => array('Af', 'Am', 'Aw', 'BWh', 'Bwk', 'BSh', 'BSk',
	 'Csa', 'Csb', 'Cwa', 'Cwb', 'Cwc', 'Cfa', 'Cfb', 'Cfc', 'Dsa', 'Dsb', 'Dsc', 'Dsd', 'Dwa', 'Dwb',
	  'Dwc', 'Dwd', 'Dfa', 'Dfb', 'Dfc', 'Dfd', 'ET', 'EF'),
	'flow_regime' => array('perennial', 'non-perennial', 'unknown'),
	'water_regime' => array('permanent', 'non-permanent', 'unknown'),
	'human_mod_level' => array('modified', 'low modification', 'unmodified'),
	'flow_impact_US' => array('yes', 'no'),
	'WQ_impact_US' => array('yes', 'no'),
	'ZF_season' => array('summer', 'autumn', 'winter', 'spring', 'unseasonal', 'NA'),
	'ZF_regularity' => array('annually predictable', 'unpredictable', 'NA')
	);

	//Range of values allowed in site file
	public static $siteForks = array(
								'altitude' => array('min'=>-400,'max'=>9000),
								'USconfluence_distance' => array('min'=>0,'max'=>7000),
								'source_distance' => array('min'=>0,'max'=>7001),
								'mouth_distance' => array('min'=>0,'max'=>7000),
								'DSconfluence_distance' => array('min'=>0,'max'=>7000),
								'discharge_mean_annual' => array('min'=>0,'max'=>1000000),
								'discharge_min_annual' => array('min'=>0,'max'=>1000000),
								'discharge_max_annual' => array('min'=>0,'max'=>1000000),
								'ZFD_mean_annual' => array('min'=>0,'max'=>366),
								'ZFD_min_annual' => array('min'=>0,'max'=>366),
								'ZFD_max_annual' => array('min'=>0,'max'=>366),
								'rainfall_mean_annual' => array('min'=>0,'max'=>15000),
								'rainfall_min_annual' => array('min'=>0,'max'=>15000),
								'rainfall_max_annual' => array('min'=>0,'max'=>15000),
								'temperature_mean_annual' => array('min'=>-50,'max'=>50),
								'temperature_min_annual' => array('min'=>-50,'max'=>50),
								'temperature_max_annual' => array('min'=>-50,'max'=>50),
								'mean_annual_ZF_dur' => array('min'=>0,'max'=>12),
								'mean_annual_dry_dur' => array('min'=>0,'max'=>12)
								);

	//Column that must be followed by timespan column
	public static $siteMandatoryTimespan = 
		array('discharge_mean_annual',
			'discharge_min_annual',
			'discharge_max_annual',
			'ZFD_mean_annual',
			'ZFD_min_annual',
			'ZFD_max_annual',
			'rainfall_mean_annual',
			'rainfall_min_annual',
			'rainfall_max_annual',
			'temperature_mean_annual',
			'temperature_min_annual',
			'temperature_max_annual',
			'LULC',
			'human_mod_level',
			'flow_impact_US',
			'WQ_impact_US',
			'mean_annual_ZF_dur',
			'mean_annual_dry_dur',
			'ZF_season',
			'ZF_regularity');

	//Values allowed in the column Country in site file
	public static $siteAllowedCountry =
		array('Afghanistan',
			'Albania',
			'Algeria',
			'Andorra',
			'Angola',
			'Antigua & Deps',
			'Argentina',
			'Armenia',
			'Australia',
			'Austria',
			'Azerbaijan',
			'Bahamas',
			'Bahrain',
			'Bangladesh',
			'Barbados',
			'Belarus',
			'Belgium',
			'Belize',
			'Benin',
			'Bhutan',
			'Bolivia',
			'Bosnia Herzegovina',
			'Botswana',
			'Brazil',
			'Brunei',
			'Bulgaria',
			'Burkina',
			'Burundi',
			'Cambodia',
			'Cameroon',
			'Canada',
			'Cape Verde',
			'Central African Rep',
			'Chad',
			'Chile',
			'China',
			'Colombia',
			'Comoros',
			'Congo',
			'Democratic Rep Congo',
			'Costa Rica',
			'Croatia',
			'Cuba',
			'Cyprus',
			'Czech Republic',
			'Denmark',
			'Djibouti',
			'Dominica',
			'Dominican Republic',
			'East Timor',
			'Ecuador',
			'Egypt',
			'El Salvador',
			'England',
			'Equatorial Guinea',
			'Eritrea',
			'Estonia',
			'Ethiopia',
			'Fiji',
			'Finland',
			'France',
			'Gabon',
			'Gambia',
			'Georgia',
			'Germany',
			'Ghana',
			'Greece',
			'Grenada',
			'Guatemala',
			'Guinea',
			'Guinea-Bissau',
			'Guyana',
			'Haiti',
			'Honduras',
			'Hungary',
			'Iceland',
			'India',
			'Indonesia',
			'Iran',
			'Iraq',
			'Ireland',
			'Israel',
			'Italy',
			'Ivory Coast',
			'Jamaica',
			'Japan',
			'Jordan',
			'Kazakhstan',
			'Kenya',
			'Kiribati',
			'Korea North',
			'Korea South',
			'Kosovo',
			'Kuwait',
			'Kyrgyzstan',
			'Laos',
			'Latvia',
			'Lebanon',
			'Lesotho',
			'Liberia',
			'Libya',
			'Liechtenstein',
			'Lithuania',
			'Luxembourg',
			'Macedonia',
			'Madagascar',
			'Malawi',
			'Malaysia',
			'Maldives',
			'Mali',
			'Malta',
			'Marshall Islands',
			'Mauritania',
			'Mauritius',
			'Mexico',
			'Micronesia',
			'Moldova',
			'Monaco',
			'Mongolia',
			'Montenegro',
			'Morocco',
			'Mozambique',
			'Myanmar',
			'Burma',
			'Namibia',
			'Nauru',
			'Nepal',
			'Netherlands',
			'New Zealand',
			'Nicaragua',
			'Niger',
			'Nigeria',
			'Nothern Ireland',
			'Norway',
			'Oman',
			'Pakistan',
			'Palau',
			'Panama',
			'Papua New Guinea',
			'Paraguay',
			'Peru',
			'Philippines',
			'Poland',
			'Portugal',
			'Qatar',
			'Romania',
			'Russian Federation',
			'Rwanda',
			'St Kitts & Nevis',
			'St Lucia',
			'Saint Vincent & the Grenadines',
			'Samoa',
			'San Marino',
			'Sao Tome & Principe',
			'Saudi Arabia',
			'Scotland',
			'Senegal',
			'Serbia',
			'Seychelles',
			'Sierra Leone',
			'Singapore',
			'Slovakia',
			'Slovenia',
			'Solomon Islands',
			'Somalia',
			'South Africa',
			'South Sudan',
			'Spain',
			'Sri Lanka',
			'Sudan',
			'Suriname',
			'Swaziland',
			'Sweden',
			'Switzerland',
			'Syria',
			'Taiwan',
			'Tajikistan',
			'Tanzania',
			'Thailand',
			'Togo',
			'Tonga',
			'Trinidad & Tobago',
			'Tunisia',
			'Turkey',
			'Turkmenistan',
			'Tuvalu',
			'Uganda',
			'Ukraine',
			'United Arab Emirates',
			'United Kingdom',
			'United States of America',
			'Uruguay',
			'Uzbekistan',
			'Vanuatu',
			'Vatican City',
			'Venezuela',
			'Vietnam',
			'Wales',
			'Yemen',
			'Zambia',
			'Zimbabwe');



	//-- Part Environment --
	
	// Order of column in environment file
	public static $environmentHeaderOrder = array('location_name','sample_name','sampling_date_start','sample_type','sampling_strategy',
			'sampling_strategy_coverage','sampling_strategy_processing','sampling_protocol','sampling_zone',
			'sampling_habitat');


	//Mandatory fields that we can't leave blank
	public static $environmentMandatoryFields = array('location_name','sample_name','sample_type','sampling_date_start',
	'sampling_season','water_state','flow_state','sampling_strategy','sampling_strategy_coverage','sampling_zone',
	'sampling_habitat','number_samples');

	//Mandatory column of environment file (value may be left blank)
	public static $environmentMandatoryColumn= array('location_name',
	'sample_name',
	'sampling_date_start',
	'sampling_date_end',
	'sampling_season',
	'water_state',
	'flow_state',
	'sample_type',
	'sampling_strategy',
	'sampling_strategy_coverage',
	'sampling_strategy_processing',
	'sampling_strategy_treatment',
	'sampling_protocol',
	'sampling_zone',
	'sampling_habitat',
	'number_samples',
	'quantitative_sampling_depth',
	'qualitative_sampling_depth',
	'area_sampled',
	'volume_sampled',
	'time_per_sample'
	);

	//Chemicals that have detection limit
	public static $chemicalsWithDetectionLimit=array('TN','TKN','TP','TC','TON','TOP','TOC','DOP',
	'DOC','NH4','NOX','NO2','NO3','SP','DIP_SRP','SO4','Cl');

	//-- The five followings arrays are used to classified a value in one of the five type of value allowed by 
	//the data base. 

	public static $typeSamplingArray=array("biota_type","sampling_season","sampling_strategy_coverage",
		"sampling_protocol_mesh_size","sampling_zone","sampling_habitat","sampling_strategy_processing",
		"sampling_strategy_treatment");

	public static $typeSampleArray=array("water_state","flow_state","quantitative_sampling_depth",
					"qualitative_sampling_depth","area_sampled","volume_sampled","time_per_sample");

	public static $typeCoverDescription=array("fine_sediment","clay","silt","sand","gravel","pebble","cobble",
		"boulder","bedrock","macrophytes","bare","detritus","canopy_cover");

	public static $typeWaterBodyDimensions=array("mean_depth_thalweg","mean_wetted_width");

	public static $typePhysicalAndChemical=array("pH","electrical_conductivity","specific_conductance","temperature",
					"dissolved_oxygen_saturated","dissolved_oxygen_ppm","Secchi","Turbidity","discharge",
					"discharge_from_gauge","chlorophyll_fluorescence","TN","TKN","TP","TC","TON","TOP","TOC","NH4","NOX",
					"NO2","NO3","DIP_SRP","DL_TN","DL_TKN","DL_TP","DL_TC","DL_TON","DL_TOP","DL_TOC","DL_NH4","DL_NOX",
					"DL_NO2","DL_NO3","DL_DIP_SRP","total_suspended_solids","chlorophyll_concentration","DOP","DL_DOP","DOC","DL_DOC",
					"SP","DL_SP","SO4","DL_SO4","Cl","DL_Cl","SWGW_difference","VHG",'hardness',"alkalinity","turbidity");



	public static $environmentVocabulary = array('sampling_season' => array('winter', 'spring', 'summer', 'autumn'), 
		'water_state' => array('wet', 'dry', 'unknown'),
	 	'flow_state' => array('no flow', 'flow' , 'dry', 'unknown' ),
	 	'sample_type' => array('waterbody dimensions', 'physicochemistry', 'cover descriptions'),
	 	'sampling_strategy' => array('random', 'haphazard', 'systematic', 'stratified', 'stratified random', 'stratified haphazard', 'stratified systematic', 'NA', 'entire location'),
	 	'sampling_protocol' => array('water quality probe', 'data-logger', 'bucket',
	 	'visual observation', 'densiometer', 'quadrat', 'Van-Dorn sampler', 'dipnet', 'plankton net', 'NA'),
	 	'sampling_zone' => array('riparian zone', 'hyporheic zone', 'benthic zone', 'water column', 'groundwater', 'other'),
	 	'qualitative_sampling_depth' => array('surface', 'bottom', 'complete depth profile', 'NA')
		);



	//-- Part Fauna --

	//Mandatory fields that we can't leave blank
	public static $faunaMandatoryFields = array('location_name','sample_name','sampling_date_start',
	'sampling_season','water_state','flow_state','biota_type','sampling_strategy','sampling_strategy_coverage',
	'sampling_zone','sampling_habitat','number_samples','type_of_abundance_data');

	//Mandatory fields that we can't leave blank
	public static $faunaMandatoryColumn = array('location_name',
	'sample_name',
	'sampling_date_start',
	'sampling_date_end',
	'sampling_season',
	'water_state',
	'flow_state',
	'biota_type',
	'sampling_strategy',
	'sampling_strategy_coverage',
	'sampling_strategy_treatment',
	'sampling_protocol',
	'sampling_protocol_mesh_size',
	'sampling_zone',
	'sampling_habitat',
	'number_samples',
	'quantitative_sampling_depth',
	'qualitative_sampling_depth',
	'area_sampled',
	'volume_sampled',
	'time_per_sample',
	'type_of_abundance_data'
	);

	public static $faunaVocabulary = array('sampling_season' => array('winter', 'spring', 'summer', 'autumn'), 
		'water_state' => array('wet', 'dry', 'unknown'),
		'flow_state' => array('no flow', 'flow' , 'blank', 'unknown' ),
		'biota_type'=> array('fish', 'aquatic invertebrates', 'terrestrial invertebrates', 'terrestrial vertebrates',
		'non-fish aquatic vertebrates', 'phytoplankton', 'benthic algae', 'bacteria', 'aquatic'),
		'sampling_strategy' => array('random', 'haphazard', 'systematic', 'stratified', 'stratified random', 'stratified haphazard', 'stratified systematic', 'NA', 'entire location'),
	 	'sampling_protocol' => array('drift net','dipnet', 'kicknet', 'gill net', 'fykenet', 'leaf pack', 'colonisation trap', 'electrofishing', 'pit-fall trapping',
	 	 'plankton net', 'emergence trap', 'butterfly net', 'Schindler-Patalas sampler', 'Bou-Rouch pump', 'vacuum pump', 'freeze-corer', 'Surber sampler',
	 	 'Hess sampler', 'seine net', 'three-pass depletion seine net', 'floating pan', 'NA'),
	 	'sampling_zone' => array('riparian zone', 'hyporheic zone', 'benthic zone', 'water column', 'groundwater', 'other'),
	 	'qualitative_sampling_depth' => array('surface', 'bottom', 'complete depth profile', 'NA'),
	 	'type_of_abundance_data' => array('density', 'arealrate', 'volumetric', 'volrate', 'count', 
	 		'biomass' , 'occurence' , 'controlled count$integer'));

	public static $lifeStageFaunaVocabulary = array('adult','larva','pupa','YOY');

	
	function __construct()
	{
		# code...
	}
}


?>