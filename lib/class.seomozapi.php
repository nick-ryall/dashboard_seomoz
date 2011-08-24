<?php

class SEOMozAPI {

	public $access_id = false;
	public  $secret_key = false;

	function __construct( $access_id, $secret_key ) {
		$this->access_id = $access_id;
		$this->secret_key = $secret_key;
	}

  function SEOMozAPI( $access_id, $secret_key ) {
  	$this->__construct( $access_id, $secret_key );
	}

	/**
	 * generate_signature - Builds the signature var needed to authenticate
	 *
	 * @param int $timestamp
	 * @returns string URL encoded Signature key/value pair
	*/
	function generate_signature( $timestamp ) {
		$timestamp = isset($timestamp) ? $timestamp : time() + 300; // one minute into the future
		$hash = hash_hmac( 'sha1', $this->access_id . "\n" . $timestamp, $this->secret_key, true );
		return urlencode( base64_encode( $hash ) );
	}

	/**
	 * query - Queries the SEOMoz API
	 *
	 * @param string $apiName
	 * @param string $target_url
	 * @returns mixed URL contents on success, false on failure
	*/
	function query( $api_call, $argument ) {
		$timestamp = mktime() + 300; // 5 minutes into the future
		$argument = urlencode( $argument );
		$request_url = "http://lsapi.seomoz.com/linkscape/{$api_call}/{$argument}?AccessID={$this->access_id}&Expires={$timestamp}&Signature=" . $this->generate_signature( $timestamp );
		
		
		$data = apc_fetch('seomoz_cache');
		if ( false == $data) {
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $request_url); 
			curl_setopt($ch, CURLOPT_HEADER, FALSE); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$data = curl_exec($ch); 
			curl_close($ch);
			apc_store('seomoz_cache', $data);
		}
		return $data;
	}

	/**
	 * mozRank - Returns the SEOMoz 'mozRank' for the given URL.
	 *
	 * @param string $target_url
	 * @param bool $raw
	 * @returns mixed if $raw == true: returns "raw rank" (float in exponential notation)
	 *                   if $raw == false: returns "pretty rank" (float between 0 and 10 inclusive)
	*/
	function urlmetrics( $target_url ) {
		$response = $this->query( 'url-metrics', $target_url );
		return $response;
	}
}
