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

{if $payments}
<div>
	<table>
		<thead>
			<tr><td style="width: 10px;">&nbsp;</td><td>Reference</td><td>Date</td><td>Amount</td></tr>
		</thead>
		<tbody>
		{foreach from=$payments item=item}
			<tr><td><input type="radio" name="payment_id" value="{$item.id}"></td><td>{$item.id}</td><td>{$item.date}</td><td>{$item.net}</td></tr>			
		{/foreach}
		</tbody>
	</table>
</div>
{else}
No records loaded.
{/if}

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>

