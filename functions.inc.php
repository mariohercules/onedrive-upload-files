<?php

/***************
* Author: Mario Hercules <mariohercules@>
* Date: 2016-02-01
*/


define("client_id", "CLIENT-ID");
define("client_secret", "SECRET");
define("callback_uri", "http://localhost/onedrive/redirect");
define("skydrive_base_url", "https://apis.live.net/v5.0/");
define("token_store", "onedrive/redirect/tokens"); // Edit path to your token store if required, see Wiki for more info.

class skydrive {

	public $access_token = '';

	public function __construct($passed_access_token) {
		$this->access_token = $passed_access_token;
	}

	public function get_folder($folderid, $sort_by='name', $sort_order='ascending', $limit='255', $offset='0') {
		if ($folderid === null) {
			$response = $this->curl_get(skydrive_base_url."me/skydrive/files?sort_by=".$sort_by."&sort_order=".$sort_order."&offset=".$offset."&limit=".$limit."&access_token=".$this->access_token);
		} else {
			$response = $this->curl_get(skydrive_base_url.$folderid."/files?sort_by=".$sort_by."&sort_order=".$sort_order."&offset=".$offset."&limit=".$limit."&access_token=".$this->access_token);
		}
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			$arraytoreturn = Array();
			$temparray = Array();
			if (@$response['paging']['next']) {
				parse_str($response['paging']['next'], $parseout);
				$numerical = array_values($parseout);
			}
			if (@$response['paging']['previous']) {
				parse_str($response['paging']['previous'], $parseout1);
				$numerical1 = array_values($parseout1);
			}
			foreach ($response as $subarray) {
				foreach ($subarray as $item) {
					if (@array_key_exists('id', $item)) {
						array_push($temparray, Array('name' => $item['name'], 'id' => $item['id'], 'type' => $item['type'], 'size' => $item['size'], 'source' => @$item['source']));
					}
				}
			}
			$arraytoreturn['data'] = $temparray;
			if (@$numerical[0]) {
				if (@$numerical1[0]) {
					$arraytoreturn['paging'] = Array('previousoffset' => $numerical1[0], 'nextoffset' => $numerical[0]);
				} else {
					$arraytoreturn['paging'] = Array('previousoffset' => 0, 'nextoffset' => $numerical[0]);
				}
			} else {
				$arraytoreturn['paging'] = Array('previousoffset' => 0, 'nextoffset' => 0);
			}
			return $arraytoreturn;
		}
	}

	function get_quota() {
		$response = $this->curl_get(skydrive_base_url."me/skydrive/quota?access_token=".$this->access_token);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			return $response;
		}
	}

	public function get_folder_properties($folderid) {
		$arraytoreturn = Array();
		if ($folderid === null) {
			$response = $this->curl_get(skydrive_base_url."/me/skydrive?access_token=".$this->access_token);
		} else {
			$response = $this->curl_get(skydrive_base_url.$folderid."?access_token=".$this->access_token);
		}

		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			@$arraytoreturn = Array('id' => $response['id'], 'name' => $response['name'], 'parent_id' => $response['parent_id'], 'size' => $response['size'], 'source' => $response['source'], 'created_time' => $response['created_time'], 'updated_time' => $response['updated_time'], 'link' => $response['link'], 'upload_location' => $response['upload_location'], 'is_embeddable' => $response['is_embeddable'], 'count' => $response['count']);
			return $arraytoreturn;
		}
	}

	public function get_file_properties($fileid) {
		$response = $this->curl_get(skydrive_base_url.$fileid."?access_token=".$this->access_token);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			$arraytoreturn = Array('id' => $response['id'], 'type' => $response['type'], 'name' => $response['name'], 'parent_id' => $response['parent_id'], 'size' => $response['size'], 'source' => $response['source'], 'created_time' => $response['created_time'], 'updated_time' => $response['updated_time'], 'link' => $response['link'], 'upload_location' => $response['upload_location'], 'is_embeddable' => $response['is_embeddable']);
			return $arraytoreturn;
		}
	}

	public function get_source_link($fileid) {
		$response = $this->get_file_properties($fileid);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			return $response['source'];
		}
	}

	function get_shared_read_link($fileid) {
		$response = curl_get(skydrive_base_url.$fileid."/shared_read_link?access_token=".$this->access_token);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			return $response['link'];
		}
	}

	function get_shared_edit_link($fileid) {
		$response = curl_get(skydrive_base_url.$fileid."/shared_edit_link?access_token=".$this->access_token);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			return $response['link'];
		}
	}

	function delete_object($fileid) {
		$response = curl_delete(skydrive_base_url.$fileid."?access_token=".$this->access_token);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			return true;
		}
	}

	public function download($fileid) {
		$props = $this->get_file_properties($fileid);
		$response = $this->curl_get(skydrive_base_url.$fileid."/content?access_token=".$this->access_token, "false", "HTTP/1.1 302 Found");
		$arraytoreturn = Array();
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			array_push($arraytoreturn, Array('properties' => $props, 'data' => $response));
			return $arraytoreturn;
		}
	}

	function put_file($folderid, $filename) {
		
		$r2s = skydrive_base_url.$folderid."/files/".basename($filename)."?access_token=".$this->access_token;
		
		if (filesize($filename) <= 1) {
			return "Filesize(0)";
		}
		
		$response = $this->curl_put($r2s, $filename);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			return $response;
		}

	}

	function put_file_from_url($sourceUrl, $folderId, $filename){
		$r2s = skydrive_base_url.$folderId."/files/".$filename."?access_token=".$this->access_token;

		$chunkSizeBytes = 1 * 1024 * 1024; //1MB

		$tempFilename = tempnam("/tmp", "UPLOAD");
		$temp = fopen($tempFilename, "w");

		$handle = @fopen($sourceUrl, "rb");
		if($handle === FALSE){
			throw new Exception("Unable to download file from " . $sourceUrl);
		}

		while (!feof($handle)) {
			$chunk = fread($handle, $chunkSizeBytes);
			fwrite($temp, $chunk);
		}

		fclose($handle);
		fclose($temp);

		$response = $this->curl_put($r2s, $tempFilename);
		if (@array_key_exists('error', $response)) {
			throw new Exception($response['error']." - ".$response['description']);
			exit;
		} else {
			unlink($tempFilename);
			return $response;
		}
	}

	function create_folder($folderid, $foldername, $description="") {
		if ($folderid===null) {
			$r2s = skydrive_base_url."me/skydrive";
		} else {
			$r2s = skydrive_base_url.$folderid;
		}
		$arraytosend = array('name' => $foldername, 'description' => $description);
		$response = $this->curl_post($r2s, $arraytosend, $this->access_token);
		if (@array_key_exists('error', $response)) {
				throw new Exception($response['error']." - ".$response['description']);
				exit;
			} else {
				$arraytoreturn = Array();
				array_push($arraytoreturn, Array('name' => $response['name'], 'id' => $response['id']));
				return $arraytoreturn;
			}
	}

	protected function curl_get($uri, $json_decode_output="true", $expected_status_code="HTTP/1.1 200 OK") {
		$output = "";
		$output = @file_get_contents($uri);
		if ($http_response_header[0] == $expected_status_code) {
			if ($json_decode_output == "true") {
				return json_decode($output, true);
			} else {
				return $output;
			}
		} else {
			return Array('error' => 'HTTP status code not expected - got ', 'description' => substr($http_response_header[0],9,3));
		}
	}

	protected function curl_post($uri, $inputarray, $access_token) {
		$trimmed = json_encode($inputarray);
		try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token,
		));
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $trimmed);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		} catch (Exception $e) {
		}
		if ($httpcode == "201") {
			return json_decode($output, true);
		} else {
			return array('error' => 'HTTP status code not expected - got ', 'description' => $httpcode);
		}
	}

	protected function curl_put($uri, $fp) {
	  $output = "";
	  try {
	  	$pointer = fopen($fp, 'r+');
	  	$stat = fstat($pointer);
	  	$pointersize = $stat['size'];
		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_INFILE, $pointer);
		curl_setopt($ch, CURLOPT_INFILESIZE, (int)$pointersize);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		/*
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);		
		*/
		
		/*
		LargeFiles
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);		
		*/
		
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	  } catch (Exception $e) {
	  }
	  	if ($httpcode == "200" || $httpcode == "201") {
	  		return json_decode($output, true);
	  	} else {
	  		return array('error' => 'HTTP status code not expected - got ', 'description' => $httpcode);
	  	}

	}

	protected function curl_delete($uri) {
	  $output = "";
	  try {
		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	  } catch (Exception $e) {
	  }
	  	if ($httpcode == "200") {
	  		return json_decode($output, true);
	  	} else {
	  		return array('error' => 'HTTP status code not expected - got ', 'description' => $httpcode);
	  	}
	}


}

