<style type="text/css">{literal}
table.list tr.warning {
    background-color: orange;
}
table.list tr.error {
    background-color: red;
}{/literal}
</style>

<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">{"Static export"|i18n('extension/ezstaticexport')}</h1>
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
                <table class="list">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    {foreach $log as $logItem sequence array('bglight', 'bgdark') as $trClass}
                        <tr class="{$trClass}{if $logItem.status|eq(1)} error{elseif $logItem.status|eq(2)} warning{/if}">
                            <td>{$logItem.date|l10n('shortdatetime')}</td>
                            <td>{$logItem.message|shorten(100)}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
            <div class="context-toolbar">
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
                                    <input type="submit" class="button" value="{"Download log file"|i18n('extension/ezstaticexport')}"  />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
