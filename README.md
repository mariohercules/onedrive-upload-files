# Create files from Oracle and upload to OneDrive

```php

<?php

require_once('classes/EsmFileUpload.php');

$time = $_GET["time"];

$sql = "SELECT SYSDATE, SYSDATE - 1 FROM DUAL";
	
$file = new EsmFileUpload(); // class instance
$file->createFileWithOracleAndSQL($sql,'FileToUploadToOneDrive.csv'); // parse SQL and create file on disk
$file->uploadToOneDrive('FileToUploadToOneDrive.csv'); // upload file to OneDrive

?>

```
