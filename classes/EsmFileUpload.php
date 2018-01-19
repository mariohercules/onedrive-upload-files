<?php

putenv("ORACLE_HOME=/u01/app/oracle/product/9i_32bit");

class EsmFileUpload {
	

	public function __construct() {
		
	}


	public function createFileWithOracleAndSQL ($SQL, $fileName, $directory = "") {
	
		$ipBanco = "ORACLE-HOST"; /* Variável referente ao endreço IP do banco de dados */
		$usuarioBanco = "ORACLE-USER"; /* Variável referente ao usuário do banco de dados */
		$senhaBanco = "ORACLE-PASS"; /* Variável referente à senha do banco */
		$conexao = null; /*  Variável referente ao objeto de conexão com o banco de dados */
		$conexao = ocilogon($usuarioBanco, $senhaBanco, $ipBanco); 

		$hasData = false;
	
		//$sql = $SQL;
		 //echo $SQL;
		$statement = ociparse($conexao,$SQL);
		ociexecute($statement);
			
		$ncols = oci_num_fields($statement);
		$header = array();
			
		for ($i = 1; $i <= $ncols; ++$i) {
			$colname .= '"' . oci_field_name($statement, $i) . '",';
		}		
	
		$colname = substr($colname,0,strlen($colname)-1);
		
		$header[] = $colname;
			
		while (($row = oci_fetch_array($statement, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
			 
			 $field = "";
		
			 foreach ($row as $item) {

			 	  $hasData = true;

				  $field .= '"' . $item .'",';

			 }
				 
			 $header[] = substr($field,0,strlen($field)-1);
		  }		
		
		if ($hasData) {

			$file = fopen($fileName,"w");
			
			foreach ($header as $line)
			{
				fputcsv($file,explode(',',$line),",",' ');
			}
			
			fclose($file);
			
			$file = NULL;
			$header = NULL;		
		
		}

	}
	
	public function createFileWithOracleAndSQLAndNumericFields ($SQL, $fileName, $directory = "") {
	
		$ipBanco = "ORACLE-HOST"; /* Variável referente ao endreço IP do banco de dados */
		$usuarioBanco = "ORACLE-USER"; /* Variável referente ao usuário do banco de dados */
		$senhaBanco = "ORACLE-PASS"; /* Variável referente à senha do banco */
		$conexao = null; /*  Variável referente ao objeto de conexão com o banco de dados */
		$conexao = ocilogon($usuarioBanco, $senhaBanco, $ipBanco); 
		
		$hasData = false;

		//$sql = $SQL;
		 //echo $SQL;
		$statement = ociparse($conexao,$SQL);
		ociexecute($statement);
			
		$ncols = oci_num_fields($statement);
		$header = array();
			
		for ($i = 1; $i <= $ncols; ++$i) {
			$colname .= '"' . oci_field_name($statement, $i) . '",';
		}		
	
		$colname = substr($colname,0,strlen($colname)-1);
		
		$header[] = $colname;		

		while (($row = oci_fetch_array($statement, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
			 
			 $field = "";
		
			 foreach ($row as $item) {

			 	$hasData = true;

			 	if (is_numeric($item)) {
				  $field .=  $item .',';
			 	}
			 	else {
			 	  $field .= '"' . $item .'",';	
			 	}
			 }
				 
			 $header[] = substr($field,0,strlen($field)-1);
		  }		
		
		if ($hasData) {

			$file = fopen($fileName,"w");
			
			foreach ($header as $line)
			{
				fputcsv($file,explode(',',$line),",",' ');
			}
			
			fclose($file);
			
			$file = NULL;
			$header = NULL;		
		
		}

	}
	
	public function uploadToOneDrive($fileName) {
		
		define("path_store" ,'onedrive/');
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
					window.location.assign(\"".skydrive_auth::build_oauth_url()."\");
				</script>";		
					
		} else {
			$sd = new skydrive($token);
			try {
		
				$response = $sd->put_file(default_dir, path_store.$fileName);
				// File was uploaded, return metadata.
			} catch (Exception $e) {
				// An error occured, print HTTP status code and description.
				echo "Error: ".$e->getMessage();
				//exit;
			}
		}
	}
	
}



?>