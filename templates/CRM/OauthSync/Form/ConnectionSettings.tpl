<!-- Based on https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/templates/CRM/Civixero/Form/XeroSettings.tpl -->
{* HEADER *}

<p>
    Please configure your Connection's Client ID and Secret. You will usually find these in the developer console for the
    product.
</p>
<p>
    The redirect url that CiviCRM will use is TODO
</p>


{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

{foreach from=$elementNames item=elementName}
    <div class="crm-section">
        <div class="label">{$form.$elementName.label}</div>
        <div class="content">{$form.$elementName.html}</div>
        <div class="clear"></div>
    </div>
{/foreach}

{* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT)
  <div>
    <span>{$form.favorite_color.label}</span>
    <span>{$form.favorite_color.html}</span>
  </div>
{* FOOTER *}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>