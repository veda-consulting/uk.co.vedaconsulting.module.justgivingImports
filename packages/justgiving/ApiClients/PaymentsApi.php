<?php

include_once 'ClientBase.php';
include_once 'Http/CurlWrapper.php';
include_once 'Model/RegisterPageRequest.php';
include_once 'Model/StoryUpdateRequest.php';

class PaymentsApi extends ClientBase
{		
	
	public function ListAll($fromDate, $toDate)
	{
        $locationFormat = $this->Parent->DataApiDomain . "{apiKey}/v{apiVersion}/payments/$fromDate;$toDate";
		$url = $this->BuildUrl($locationFormat);
        $json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json); 
	}
	
	public function Retrieve($refNum)
	{
		$locationFormat = $this->Parent->DataApiDomain . "{apiKey}/v{apiVersion}/payments/" . $refNum;
		$url = $this->BuildUrl($locationFormat);
		$json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json);
	}	
}
