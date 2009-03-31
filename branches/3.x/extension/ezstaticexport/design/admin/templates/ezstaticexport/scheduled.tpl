<script language="javascript" src="{'scripts/calendar.js'|ezdesign('no')}"></script>

<h4>{'Choose a date'|i18n('extension/ezstaticexport')}</h4>

<p>
    <input type="text" name="ScheduledDate" value="{if is_set( $export_date )}{$export_date}{else}dd/mm/yyyy{/if}" onfocus="this.select();lcs(this)" onclick="event.cancelBubble=true;this.select();lcs(this)">

    {'Hour'|i18n('extension/ezstaticexport')}
    <select name="ScheduledHour">
        {for 0 to 23 as $hour}
            <option value="{$hour}"{if and( is_set( $export_hour ), eq( $export_hour, $hour ) )} selected="selected"{/if}>{$hour}</option>
        {/for}
    </select>

    {def $quarters = array( 0, 15, 30, 45 )}
    {'Minutes'|i18n('extension/ezstaticexport')}
    <select name="ScheduledMinute">
        {foreach $quarters as $quarter}
            <option value="{$quarter}"{if and( is_set( $export_minute ), eq( $export_minute, $quarter ) )} selected="selected"{/if}>{$quarter}</option>
        {/foreach}
    </select>
</p>
<p>&nbsp;</p>
<h4>{'Recurrence'|i18n('extension/ezstaticexport')}</h4>
<p>
    <input type="radio" name="ScheduledRecurrence" value="none"    {if and( is_set( $export_recurrence ), eq( $export_recurrence, 'none' ) )} checked="checked"{/if}/>{'None'|i18n('extension/ezstaticexport')}
    <input type="radio" name="ScheduledRecurrence" value="hourly"  {if and( is_set( $export_recurrence ), eq( $export_recurrence, 'hourly' ) )} checked="checked"{/if}/>{'Hourly'|i18n('extension/ezstaticexport')}
    <input type="radio" name="ScheduledRecurrence" value="daily"   {if and( is_set( $export_recurrence ), eq( $export_recurrence, 'daily' ) )} checked="checked"{/if}/>{'Daily'|i18n('extension/ezstaticexport')}
    <input type="radio" name="ScheduledRecurrence" value="weekly"  {if and( is_set( $export_recurrence ), eq( $export_recurrence, 'weekly' ) )} checked="checked"{/if}/>{'Weekly'|i18n('extension/ezstaticexport')}
    <input type="radio" name="ScheduledRecurrence" value="monthly" {if and( is_set( $export_recurrence ), eq( $export_recurrence, 'monthly' ) )} checked="checked"{/if}/>{'Monthly'|i18n('extension/ezstaticexport')}
</p>

<p>&nbsp;</p>
