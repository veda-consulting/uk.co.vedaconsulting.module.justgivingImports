<?php
include_once 'ClientBase.php';
include_once 'Http/CurlWrapper.php';

class CharityApi extends ClientBase
{		
	
	public function Retrieve($charityId)
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/charity/" . $charityId;
		$url = $this->BuildUrl($locationFormat);
		$json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json); 
	}
}
