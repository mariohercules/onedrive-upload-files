<?php
 
require_once "../functions.inc.php";
$response = skydrive_auth::get_oauth_token($_GET['code']);
if (skydrive_tokenstore::save_tokens_to_store($response)) {
	header("Location: ../index.php");
} else {
	echo "error";
}	
	
?>

