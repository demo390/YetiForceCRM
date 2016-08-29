{*<!--
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
-->*}
{strip}
	<div class="pull-right actions">
		<span class="actionImages">
			{if $RELATED_MODULE->isPermitted('WatchingRecords') && $RELATED_RECORD->isViewable()}
				{assign var=WATCHING_STATE value=(!$RELATED_RECORD->isWatchingRecord())|intval}
				<a href="#" onclick="Vtiger_Index_Js.changeWatching(this)" title="{vtranslate('BTN_WATCHING_RECORD', $MODULE)}" data-record="{$RELATED_RECORD->getId()}" data-value="{$WATCHING_STATE}" class="noLinkBtn{if !$WATCHING_STATE} info-color{/if}" data-on="info-color" data-off="" data-icon-on="glyphicon-eye-open" data-icon-off="glyphicon-eye-close" data-module="{$RELATED_MODULE_NAME}">
					<span class="glyphicon {if $WATCHING_STATE}glyphicon-eye-close{else}glyphicon-eye-open{/if} alignMiddle"></span>
				</a>&nbsp;
			{/if}
			{if $RELATED_MODULE_NAME eq 'Calendar'}
				{assign var=CURRENT_ACTIVITY_LABELS value=Calendar_Module_Model::getComponentActivityStateLabel('current')}
				{if $IS_EDITABLE && in_array($RELATED_RECORD->get('activitystatus'),$CURRENT_ACTIVITY_LABELS)}
					<a class="showModal" data-url="{$RELATED_RECORD->getActivityStateModalUrl()}">
						<span title="{vtranslate('LBL_SET_RECORD_STATUS', $MODULE)}" class="glyphicon glyphicon-ok alignMiddle"></span>
					</a>&nbsp;
				{/if}
				{if $RELATED_RECORD->isViewable()}
					<a href="{$RELATED_RECORD->getFullDetailViewUrl()}">
						<span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span>
					</a>&nbsp;
				{/if}
			{else}
				<a href="{$RELATED_RECORD->getFullDetailViewUrl()}">
					<span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span>
				</a>&nbsp;
			{/if}
			{if $IS_EDITABLE}
				{if $RELATED_MODULE_NAME eq 'PriceBooks'}
					<a data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}"
					   class="editListPrice cursorPointer" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}>
						<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></span>
					</a>&nbsp;
				{elseif $RELATED_MODULE_NAME eq 'Calendar'}
					{if $RELATED_RECORD->isEditable()}
						<a href='{$RELATED_RECORD->getEditViewUrl()}'>
							<span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span>
						</a>&nbsp;
					{/if}
				{else}
					<a href='{$RELATED_RECORD->getEditViewUrl()}'>
						<span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span>
					</a>&nbsp;
				{/if}
			{/if}
			{if ($IS_EDITABLE && $RELATED_RECORD->isEditable() && $RELATED_RECORD->editFieldByModalPermission()) || $RELATED_RECORD->editFieldByModalPermission(true)}
				{assign var=FIELD_BY_EDIT_DATA value=$RELATED_RECORD->getFieldToEditByModal()}
				<a class="showModal {$FIELD_BY_EDIT_DATA['listViewClass']}" data-url="{$RELATED_RECORD->getEditFieldByModalUrl()}">
					<span title="{vtranslate({$FIELD_BY_EDIT_DATA['titleTag']}, $MODULE)}" class="glyphicon {$FIELD_BY_EDIT_DATA['iconClass']} alignMiddle"></span>
				</a>&nbsp;
			{/if}
			{if $IS_DELETABLE}
				<a class="relationDelete">
					<span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span>
				</a>
			{/if}
		</span>
	</div>
{/strip}

