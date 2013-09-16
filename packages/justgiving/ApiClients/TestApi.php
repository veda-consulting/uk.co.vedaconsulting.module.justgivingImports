<?php

include_once 'ClientBase.php';
include_once 'Http/CurlWrapper.php';
include_once 'Model/RegisterPageRequest.php';
include_once 'Model/StoryUpdateRequest.php';

class TestApi extends ClientBase
{		
	public function ListAll()
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/pages/created/2010-01-10;2010-12-31";
		$url = $this->BuildUrl($locationFormat);
        $json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
        return json_decode($json);
	}
    
}
