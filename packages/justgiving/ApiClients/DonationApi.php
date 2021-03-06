<?php
include_once 'ClientBase.php';
include_once 'Http/CurlWrapper.php';

class DonationApi extends ClientBase
{		
	public function Retrieve($donationId)
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/donation/" . $donationId;
		$url = $this->BuildUrl($locationFormat);
		$json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json); 
	}	
	
	public function RetrieveStatus($donationId)
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/donation/" . $donationId . "/status";
		$url = $this->BuildUrl($locationFormat);
		$json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json); 
	}
}
