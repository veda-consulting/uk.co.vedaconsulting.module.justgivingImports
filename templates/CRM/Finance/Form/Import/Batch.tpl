{if $importSummary}
    <table class="form-layout-compressed">
        {foreach from=$formObject->getUserElements() item=element}
        <tr>
        <td>{$element->getLabel()}</td>
        <td>
            {if $element->getName() == 'banking_date' ||
            $element->getName() == 'expected_posting_date' ||
            $element->getName() == 'received_date'}
                {include file="CRM/common/jcalendar.tpl" elementName=$element->getName()}
            {else}
            {$element->toHtml()}
            {/if}
        </td>
        </tr>
        {/foreach}
    </table>
{/if}