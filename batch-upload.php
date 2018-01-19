<?php

require_once('classes/EsmFileUpload.php');

$time = $_GET["time"];


$sql = "
	  SELECT SYSDATE, SYSDATE - 1 FROM DUAL
	";
	
$file = new EsmFileUpload();
$file->createFileWithOracleAndSQL($sql,'FileToUploadToOneDrive.csv');
$file->uploadToOneDrive('FileToUploadToOneDrive.csv');
		
		
function executeNow() {

	$objDateTime = new DateTime('now');

	if ($objDateTime->format('H') >= '23' and $objDateTime->format('i') <= '59') {
		return true;
	} else {
		return false;
	}

}


?>

<html>
<body>
</body>
</html>


<script>

function sleepTimer()
{
    setTimeout(continueExecution, 55000) //wait ten seconds before continuing
}

function continueExecution()
{
   top.open('','_self',''); top.close();
}

sleepTimer();

</script>


