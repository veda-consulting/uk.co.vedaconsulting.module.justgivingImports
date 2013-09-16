<div class="crm-block crm-form-block crm-export-form-block">

<table class="form-layout-compressed">
    <tr><td class="label">{$form.batch_title.label}</td><td>{$form.batch_title.html}</td><tr>
    <tr><td class="label">{$form.description.label}</td><td>{$form.description.html}</td></tr>
     <tr><td class="label">{$form.banking_account.label}</td><td>{$form.banking_account.html}</td></tr>
    <tr>
       <td class="label">{$form.banking_date.label}</td>
       <td>{include file="CRM/common/jcalendar.tpl" elementName=banking_date}</td>
    </tr>
    <tr><td class="label">{$form.exclude_from_posting.label}</td><td>{$form.exclude_from_posting.html}</td></tr>
    <tr><td class="label">{$form.contribution_type_id.label}</td><td>{$form.contribution_type_id.html}</td></tr>
    <tr><td class="label">{$form.payment_instrument_id.label}</td><td>{$form.payment_instrument_id.html}</td></tr>
</table>

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="top"}
</div>



