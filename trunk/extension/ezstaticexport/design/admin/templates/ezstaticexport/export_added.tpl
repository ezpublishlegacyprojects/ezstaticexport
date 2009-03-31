<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">
                                {if $export_schedule|eq('immediate')}
                                    {"Export created"|i18n('extension/ezstaticexport')}
                                {else}
                                    {"Schedule created"|i18n('extension/ezstaticexport')}
                                {/if}
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
                {if $export_schedule|eq('immediate')}
                    {"Your immediate export of the %export_type %node_name was successfully created. It will start in the next few minutes."|i18n('extension/ezstaticexport', '',
                    hash('%export_type', $export.type, '%node_name', $export.node.name))}
                {else}
                    {"Your scheduled export of the %export_type %node_name was successfully created."|i18n('extension/ezstaticexport', '',
                    hash('%export_type', $schedule.type, '%node_name', $schedule.node.name))}
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
