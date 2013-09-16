<?php

include_once 'ClientBase.php';
include_once 'Http/CurlWrapper.php';
include_once 'Model/RegisterPageRequest.php';
include_once 'Model/StoryUpdateRequest.php';

class PageApi extends ClientBase
{		
	public function Create($pageCreationRequest)
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/fundraising/pages";
		$url = $this->BuildUrl($locationFormat);
		$payload = json_encode($pageCreationRequest);
		$json = $this->curlWrapper->Put($url, $this->BuildAuthenticationValue(), $payload);
		return json_decode($json); 
	}
	
	public function IsShortNameRegistered($pageShortName)
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/fundraising/pages/" . $pageShortName;
		$url = $this->BuildUrl($locationFormat);		
		$httpInfo = $this->curlWrapper->Head($url, $this->BuildAuthenticationValue());		
		
		if($httpInfo['http_code'] == 200)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function ListAll()
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/fundraising/pages";
		$url = $this->BuildUrl($locationFormat);
		$json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json);
	}
	
	public function Retrieve($pageShortName)
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/fundraising/pages/" . $pageShortName;
		$url = $this->BuildUrl($locationFormat);
		$json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json);
	}
    
    public function RetrieveDonationsForPage($pageShortName, $pageSize=50, $pageNumber=1)
	{
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/fundraising/pages/".$pageShortName."/donations"."?PageSize=".$pageSize."&PageNum=".$pageNumber;
		$url = $this->BuildUrl($locationFormat);
		$json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
		return json_decode($json);
	}
	
	public function UpdateStory($pageShortName, $storyUpdate)
	{		
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/fundraising/pages/" . $pageShortName;
		$url = $this->BuildUrl($locationFormat);
		$storyUpdateRequest = new StoryUpdateRequest();
		$storyUpdateRequest->storySupplement = $storyUpdate;
		$payload = json_encode($storyUpdateRequest);		
		$httpInfo = $this->curlWrapper->Post($url, $this->BuildAuthenticationValue(), $payload);
		
		if($httpInfo['http_code'] == 200)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function UploadImage($pageShortName, $caption, $filename, $imageContentType)
	{            
		$fh = fopen($filename, 'r');
		$imageBytes = fread($fh, filesize($filename));
		fclose($fh);
	
		$locationFormat = $this->Parent->RootDomain . "{apiKey}/v{apiVersion}/fundraising/pages/" . $pageShortName . "/images?caption=" . urlencode ($caption);
		$url = $this->BuildUrl($locationFormat);
		$httpInfo = $this->curlWrapper->Post($url, $this->BuildAuthenticationValue(), $imageBytes, $imageContentType);
		
		if($httpInfo['http_code'] == 200)
		{
			return true;
		}
		else
		{
			return $httpInfo;
		}
	}
    
    /**
     * @author matusz
     * @param type $pageId
     * @param array $customCodes 
     */
    public function CreateCustomCodes($pageId, array $customCodes) {
        $locationFormat = $this->Parent->DataApiDomain . "{apiKey}/v{apiVersion}/pages/" . $pageId . "/customcodes";
        $url = $this->BuildUrl($locationFormat);
        
        $params = (object)$customCodes;
        $paramsJson = json_encode($params);
        $json = $this->curlWrapper->Put($url, $this->BuildAuthenticationValue(), $paramsJson);
        
        return json_decode($json);
    }
    
    /**
     * @author matusz
     * @param type $pageId
     */
    public function GetCustomCodes($pageId) {
        $locationFormat = $this->Parent->DataApiDomain . "{apiKey}/v{apiVersion}/pages/" . $pageId . "/customcodes";
        $url = $this->BuildUrl($locationFormat);
        
        $json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
        
        return json_decode($json);
    }
    
    /**
     * @author matusz
     * @param type $fromDate
     * @param type $toDate
     * @param type $searchParams
     * @return type 
     */
    public function RetrieveCreatedPagesReport($fromDate, $toDate, $searchParams = array())
	{
        //transform dates to format we need
        $from = new DateTime($fromDate);
        $fromDate = $from->format('Y-m-d');
        $to = new DateTime($toDate);
        $toDate = $to->format('Y-m-d');
        
        $locationFormat = $this->Parent->DataApiDomain . "{apiKey}/v{apiVersion}/pages/created/$fromDate;$toDate";
        if(count($searchParams) > 0) {
            $locationFormat .= '/search';
            $params = array();
            foreach($searchParams as $key => $val) {
                $params[] = "$key=" . urlencode($val);
            }
            
            $locationFormat .= '?' . implode('&', $params);
        }
        $url = $this->BuildUrl($locationFormat);
        $json = $this->curlWrapper->Get($url, $this->BuildAuthenticationValue());
        return json_decode($json);
	}
	
	
}
