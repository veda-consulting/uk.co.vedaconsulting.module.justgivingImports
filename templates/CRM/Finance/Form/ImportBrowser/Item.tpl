<div id="common-form-controls" class="form-item">
    <div id="common-form-controls" class="form-item">
        <table class="form-layout-compressed">
            {include file="CRM/Contact/Form/NewContact.tpl"}
            {foreach from=$formElementNames item=el name=fields}
            	{if ($smarty.foreach.fields.iteration % 2) != 0}<tr>{/if}
	                <td>{$form.$el.label}</td>
	                <td>{$form.$el.html}</td>
                {if ($smarty.foreach.fields.iteration % 2) == 0}</tr>{/if}
            {/foreach}
        </table>
    </div>
</div>

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>