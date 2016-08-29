{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<input class="widgetData" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(\includes\utils\Json::encode($DATA))}' />
{if count($DATA) gt 0 }
	<div class="widgetChartContainer" style="height:98%;width:100%"></div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
{/strip}
