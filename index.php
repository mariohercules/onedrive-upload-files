<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Esmaltec S/A OneDrive Upload</title>
</head>

<body>

<?php

/*
ID do Cliente:
000000004C181586 Este é um identificador exclusivo para o aplicativo.
Segredo do cliente (v1):

4930so4MUdaHhJb4-cwsSJVK2pQJqRQR  Por motivos de segurança, não compartilhe o segredo do cliente com ninguém.

code	Mf1374908-9ae5-ef5c-aef3-45d6d892a906
		M93771501-9e95-4509-8ad4-8ed4157a68aa

*/

?>

<?php

define("path_store" ,'/onedrive/');
define("default_dir",'folder.3a99703216a5f73d.3A99703216A5F73D!103');

session_start();

require_once 'functions.inc.php';
$token = skydrive_tokenstore::acquire_token(); // Call this function to grab a current access_token, or false if none is available.

if (!$token) { // If no token, prompt to login. Call skydrive_auth::build_oauth_url() to get the redirect URL.
	 
    echo "<div>";
    echo "<img src='statics/key-icon.png'>&nbsp";
    echo "<a href='" . skydrive_auth::build_oauth_url() . "'>Login with SkyDrive</a></span>";
    echo "</div>";
	echo "<script>		
		window.location.assign(\"<?php echo skydrive_auth::build_oauth_url();?>\");
	</script>";
	
} else {
    $sd = new skydrive($token);
    try {

        $response = $sd->put_file(default_dir, path_store.'myfile.csv');
        // File was uploaded, return metadata.

        //$response = $sd->get_folder(default_dir, $sort_by, $sort_order, $limit, $offset);;


        print_r($response);
    } catch (Exception $e) {
        // An error occured, print HTTP status code and description.
        echo "Error: ".$e->getMessage();
        exit;
    }
}

?>

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

