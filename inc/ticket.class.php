<?php

/**
 * -------------------------------------------------------------------------
 * engage plugin for GLPI
 * Copyright (C) 2022 by the engage Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

class PluginEngageTicket extends CommonDBTM {

	static private $_instance = NULL;
	static $rightname         = 'ticket';


	static function canCreate() {
		return Session::haveRight('ticket', UPDATE);
	}


	static function canView() {
		return Session::haveRight('ticket', READ);
	}


	static function getTypeName($nb=0) {
		return __('Ticket');
	}


	function getName($with_comment=0) {
		return __('Ticket', 'ticket');
	}

	public static function createFollowup($item){
		$config	   = PluginEngageConfig::getInstance();

		$template 	   = new ITILFollowupTemplate();

		$requester 	   = $item->fields['users_id_lastupdater']; //items_id
		$current_status = $item->fields['status'];
		$ticket_id 	   = $item->fields['id'];

		$technician	   = $config->fields['users_id_tech'];
		$template_id	   = $config->fields['itil_followup'];

		$ticket_r = $_SESSION["glpiactiveprofile"]['ticket'];

		//impersonating as Technician
		$profile = new Profile();
		$profile->getFromDB($technician);

		$_SESSION["glpiID"] = $technician; 
		$_SESSION["glpiactiveprofile"]['ticket'] = $profile->fields['ticket'];
		Toolbox::logDebug(Session::getLoginUserID());

		$parents_itemtype = 'Ticket';
		$parent = new $parents_itemtype();
		$template->getFromDB($template_id);
		$parent->getFromDB($requester);

		$template->fields['content'] = $template->getRenderedContent($parent);

		$template->fields['requesttypes_name'] = "";
		if ($template->fields['requesttypes_id']) {
			$entityRestrict = getEntitiesRestrictCriteria(getTableForItemType(RequestType::getType()), "", $parent->fields['entities_id'], true);
			$requesttype = new RequestType();
			if (
				$requesttype->getFromDBByCrit([
				"id" => $template->fields['requesttypes_id'],
				] + $entityRestrict)
				) {
				$template->fields['requesttypes_name'] = Dropdown::getDropdownName(
					getTableForItemType(RequestType::getType()),
					$template->fields['requesttypes_id'],
					0,
					true,
					false,
					"(" . $template->fields['requesttypes_id'] . ")"
				);
			}
		}

		$f_up = new ITILFollowup();

		$followup_item = [
		'itemtype' => 'Ticket',
			'items_id' => $ticket_id,
			'content' => $template->fields['content'],
		'users_id' => $technician,
			'requesttypes_id' => $template->fields['requesttypes_id'],
			'is_private' => '0',
			'_fup_to_kb' => '0',
			'add' => '',
			'pending' => '0',
			'followup_frequency' => '0',
			'followups_before_resolution' => '0'
		];

		$followup_item = $f_up->prepareInputForAdd($followup_item);

		$f_up->add($followup_item);

		$ticket_user = new Ticket();
			$ticket_user->update(['id' => $ticket_id,
			'status'   => CommonITILObject::ASSIGNED,
			'_itil_assign' => ['_type' => 'user', 'users_id' => $technician],
			'type'       => '2' // ?? CommonITILActor::ASSIGN
		]);

		//end impersonating
		$_SESSION["glpiID"] = $requester; 
		$_SESSION["glpiactiveprofile"]['ticket'] = $ticket_r;
		Toolbox::logDebug(Session::getLoginUserID());
	}

}
