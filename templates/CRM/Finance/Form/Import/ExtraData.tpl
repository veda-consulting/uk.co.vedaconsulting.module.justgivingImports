{include file="CRM/common/WizardHeader.tpl"}

<div id="common-form-controls" class="form-item">
    <div id="common-form-controls" class="form-item">
    
        {include file="CRM/Finance/Form/Import/importSummary.tpl"}
        {include file="CRM/Finance/Form/Import/validationSummary.tpl" readonly="1"}
        {include file="CRM/Finance/Form/Import/Batch.tpl" readonly="1"}
        
    </div>
</div>
  
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>

<script type="text/javascript" src="{$config->resourceBase}js/rest.js"></script>{literal}
<script type="text/javascript">
{/literal}{if $config->cleanURL eq 0}{literal}
var campaignUrl = '{/literal}{$config->userFrameworkBaseURL}{literal}index.php?q=civicrm/ajax/rest&className=CRM_Batch_Page_AJAX&fnName=getCampaignList&json=1&limit=25';
{/literal}{else}{literal}
var campaignUrl = '{/literal}{$config->userFrameworkBaseURL}{literal}civicrm/ajax/rest?className=CRM_Batch_Page_AJAX&fnName=getCampaignList&json=1&limit=25';
{/literal}{/if}{literal}
var contactElement = '#campaign_name';
var contactHiddenElement = 'input[name=campaign_id]';
cj( contactElement ).autocomplete( campaignUrl, { 
    selectFirst : false, matchContains: true, minChars: 1
}).result( function(event, data, formatted) {
    cj( contactHiddenElement ).val(data[1]);
});

</script>
{/literal}