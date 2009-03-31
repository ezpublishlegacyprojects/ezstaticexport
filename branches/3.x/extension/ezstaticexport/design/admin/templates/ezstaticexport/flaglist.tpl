<form name="FlagList" method="post" action={"ezstaticexport/flagcontenttype"|ezurl}>

<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">
                                {"Flags list"|i18n('extension/ezstaticexport')}
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
                <table class="list" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="tight"><img onClick="ezjs_toggleCheckboxes( document.FlagList, 'FlagIDArray[]' ); return false;" title="Invert selection." alt="Invert selection." src={"toggle-button-16x16.gif"|ezimage}/></th>
                            <th>{"Node"|i18n('extension/ezstaticexport/flaglist')}</th>
                            <th class="tight">{"Scope"|i18n('extension/ezstaticexport/flaglist')}</th>
                            <th class="tight">{"Content-type"|i18n('extension/ezstaticexport/flaglist')}</th>
                            <th class="tight">{"Nodes"|i18n('extension/ezstaticexport/flaglist')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $flags as $flag sequence array('bglight', 'bgdark') as $trClass}
                        <tr class="{$trClass}">
                            <td><input type="checkbox" name="FlagIDArray[]" value="{$flag.id}" /></td>
                            <td>{$flag.node.url_alias|wash}</td>
                            <td>{$flag.flag_type|wash}</td>
                            <td>{$flag.content_type|wash}</td>
                            <td>{$flag.nodes_count}</td>
                        </tr>
                        {/foreach}
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
                                <input type="submit" class="button" name="eZStaticExportRemoveContentTypeFlagButton" value="{"Remove selected"|i18n('extension/ezstaticexport/flaglist')}"  />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</form>
