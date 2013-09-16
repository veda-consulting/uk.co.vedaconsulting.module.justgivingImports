{if $validationSummary}
    {*<table style="width: 300px">
      <thead><tr><td colspan="2"><b>Import summary</b></td></tr></thead>
      <tbody>
        <tr><td>Total Import Value</td><td>{$validationSummary.total_import_net_amount|crmMoney}</td></tr>
        </tbody>
    </table>*}
    <table style="width: 300px">
      <thead><tr><td colspan="2"><b>Validation summary</b></td></tr></thead>
      <tbody>
        {if $validationSummary.duplicates}<tr><td style="width: 150px">Possible duplicates</td><td class="crm-error">{foreach name=foo from=$validationSummary.duplicates item=duplicate}{if !$smarty.foreach.foo.first}, {/if}<a href="/civicrm/finance/import/browser?id={$duplicate}&reset=1&readonly=1" target="_blank">{$duplicate}</a>{/foreach}</td></tr>{/if}
        <tr><td style="width: 150px">Errors</td><td {if $validationSummary.total_error}class="crm-error"{/if}>{$validationSummary.total_error}</td></tr>
        <tr><td>Valid Transactions</td><td>{$validationSummary.total_valid}</td></tr>
        {*<tr><td>Total Value - Gross</td><td>{$validationSummary.total_gross_amount|crmMoney}</td></tr>*}
        <tr><td>Total Valid Value</td><td>{$validationSummary.total_net_amount|crmMoney}</td></tr>
        {*<tr><td>Validation OK</td><td>{$validationSummary.validate_ok}</td></tr>
        <tr><td>Validation errors</td><td>{$validationSummary.validate_error}</td></tr>
        <tr><td>Skipped and valid</td><td>{$validationSummary.skipped_valid}</td></tr>
        <tr><td>Skipped with error</td><td>{$validationSummary.skipped_error}</td></tr>*}
        {if !$nobrowserlink}<tr><td></td><td><a href="/civicrm/finance/import/browser?id={$importId}&reset=1{if $readonly}&readonly=1{/if}" target="_blank" id="link-open-browser">Open import browser</a></td></tr>{/if}
        </tbody>
    </table>
    {/if}