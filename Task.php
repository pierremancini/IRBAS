
/* 
* get user files
	**security check (comment ça ce traduit en pratique ?)
	**copy on local space for reading and archiving (@TODO begin or end of the insertion)
		***rule when file is bad or enter twice
	**ask for doublons treatment
		doublon = same dataset_name, creation_date, data_provider
		***1) erase all previously inserted data with the same metadata(@TODO delete publication and geographical entities that do not have any data associated with them)
		***3) erase nothing dbplus_update(a, old, new)
* parse file read line by line
	** check encodage, blank characters, case lower/upper
	** manage metadata : create a metadata object with all metadata as properties hardcoded
		*** check metada needed are presents (name, creation, provider)
			**** check data constraint and type
				***** Filtre 5: contact_email content must match with the pattern __@_._  (mutualisation between scripts)
				***** Filter 6: creation_date content must match with the pattern DD/MM/YYYY (mutualisation between scripts)
				***** Filter 8: availability content has to be 0 or 1. (mutualisation between scripts)
		*** check metadata are the same between the group of files
			**** show user where the problem is
		*** reeuse to check doublon (datasetname, creationdate, data_provider)	
	** manage header
		*** check fixed columns are present and order
		*** split to have data position
		***
			****(contenu)(à mettre dans script controller) Filter 1 : if '(' ')'-> the format is 'xxxx-xxxx' if '$' -> the format is xxxx-xxxx$frequency; frequency has controlled vocabulary
			****Filter 3 :  Columns qualifying a time period must preceed a timespan colomunm
			****Filter : test the order geographical entities, then test coordinates, then test measurements
			*** test the order measurement -> measurement_timespan 
	** manage data
		*** transaction début
		*** insert site in database
		*** all filters
		Ligne par lignes
			****Filter 11 : Check coordinate formats, convert it in the format 00*00*00*00.00E,W
			****Filter 12 : If flow_station_name or rainfall_station_name or temperature_station_name are filled then lat_station long_station must be filled too. 
			****Filter 13 : location_name must follow the naming convention : “site_name” followed by _”a number”. CL_GDo  -> CL_GDo_1
			****Filter that checks that a value is in a fork of values
		*** find the climate corresponding to the coordinates 
		*** fin transaction 
	**insertion in database
*advert user for progression
*see data (optionnal)

