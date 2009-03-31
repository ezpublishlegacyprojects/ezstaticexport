<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">
                                {'Scheduled exports'|i18n('extension/ezstaticexport')}
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
            {if $scheduledExports}
                <table border="0" cellspacing="0" class="list">
                    <tbody>
                        {foreach $scheduledExports as $scheduledExport}

                            {*def $nodeToExport = fetch( 'content', 'node',
                                                         hash( 'node_id', $scheduledExport.node_id ) )*}

                            <tr class="bglight">
                                <td>{'Date'|i18n('extension/ezstaticexport')}</td>
                                <td>{$scheduledExport.date|l10n('shortdatetime')}</td>
                            </tr>

                            <tr class="bgdark">
                                <td>{'NodeID to export'|i18n('extension/ezstaticexport')}</td>
                                <td><a href={concat( 'content/view/full/', $scheduledExport.node.url_alias )|ezurl('no')}>{$scheduledExport.node.name|wash()}</a></td>
                            </tr>

                            <tr>
                                <td>{'Recurrence'|i18n('extension/ezstaticexport')}</td>
                                <td>{$scheduledExport.recurrence}</td>
                            </tr>

                            <tr class="bglight">
                                <td>{'Subtree exported ?'|i18n('ezstaticexport')}</td>
                                <td>{$scheduledExport.export_subtree}</td>
                            </tr>

                            {delimiter}
                                <tr colspan="2">
                                    <td></td>
                                </tr>
                            {/delimiter}

                        {/foreach}
                    </tbody>
                </table>
            {else}
                {"There are no scheduled exports"|i18n('extension/ezstaticexport')}
            {/if}
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
