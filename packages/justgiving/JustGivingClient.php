<?php

include_once 'ApiClients/PageApi.php';
include_once 'ApiClients/AccountApi.php';
include_once 'ApiClients/CharityApi.php';
include_once 'ApiClients/DonationApi.php';
include_once 'ApiClients/SearchApi.php';
include_once 'ApiClients/EventApi.php';
include_once 'ApiClients/TeamApi.php';

include_once 'ApiClients/PaymentsApi.php';

include_once 'ApiException.php';
include_once 'BadRequestException.php';
include_once 'ConflictException.php';
include_once 'ForbiddenException.php';
include_once 'NotAuthorizedException.php';
include_once 'NotFoundException.php';

include_once 'ApiClients/TestApi.php';


class JustGivingClient
{   
    const ENV_PRODUCTION = 'production';
    const ENV_STAGING = 'staging';
    
    const DOMAIN_API_STAGING = 'https://api.staging.justgiving.com/';
    const DOMAIN_API_PRODUCTION = 'TODO';
    const DOMAIN_DATAAPI_STAGING = 'https://staging-dataapi.justgiving.com/';
    const DOMAIN_DATAAPI_PRODUCTION = 'https://dataapi.justgiving.com/';
    
    public $ApiKey;
    public $ApiVersion;
    public $Username;
    public $Password;
    public $RootDomain;
    public $DataApiDomain;
    
    public $Page;
    public $Account;
    public $Charity;
    public $Donation;
    public $Search;
    public $Event;
    public $Team;
    
    public $Payments;
    
    public $Test;

    public function __construct($env, $apiKey, $apiVersion, $username="", $password="")
    {
        switch($env) {
            case self::ENV_PRODUCTION:
                $this->RootDomain       = self::DOMAIN_API_PRODUCTION;
                $this->DataApiDomain    = self::DOMAIN_DATAAPI_PRODUCTION;
                break;
            case self::ENV_STAGING:
                $this->RootDomain       = self::DOMAIN_API_STAGING;
                $this->DataApiDomain    = self::DOMAIN_DATAAPI_STAGING;
                break;
            default:
                throw new Exception("No environment '$env' defined");
        }
        
        $this->ApiKey           = (string) $apiKey;
        $this->ApiVersion       = (string) $apiVersion;
        $this->Username         = (string) $username;
        $this->Password         = (string) $password;
        $this->curlWrapper      = new CurlWrapper($this);
        $this->debug            = false;
        
        // Init API clients
        $this->Page             = new PageApi($this);
        $this->Account          = new AccountApi($this);
        $this->Charity          = new CharityApi($this);
        $this->Donation         = new DonationApi($this);
        $this->Search           = new SearchApi($this);
        $this->Event            = new EventApi($this);
        $this->Team             = new TeamApi($this);
        
        $this->Payments         = new PaymentsApi($this);
        
        //matusz: TODO testing
        $this->Test             = new TestApi($this);
    }
    
    /**
     * This needs some better parsing.
     * E.g. is timezone always defined using + symbol?
     * How to use timezone info properly?
     * @param string $dateStr
     * @return DateTime
     */
    public static function parseDate($dateStr, $outFormat = null) {
        $ret = substr($dateStr, strpos($dateStr, '(') + 1, -2);
        list($milis, $timezone) = explode('+', $ret);
        $date = DateTime::createFromFormat('U O', ($milis / 1000) . ' +' . $timezone);
        if($date === false) {
            return null; 
        }
        
        if($outFormat !== null) {
            return $date->format($outFormat);
        }
        
        return $date;
    }
}