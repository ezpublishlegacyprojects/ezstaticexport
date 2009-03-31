<script language="JavaScript1.2" type="text/javascript">
menuArray['eZStaticExport'] = new Array();
menuArray['eZStaticExport']['depth'] = 1; // this is a first level submenu of ContextMenu
menuArray['eZStaticExport']['elements'] = new Array();
</script>

 <hr/>
    <a id="child-menu-ezstaticexport" class="more" href="#" onmouseover="ezpopmenu_showSubLevel( event, 'eZStaticExport', 'child-menu-ezstaticexport' ); return false;">
        {'Static export'|i18n('extension/ezstaticexport')}
    </a>

<form id="menu-form-export" method="post" action={"/ezstaticexport/export"|ezurl}>
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="TargetServer" value="%targetServer%" />
  <input type="hidden" name="Schedule" value="%schedule%" />
  <input type="hidden" name="PrepareExportButton" value="1" />
</form>

<form id="menu-form-flag-content-type" method="post" action={"/ezstaticexport/flagcontenttype/"|ezurl}>
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="FlagType" value="%flagType%" />
  <input type="hidden" name="ContentType" value="%contentType%" />
  <input type="hidden" name="eZStaticExportFlagContentTypeButton" value="1" />
</form>

