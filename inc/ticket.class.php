<?php

/**
 * -------------------------------------------------------------------------
 * engage plugin for GLPI is a tool designed to facilitate user assignment 
 * and SLA compliance.
 * Copyright (C) 2022 by the engage Development Team.
 * -------------------------------------------------------------------------
 * 
 * LICENSE
 *
 * This file is part of Engage.
 *
 * Engage is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Engage is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Engage. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @package     Engage
 * @author      Miguel Angel Ruiz (miguelangelrtorresco@gmail.com)
 * @copyright   Copyright (C) 2022 by the engage plugin team.
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GPLv3+   
 * @link        https://github.com/miguelanruiz/engage
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

	private static function getEntityRestrictProfile($userID,$entityID){

		global $DB;

		$rightvector = [Ticket::ASSIGN, Ticket::STEAL];

		foreach($rightvector as $name){
			$query = $DB->request(
				[
					'SELECT'      => 'glpi_profilerights.profiles_id',
					'FROM'       => 'glpi_profilerights',
					'INNER JOIN' => [
						'glpi_profiles' => [
							'FKEY' => [
								'glpi_profilerights' => 'profiles_id',
								'glpi_profiles'      => 'id',
							]
						],
						'glpi_profiles_users' => [
							'FKEY' => [
								'glpi_profiles_users' => 'profiles_id',
								'glpi_profiles'       => 'id',
								[
									'AND' => ['glpi_profiles_users.users_id' => $userID],
								],
							]
						],
					],
					'WHERE'      => [
						'glpi_profilerights.name'   => Ticket::$rightname,
						'glpi_profilerights.rights' => ['&', $name],
					] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entityID, true),
				]
			);

			if($profiles = $query->current()){
				$profile = array_values($profiles)[0];
				$profile = ProfileRight::getProfileRights($profile,['ticket']);
				return $profile['ticket'];
			}
			
		}

	}

	public static function createFollowup($item){
		$config	   	    = PluginEngageConfig::getConfigForEntity($item->getEntityID());

		$template 	    = new ITILFollowupTemplate();

		$requester 	    = $item->fields['users_id_lastupdater']; //items_id
		$current_status = $item->fields['status'];
		$ticket_id 	    = $item->fields['id'];

		$technician	    = $config->fields['users_id_tech'];
		$template_id	= $config->fields['itil_followup'];

		if (
			($technician == 0) 
			|| ($template_id == 0)
			|| !Profile::haveUserRight($technician,Ticket::$rightname,Ticket::ASSIGN,$item->getEntityID())
			|| !Profile::haveUserRight($technician,Ticket::$rightname,Ticket::STEAL,$item->getEntityID())
			|| !$config-fields['is_active']){
			//couldn't do something with null information
			return;
		}

		$ticket_r = $_SESSION["glpiactiveprofile"]['ticket'];

		//impersonating as Technician
		$right = self::getEntityRestrictProfile($technician,$item->getEntityID());

		$_SESSION["glpiID"] 					 = $technician; 
		$_SESSION["glpiactiveprofile"]['ticket'] = $right;
		//Toolbox::logDebug(Session::getLoginUserID());

		$parents_itemtype = 'Ticket';
		$parent 		  = new $parents_itemtype();
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
			'itemtype' 	=> 'Ticket',
			'items_id' 	=> $ticket_id,
			'content' 	=> $template->fields['content'],
			'users_id' 	=> $technician,
			'requesttypes_id' => $template->fields['requesttypes_id'],
			'is_private' 	  => '0',
			'_fup_to_kb' 	  => '0',
			'add' 	  => '',
			'pending' => '0',
			'followup_frequency' 		  => '0',
			'followups_before_resolution' => '0'
		];

		$followup_item = $f_up->prepareInputForAdd($followup_item);

		$f_up->add($followup_item);

		$ticket_user   = new Ticket();
			$ticket_user->update(['id' => $ticket_id,
			'status'   		=> CommonITILObject::ASSIGNED,
			'_itil_assign'  => ['_type' => 'user', 'users_id' => $technician]
		]);

		//end impersonating
		$_SESSION["glpiID"] 				     = $requester; 
		$_SESSION["glpiactiveprofile"]['ticket'] = $ticket_r;
		//Toolbox::logDebug(Session::getLoginUserID());
	}

}
