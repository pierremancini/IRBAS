<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
	<HEAD>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
			<TITLE>Insertion of DataSet</TITLE>
			<script type="text/javascript" src="insertion.js"></script>
	</HEAD>
	<BODY>


		<h2>Insert .csv files</h2>
		<form action="parser_csv.php?>" method="post" enctype="multipart/form-data">
			<h5>Site file</h5>
			<input id="entree_site_dataset" name="entree_site_dataset" type="file" onchange="checkSiteFile(this.id);">
			<br/>
			<h5>Environment file</h5>
			<input id="entree_environment_dataset" name="entree_environment_dataset" type="file" onchange="">
			<br/>
			<h5>Fauna file</h5>
			<input id="entree_fauna_dataset" name="entree_fauna_dataset" type="file" onchange="">
			<br/><br/>
			<h5>Please select the option that applies to the files you are inserting:</h5>
			<input type="radio" name="insertion_rule" value="update" checked="checked">These files are NEW<br/>

(You have not previously inserted files with the same data-set name as these new files)<br/>
Note: selecting this option will create duplicate data entries for your sites and samples if you have previously entered files with the same dataset name<br/>			
			<br/>
			<input type="radio" name="insertion_rule" value="erase">At least one of these files is an UPDATED VERSION of a previously inserted file<br/>
 
(At least one of these files contains corrected and/or additional data/samples than the previously entered file with the same dataset name)<br/>
Note: selecting this option will delete all previously inserted data associated with the same dataset name as these files;<br/>
 the data in these files will overwrite any previously inserted data that were associated with the same dataset name)<br/>

			<br/><br/>
			Submit : <input id="submit" type="submit" value="OK" disabled="true" onclick="alertSiteFile();" >

		</form>

		<?php

		?>

	</BODY>
</HTML>