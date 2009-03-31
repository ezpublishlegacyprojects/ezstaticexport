{*
Export creation form

Assigned variables:
  $export_target:   name of the export target
  $export_type:     subtree or node
  $export_schedule: scheduled or immediate. If the value is scheduled, the schedule part of the form is displayed
  $export_date:     for scheduled exports only, yyyymmdd
  $export_hour:     for scheduled exports only, hh
  $export_minute:   for scheduled exports only, mm
  $export_node_id:  exported NodeID
  $errors:          array of error messages when for gets submitted
*}
<form name="exportForm" method="post" action={'ezstaticexport/export'|ezurl}>

<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">
                                {if $export_schedule|eq('immediate')}
                                    {if $export_target|eq('DefaultTargetServer')}
                                        {'Immediate export to default target'|i18n('extension/ezstaticexport')}
                                    {else}
                                        {'Immediate export to another target'|i18n('extension/ezstaticexport')}
                                    {/if}
                                {else}
                                    {'Scheduled export'|i18n('extension/ezstaticexport')}
                                {/if}
                            </h1>
                            <div class="header-mainline"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="box-ml">
    <div class="box-mr">
        <div class="box-content">
            <div class="block">

                {if is_set($errors)}
                    {foreach $errors as $error}
                        <p style="color:red">{$error}</p>
                    {/foreach}
                {/if}

                  <input type="hidden" name="Schedule" value="{$export_schedule}" />
                  <input type="hidden" name="NodeID" value="{$export_node_id}" />

                  {if $export_schedule|eq( 'scheduled' )}
                    {include uri='design:ezstaticexport/scheduled.tpl'}
                  {/if}

                <h4>{'Choose Target'|i18n('extension/ezstaticexport')}</h4>

                <table border="0" cellspacing="0" class="list">
                    <thead>
                        <th class="tight">&nbsp;</th>
                        <th colspan="2">{'Target Name'|i18n('extension/ezstaticexport')}</th>
                    </thead>
                    <tbody>
                        {* FETCHING DEFAULT TARGET *}
                        <tr>
                            <td><input type="radio" name="TargetServer" value="DefaultTargetServer"{if and( is_set( $export_target ), eq( $export_target, 'DefaultTargetServer' ) )} checked="checked"{/if}/></td>
                            <td><strong>Default target server</strong></td>
                            <td>
                                <ul>
                                    <li>Name :     {ezini( 'DefaultTargetServerSettings', 'TargetServerName'    , 'staticexport.ini' )}</li>
                                    <li>URL :      {ezini( 'DefaultTargetServerSettings', 'TargetServerURL'     , 'staticexport.ini' )}</li>
                                </ul>
                            </td>
                        </tr>

                        {* FETCHING OTHER TARGETS *}
                        {def $targetServers = ezini( 'TargetServerList', 'TargetServer', 'staticexport.ini')}
                        {foreach $targetServers as $targetServer sequence array( 'bgdark', 'bglight' ) as $style}
                            {def $targetServerConfigurationGroup = concat( 'TargetServer-', $targetServer )}
                            <tr class="{$style}">
                                <td><input type="radio" name="TargetServer" value="{$targetServer}"{if and( is_set( $export_target ), eq( $export_target, $targetServer ) )} checked="checked"{/if}/></td>
                                <td>{$targetServer}</td>
                                <td>
                                    <ul>
                                        <li>Name :     {ezini( $targetServerConfigurationGroup, 'TargetServerName'    , 'staticexport.ini' )}</li>
                                        <li>URL :      {ezini( $targetServerConfigurationGroup, 'TargetServerURL'     , 'staticexport.ini' )}</li>
                                    </ul>
                                </td>
                            </tr>
                            {undef $targetServerConfigurationGroup}
                        {/foreach}
                    </tbody>
                </table>

                  <p>&nbsp;</p>

                  <table border="0" cellspacing="0" class="list">
                      <thead>
                          <th class="tight">&nbsp;</th>
                          <th>{'Export type'|i18n('extension/ezstaticexport')}</th>
                      </thead>
                      <tbody>
                          <tr class="bglight">
                              <td><input type="radio" name="Type" id="ExportTypeSubtree" value="subtree"{if and( is_set( $export_type ), eq( $export_type, 'subtree' ) )} checked="checked"{/if}/></td>
                              <td><label for="ExportTypeSubtree">{'Subtree'|i18n('extension/ezstaticexport')}</label></td>
                          </tr>
                          <tr class="bgdark">
                              <td><input type="radio" name="Type" id="ExportTypeNode" value="node"{if and( is_set( $export_type ), eq( $export_type, 'node' ) )} checked="checked"{/if}/></td>
                              <td><label for="ExportTypeNode">{'Node'|i18n('extension/ezstaticexport')}</label></td>
                          </tr>
                      </tbody>
                  </table>

                  <table border="0" cellspacing="0" class="list">
                      <thead>
                          <th class="tight">&nbsp;</th>
                          <th>{'Static resources'|i18n('extension/ezstaticexport')}</th>
                      </thead>
                      <tbody>
                          <tr class="bglight">
                              <td><input type="checkbox" name="StaticResources" id="StaticResources" {if and( is_set( $static_resources ), eq( $static_resources, 1 ) )}checked="checked" {/if}/></td>
                              <td><label for="StaticResources">{'Export static resources as well'|i18n('extension/ezstaticexport')}</label></td>
                          </tr>
                      </tbody>
                  </table>

            </div>
            <div class="context-toolbar" />
        </div>
    </div>
</div>

<div class="controlbar">
    <div class="box-bc">
        <div class="box-ml">
            <div class="box-mr">
                <div class="box-tc">
                    <div class="box-bl">
                        <div class="box-br">
                            <div class="block">
                                <form method="post" action={$logDownloadUrl|ezurl}>
                                    <input type="submit" name="DoExportButton" value="OK" class="button" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</form>
