{*
 Shows informations about the current node's configuration for static export:
  - export content type
*}

{if is_set( $module_result.content_info.node_id )}
{def $static_cache_info=fetch('ezstaticexport', 'node_info', hash( 'node_id', $module_result.content_info.node_id))}
{if $static_cache_info}

<div id="static_cache_info">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr">{section show=$first}<div class="box-tl"><div class="box-tr">{/section}

<h4>{'Static export'|i18n( 'extension/ezstaticexport' )}</h4>

</div></div></div></div>

{if $first}</div></div>{/if}

{if $last}
<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">
{else}
<div class="box-ml"><div class="box-mr"><div class="box-content">
{/if}

<div class="block">
{"Exported as %content_type"|i18n( 'extension/ezstaticexport', '', hash('%content_type', $static_cache_info.content_type ) )}<br />
{"Assigned as %flag_type"|i18n( 'extension/ezstaticexport', '', hash('%flag_type', $static_cache_info.flag_type ) )}<br />
</div>

</div></div></div>{if $last}</div></div></div>{/if}

</div>

{/if} {* if $static_cache_info *}
{/if} {* if is_set( $module_result.content_info.node_id ) *}
