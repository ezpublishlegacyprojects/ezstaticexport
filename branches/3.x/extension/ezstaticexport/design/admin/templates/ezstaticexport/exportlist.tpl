{*
Shows a list of currently running exports.
Variables:
 - $exports, array of eZStaticExportExport
*}

<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">
                                {"Export list"|i18n('extension/ezstaticexport')}
                            </h1>
                            <div class="header-mainline" />
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
                {if not($exports|count)}
                    {"No exports are currently running"|i18n('extension/ezstaticexport')}
                {else}
                    <table class="list" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="tight">ID</th>
                                <th>Path string</th>
                                <th>Target</th>
                                <th>Type</th>
                                <th>{"SR"|i18n('extension/ezstaticexport', 'Short for Static resources')}*</th>
                                <th>Status</th>
                                <th>User</th>
                            </tr>
                        </thead>

                        <tbody>
                            {foreach $exports as $export sequence array('bglight', 'bgdark') as $trClass}
                            <tr class="{$trClass}">
                                <td>{$export.id}</td>
                                <td>{$export.path_string}</td>
                                <td>{$export.target}</td>
                                <td>{$export.type}</td>
                                <td>{if $export.static_resources|eq(1)}{"yes"|i18n('extension/ezstaticexport')}{else}{"no"|i18n('extension/ezstaticexport')}{/if}</td>
                                <td>{$export.status_string}</td>
                                <td>{$export.user.contentobject.name}</td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {/if}

                <p>*{"SR"|i18n('extension/ezstaticexport', 'Short for Static resources')} = {"Static resources"|i18n('extension/ezstaticexport')}</p>

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
                                {* BOTTOM CONTENT *}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
