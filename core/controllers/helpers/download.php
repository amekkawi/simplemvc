<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

class Download
{
	private $referer;
	private $cookiejar;
	private $useragent;
	private $overwrite;
	private $retry;
	private $maxretries;
	private $responseheaders;
	
	const FILE_EXISTS = 1;
	const NON_200_STATUS = 2;
	const INCOMPLETE_DOWNLOAD = 3;
	const CURL_ERROR = 4;
	const MAX_RETRIES = 5;
	
	/**
	 * Setup a downloader.
	 * @param array $arguments [optional]
	 * @return 
	 */
	function __construct($arguments = array()) {
		$this->referer = array_key_exists('referer', $arguments) ? $arguments['referer'] : NULL;
		$this->cookiejar = array_key_exists('cookiejar', $arguments) ? $arguments['cookiejar'] : NULL;
		$this->useragent = array_key_exists('useragent', $arguments) ? $arguments['useragent'] : NULL;
		$this->overwrite = array_key_exists('overwrite', $arguments) ? $arguments['overwrite'] === TRUE : FALSE;
		$this->retry = array_key_exists('retry', $arguments) ? $arguments['retry'] === TRUE : TRUE;
		$this->maxretries = array_key_exists('maxretries', $arguments) ? $arguments['maxretries'] : 5;
	}
	
	/**
	 * Download a file via a HTTP GET request.
	 * @param string $url
	 * @param string $out [optional] A file to output to. If specified, the output is not in the returned array.
	 * @param boolean $both [optional] Force the output to be in the returned array.
	 * @return array An associative array containing the result of the HTTP request.
	 */
	function get($url, $out = NULL, $both = FALSE) {
		$return = array(
			'success' => FALSE,
			'errorno' => 0,
			'message' => NULL,
			'url' => $url,
			'status' => NULL,
			'type' => NULL,
			'length' => NULL,
			'output' => '',
			'attempts' => 1
		);
		
		if (!is_null($out) && !$this->overwrite && file_exists($out)) {
			$return['errorno'] = self::FILE_EXISTS;
			$return['message'] = 'File already exists and override is not set';
		}
		else {
			$ch = curl_init($url);
			
			// Open the out file and pass it to curl.
			if (is_null($out) || $both) {
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			}
			
			if (!is_null($out) || $both) {
				$outfp = fopen($out, $this->overwrite ? "w" :"x");
				curl_setopt($ch, CURLOPT_FILE, $outfp);
			}
			
			curl_setopt_array($ch, array(
				CURLOPT_HEADERFUNCTION => array(&$this, '_HeaderHandler'),
				CURLOPT_FAILONERROR => true,
				CURLOPT_RESUME_FROM => 0,
				CURLOPT_BINARYTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_CONNECTTIMEOUT => 60,
				CURLOPT_TIMEOUT => 60
			));
			
			// Set options.
			if (!is_null($this->referer)) {
				curl_setopt($ch, CURLOPT_REFERER, $this->referer);
			}
			if (!is_null($this->cookiejar)) {
				curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
			}
			if (!is_null($this->useragent)) {
				curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
			}
			
			// Keep trying until the max attempts are reached.
			while (!$return['success'] && is_null($return['message']) && $return['attempts'] <= $this->maxretries) {
				
				$cresult = curl_exec($ch);
				
				if ($cresult !== FALSE) {
					
					if (is_null($out) || $both) {
						$return['output'] .= $cresult;
						$size = strlen($return['output']);
					}
					else {
						$size = ftell($outfp);
					}
					
					// Get response information.
					$info =  curl_getinfo($ch);
					
					// Always store the last status
					$return['status'] = $info['http_code'];
					
					// If not set already, store various response info.
					if (is_null($return['type'])) $return['type'] = $info['content_type'];
					if (is_null($return['length'])) $return['length'] = $info['download_content_length'];
					
					// If not a 200 or 206 response, return an error message.
					if ($info['http_code'] != 200 && $info['http_code'] != 206) {
						$return['errorno'] = self::NON_200_STATUS;
						$return['message'] = "Not 200 status: " . $info['http_code'];
					}
					
					// Retry the download if not completed.
					elseif ($info['download_content_length'] > 0 && $info['download_content_length'] > $info['size_download']) {
						if ($this->retry) {
							$return['attempts']++;
							curl_setopt($ch, CURLOPT_RESUME_FROM, $size);
						}
						else {
							$return['errorno'] = self::INCOMPLETE_DOWNLOAD;
							$return['message'] = "Failed to download entire file, and retry is not set";
						}
					}
					
					// Report success.
					else {
						$return['success'] = TRUE;
					}
				}
				
				// CURL error message
				else {
					$return['errorno'] = self::CURL_ERROR;
					$return['message'] = curl_error($ch);
				}
			}
			
			// Report max retries reached.
			if (!$return['success'] && $return['attempts'] > $this->maxretries) {
				$return['errorno'] = self::MAX_RETRIES;
				$return['message'] = "Maximum retries reached";
			}
			
			// Close the CURL and file.
			curl_close($ch);
			if (isset($outfp)) fclose($outfp);
		}
		
		return $return;
	}
	
	function _HeaderHandler($ch, $header) {
		//echo "Header(".strlen($header)."): $header";
		if (preg_match('/^((Content-Type)|(Content-Length)): (.+)$/', $header, $matches)) {
			$this->responseheaders[$matches[1]] = $matches[4];
		}
		return strlen($header);
	}
	
	// Gets and sets
	
	function getReferer() {
		$this->referer;
	}
	function setReferer($referer) {
		$this->referer = $referer;
	}
	function getCookiejar() {
		$this->cookiejar;
	}
	function setCookiejar($cookiejar) {
		$this->cookiejar = $cookiejar;
	}
	function getUseragent() {
		$this->useragent;
	}
	function setUseragent($useragent) {
		$this->useragent = $useragent;
	}
	function getOverwrite() {
		$this->overwrite;
	}
	function setOverwrite($overwrite) {
		$this->overwrite = $overwrite;
	}
	function getRetry() {
		$this->retry;
	}
	function setRetry($retry) {
		$this->retry = $retry;
	}
	function getMaxretries() {
		$this->maxretries;
	}
	function setMaxretries($maxretries) {
		$this->maxretries = $maxretries;
	}
}

?>