class skydrive_auth {

	public static function build_oauth_url() {
		$response = "https://login.live.com/oauth20_authorize.srf?client_id=".client_id."&scope=wl.signin%20wl.offline_access%20wl.skydrive_update%20wl.basic&response_type=code&redirect_uri=".urlencode(callback_uri);
		return $response;
	}

	public static function get_oauth_token($auth) {
		$arraytoreturn = array();
		$output = "";
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://login.live.com/oauth20_token.srf");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded',
				));
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$data = "client_id=".client_id."&redirect_uri=".urlencode(callback_uri)."&client_secret=".urlencode(client_secret)."&code=".$auth."&grant_type=authorization_code";
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$output = curl_exec($ch);
			curl_close($ch);
		} catch (Exception $e) {
		}

		$out2 = json_decode($output, true);
		$arraytoreturn = Array('access_token' => $out2['access_token'], 'refresh_token' => $out2['refresh_token'], 'expires_in' => $out2['expires_in']);
		return $arraytoreturn;
	}

	public static function refresh_oauth_token($refresh) {
		$arraytoreturn = array();
		$output = "";
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://login.live.com/oauth20_token.srf");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded',
				));
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$data = "client_id=".client_id."&redirect_uri=".urlencode(callback_uri)."&client_secret=".urlencode(client_secret)."&refresh_token=".$refresh."&grant_type=refresh_token";
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$output = curl_exec($ch);
			curl_close($ch);
		} catch (Exception $e) {
		}

		$out2 = json_decode($output, true);
		$arraytoreturn = Array('access_token' => $out2['access_token'], 'refresh_token' => $out2['refresh_token'], 'expires_in' => $out2['expires_in']);
		return $arraytoreturn;
	}

}

