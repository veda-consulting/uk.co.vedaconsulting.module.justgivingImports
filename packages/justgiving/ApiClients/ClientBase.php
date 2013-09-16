<?php

class ClientBase
{		
	public $debug;
	public $Parent;
	public $curlWrapper;
	
	public function __construct(JustGivingClient $justGivingApi)
	{		
        $this->Parent		=	$justGivingApi;
        $this->debug = $justGivingApi->debug;
        $this->curlWrapper = $justGivingApi->curlWrapper;
	}
	
	public function BuildUrl($locationFormat)
	{	
		$url = $locationFormat;
		$url = str_replace("{apiKey}", $this->Parent->ApiKey, $url);
		$url = str_replace("{apiVersion}", $this->Parent->ApiVersion, $url);
		return $url;
	}
	
	public function BuildAuthenticationValue()
	{
		$stringForEnc = $this->Parent->Username.":".$this->Parent->Password;
		return base64_encode($stringForEnc);
	}
	
	public function WriteLine($string)
	{
		echo $string . "<br/>";
	}	
}
