<?php
class CurlWrapper
{
    private $client;
    
    public function __construct(JustGivingClient $client)
	{
		if (!function_exists('curl_init'))
		{ 
			die('CURL is not installed!');
		}
        
        $this->client = $client;
	}
	
    protected function isDebug() {
        return $this->client->debug;
    }
    
	public function Get($url, $base64Credentials = "")
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        
		$this->SetCredentials($ch, $base64Credentials);
		
        $buffer = curl_exec($ch);
        list($headers, $body) = explode("\r\n\r\n", $buffer);
        
		$info = curl_getinfo($ch);
        curl_close($ch);
        
        $this->handleError($info, $headers, $body);
        
        return $body;
	}
	
	/**
	 * TODO more to be implemented
	 * https://api.justgiving.com/docs/usage#errors
	 * 
	 * @param unknown_type $info
	 * @param unknown_type $buffer
	 * @throws JustGiving_NotAuthorizedException
	 * @throws JustGiving_ApiException
	 */
	private function handleError($info, $headers, $body) {
		$code = $info['http_code'];
		$headerCode = substr($headers, 13, strpos($headers, "\r\n") - 13);
        
        if($this->isDebug()) {
            //var_dump($info);
            echo "<b>{$info['url']}</b><br/>";
            echo '<b>Request header:</b><br/>';
            echo "<pre>{$info['request_header']}</pre>";
            echo '<b>Response header:</b><br/>';
            //var_dump($headers);
            echo "<pre>$headers</pre>";
            echo '<b>Response Body (JSON decode)</b><br/><pre>';
            $x = json_decode($body);
            var_dump($x);
            echo "</pre>";
            echo '<b>Response Body (Raw)</b><br/><pre>';
            var_dump($body);
            echo "</pre>";
            exit;
        }

        switch($code) {
			case 400:
				throw new JustGiving_BadRequestException($headerCode, $code);
        		break;
			case 401:
        		throw new JustGiving_NotAuthorizedException($headerCode, $code);
        		break;
        	case 403:
                $errs = json_decode($body);
                throw new JustGiving_ForbiddenException($headerCode, $code, null, $errs);
        		break;
        	case 404:
        		throw new JustGiving_NotFoundException($headerCode, $code, null);
        		break;
        	case 409:
        		throw new JustGiving_ConflictException($headerCode, $code);
        		break;
        	case 500:
        		//this should work with XML also - check $info for content type
        		$errs = json_decode($body);
        		throw new JustGiving_ApiException($headerCode, $code, null, $errs);
        		break;
        }
	}
	
	/**
	 * TODO implement handleError
	 * 
	 * @param unknown_type $url
	 * @param unknown_type $base64Credentials
	 * @param unknown_type $payload
	 */
	public function Put($url, $base64Credentials, $payload)
	{	
		$fh = fopen('php://memory', 'rw+');
		fwrite($fh, $payload);
		rewind($fh);
	
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_PUT, true);
        
		curl_setopt($ch, CURLOPT_INFILE, $fh);
        //was causing apache (winxp) to freeze up.
		//curl_setopt($ch, CURLOPT_INFILESIZE, strlen($payload));
		
        $this->SetCredentials($ch, $base64Credentials);
		
        $buffer = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
        
        $parts = explode("\r\n\r\n", $buffer);
        if(count($parts) == 1) {
            $headers = current($parts);
        }
        else {
            $body = array_pop($parts);
            $headers = array_pop($parts);
        }
        
        if($this->isDebug()) {
            echo "PUT Data: $payload<br/>";
        }
        $this->handleError($info, $headers, $body);
        
		return $buffer;
	}
	
	/**
	 * TODO implement handleError
	 *
	 * @param unknown_type $url
	 * @param unknown_type $base64Credentials
	 */
	public function Head($url, $base64Credentials = "")
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		
		$this->SetCredentials($ch, $base64Credentials);
		
		$buffer = curl_exec($ch);
		$info = curl_getinfo($ch);		
		curl_close($ch);
		
		return $info;
	}	
	
	/**
	 * TODO implement handleError
	 * 
	 * @param unknown_type $url
	 * @param unknown_type $base64Credentials
	 * @param unknown_type $payload
	 * @param unknown_type $contentType
	 */
	public function Post($url, $base64Credentials = "", $payload, $contentType="application/json")
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		
		$this->SetCredentials($ch, $base64Credentials, $contentType);
		
		$buffer = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $info;
	}
	
	private function SetCredentials($ch, $base64Credentials = "", $contentType="application/json")
	{		
		if($base64Credentials != null && $base64Credentials != "")
		{			
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: '.$contentType, 'Authorize: Basic '.$base64Credentials, 'Authorization: Basic '.$base64Credentials ));
		}
		else
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: '.$contentType));
		}
	}
}