class skydrive_tokenstore {

	public static function acquire_token() {

		$response = skydrive_tokenstore::get_tokens_from_store();
		if (empty($response['access_token'])) {	
			return false;
			exit;
		} else {
			if (time() > (int)$response['access_token_expires']) { 
				$refreshed = skydrive_auth::refresh_oauth_token($response['refresh_token']);
				if (skydrive_tokenstore::save_tokens_to_store($refreshed)) {
					$newtokens = skydrive_tokenstore::get_tokens_from_store();
					return $newtokens['access_token'];
				}
				exit;
			} else {
				return $response['access_token']; 
				exit;
			}
		}
	}

	public static function get_tokens_from_store() {
		$response = @json_decode(@file_get_contents(token_store), TRUE);
		return $response;
	}

	public static function save_tokens_to_store($tokens) {
		$tokentosave = Array();
		$tokentosave = Array('access_token' => $tokens['access_token'], 'refresh_token' => $tokens['refresh_token'], 'access_token_expires' => (time()+(int)$tokens['expires_in']));
		if (file_put_contents(token_store, json_encode($tokentosave))) {
			return true;
		} else {
			return false;
		}
	}

	public static function destroy_tokens_in_store() {
		if (file_put_contents(token_store, "loggedout")) {
			return true;
		} else {
			return false;
		}

	}
}

?>
