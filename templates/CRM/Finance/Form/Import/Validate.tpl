{* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
{include file="CRM/common/WizardHeader.tpl"}

<div id="common-form-controls" class="form-item">
    {include file="CRM/Finance/Form/Import/importSummary.tpl"}
	{include file="CRM/Finance/Form/Import/validationSummary.tpl" onbrowseropen="cj('#_qf_Validate_ignoreerrors').parent().hide(); return true;"}

    {*foreach from=$errors item=error}
        {$error}
    {/foreach
    
    {if $errors}
        <a href="{$editorUrl}" target="_blank" onclick="cj('#_qf_Validate_ignoreerrors').parent().hide(); return true;">Open editor</a>
    {/if*}
</div>

{* <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div> *}
<div class="crm-submit-buttons">
{if $errors}
<span class="crm-button crm-button-type-next crm-button_qf_Validate_next"><input class="form-submit default validate" name="_qf_Validate_next" value="Re-Validate" type="submit" id="_qf_Validate_submit" /></span>
<span class="crm-button crm-button-type-next crm-button_qf_Validate_next" id="hideonbrowseropen"><input class="form-submit" name="_qf_Validate_ignoreerrors" value="Ignore Errors and Proceed" type="submit" id="_qf_Validate_ignoreerrors" /></span>
    {literal}
    <script>
    cj("#link-open-browser").click(function(event) {
        cj("#hideonbrowseropen").hide();
        return true;
    });
    </script>
    {/literal}
{else}
<span class="crm-button crm-button-type-next crm-button_qf_Validate_next"><input class="form-submit default validate" name="_qf_Validate_next" value="Validate" type="submit" id="_qf_Validate_submit" /></span>
{/if}
</div>