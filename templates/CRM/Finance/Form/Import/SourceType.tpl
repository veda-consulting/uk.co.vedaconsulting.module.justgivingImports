{* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
{include file="CRM/common/WizardHeader.tpl"}
<div id="common-form-controls" class="form-item">
    <table class="form-layout-compressed">
        <tr class="">
             <td class="label">{$form.source.label}</td>
             <td>{$form.source.html} {help id='sourceTypes'}</td>
        </tr>
    </table>
</div>
  
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>