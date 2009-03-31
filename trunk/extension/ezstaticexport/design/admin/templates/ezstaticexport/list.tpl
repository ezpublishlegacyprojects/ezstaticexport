<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">{'Static exports'|i18n('extension/ezstaticexport')}</h1>
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
                            <th class="tight">{"ID"|i18n('extension/ezstaticexport')}</th>
                            <th>{"Path string"|i18n('extension/ezstaticexport')}</th>
                            <th>{"Target"|i18n('extension/ezstaticexport')}</th>
                            <th>{"Type"|i18n('extension/ezstaticexport')}</th>
                            <th>{"SR"|i18n('extension/ezstaticexport', 'Short for Static resources')}*</th>
                            <th>{"Schedule"|i18n('extension/ezstaticexport')}</th>
                            <th>{"Status"|i18n('extension/ezstaticexport')}</th>
                            <th>{"User"|i18n('extension/ezstaticexport')}</th>
                            <th>{"Log"|i18n('extension/ezstaticexport')}</th>
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
                            <td>{$export.schedule_type}</td>
                            <td>{$export.status_string}</td>
                            <td>{$export.user.contentobject.name}</td>
                            <td><a href={concat('ezstaticexport/logs/', $export.id)|ezurl}>{"View log"|i18n('extension/ezstaticexport')}</a></td>
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
<div class="controlbar"><div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br"></div></div></div></div></div></div></div>