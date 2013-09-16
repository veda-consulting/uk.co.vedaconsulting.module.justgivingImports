{* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
{include file="CRM/common/WizardHeader.tpl"}
<div id="common-form-controls" class="form-item">
    <div id="common-form-controls" class="form-item">
        <table class="form-layout-compressed">
            {foreach from=$formObject->getUserElements() item=element}
            	<tr>
	                <td>{$element->getLabel()}</td>
	                <td>
		                {if $element->getName() == 'range_from' || $element->getName() == 'range_to'}
		                	{include file="CRM/common/jcalendar.tpl" elementName=$element->getName()}
		                {else}
		                	{$element->toHtml()}
		                {/if}
	                </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>
  
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>