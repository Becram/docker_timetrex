<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2018 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/


/**
 * @package Modules\Policy
 */
class OverTimePolicyListFactory extends OverTimePolicyFactory implements IteratorAggregate {

	/**
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAll( $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getById( $id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'id' => TTUUID::castUUID($id),
						);

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc', 'trigger_time' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						company_id = ?
						AND id in ('. $this->getListSQL( $id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getByCompanyId( $id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.type_id' => 'asc', 'a.name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pgf = new PolicyGroupFactory();
		$cgmf = new CompanyGenericMapFactory();
		$spf = new SchedulePolicyFactory();

		$ph = array(
					'id' => TTUUID::castUUID($id),
					);


		$query = '
					select	a.*,
							(
								( select count(*) from '. $cgmf->getTable() .' as w, '. $pgf->getTable() .' as v where w.company_id = a.company_id AND w.object_type_id = 110 AND w.map_id = a.id AND w.object_id = v.id AND v.deleted = 0)+
								( select count(*) from '. $spf->getTable() .' as z where z.over_time_policy_id = a.id and z.deleted = 0)
							) as assigned_policy_groups
					from	'. $this->getTable() .' as a
					where	a.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id UUID
	 * @param int $limit Limit the number of records returned
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getByCompanyIdAndPayCodeId( $company_id, $id, $limit = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						company_id = ?
						AND pay_code_id in ('. $this->getListSQL( $id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $pay_code_id UUID
	 * @param string $pay_formula_policy_id UUID
	 * @param int $limit Limit the number of records returned
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RegularTimePolicyListFactory
	 */
	function getByCompanyIdAndPayCodeIdAndPayFormulaPolicyId( $company_id, $pay_code_id, $pay_formula_policy_id, $limit = NULL, $where = NULL, $order = NULL) {
		if ( $pay_code_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
				'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						company_id = ?
						AND pay_code_id in ('. $this->getListSQL( $pay_code_id, $ph, 'uuid' ) .')
						AND pay_formula_policy_id in ('. $this->getListSQL( $pay_formula_policy_id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $pay_formula_policy_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getByCompanyIdAndPayFormulaPolicyId( $id, $pay_formula_policy_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $pay_formula_policy_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					);


		$query = '
					select	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND pay_formula_policy_id in ('. $this->getListSQL( $pay_formula_policy_id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $contributing_shift_policy_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getByCompanyIdAndContributingShiftPolicyId( $id, $contributing_shift_policy_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $contributing_shift_policy_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					);


		$query = '
					select	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND contributing_shift_policy_id in ('. $this->getListSQL( $contributing_shift_policy_id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getByPolicyGroupUserId( $user_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'd.type_id' => 'asc', 'd.trigger_time' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					);

		$query = '
					select	d.*
					from	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $cgmf->getTable() .' as c,
							'. $this->getTable() .' as d
					where	a.policy_group_id = b.id
						AND ( b.id = c.object_id AND b.company_id = c.company_id AND c.object_type_id = 110 )
						AND c.map_id = d.id
						AND a.user_id = ?
						AND ( b.deleted = 0 AND d.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getByPolicyGroupUserIdOrId( $user_id, $id = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			$id = TTUUID::getZeroID();
		}

		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc', 'trigger_time' => 'desc', 'is_policy_group' => 'asc', 'id' => 'asc' ); //Order by is_policy_group last so policies coming from the policy group are returned last and therefore override duplicate policies from the schedule policy.
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();
		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					);

		$query = '
					select	d.*,
							1 as is_policy_group
					from	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $cgmf->getTable() .' as c,
							'. $this->getTable() .' as d
					where	a.policy_group_id = b.id
						AND ( b.id = c.object_id AND b.company_id = c.company_id AND c.object_type_id = 110 )
						AND c.map_id = d.id
						AND a.user_id = ?
						AND ( b.deleted = 0 AND d.deleted = 0 )
					UNION
					select	e.*,
							0 as is_policy_group
					from
							'. $otpf->getTable() .' as e
					where
							e.id in ('. $this->getListSQL( $id, $ph, 'uuid' ) .')
							AND e.deleted = 0
						';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OverTimePolicyListFactory
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('type_id', 'in_use');

		$sort_column_aliases = array(
									'type' => 'type_id',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by type
			if ( !isset($order['type_id']) ) {
				$order['type_id'] = 'asc';
			}
			//Always sort by name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$pgf = new PolicyGroupFactory();
		$cgmf = new CompanyGenericMapFactory();
		$spf = new SchedulePolicyFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					);

		$query = '
					select	
							_ADODB_COUNT
							a.*,
							(
								CASE WHEN EXISTS ( select 1 from '. $cgmf->getTable() .' as w, '. $pgf->getTable() .' as v where w.company_id = a.company_id AND w.object_type_id = 110 AND w.map_id = a.id AND w.object_id = v.id AND v.deleted = 0 )
									THEN 1
									ELSE
										CASE WHEN EXISTS ( select 1 from '. $spf->getTable() .' as z where z.over_time_policy_id = a.id and z.deleted = 0 )
										THEN 1 ELSE 0
										END
								END
							) as in_use,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
							_ADODB_COUNT
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : NULL;

		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['description']) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['contributing_shift_policy_id']) ) ? $this->getWhereClauseSQL( 'a.contributing_shift_policy_id', $filter_data['contributing_shift_policy_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_code_id']) ) ? $this->getWhereClauseSQL( 'a.pay_code_id', $filter_data['pay_code_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_formula_policy_id']) ) ? $this->getWhereClauseSQL( 'a.pay_formula_policy_id', $filter_data['pay_formula_policy_id'], 'uuid_list', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param bool $include_blank
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @return array|bool
	 */
	function getByCompanyIdArray( $company_id, $include_blank = TRUE, $where = NULL) {

		$otplf = new OverTimePolicyListFactory();
		$otplf->getByCompanyId($company_id, $where);

		$list = array();
		if ( $include_blank == TRUE ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ($otplf as $otp_obj) {
			$list[$otp_obj->getID()] = $otp_obj->getName();
		}

		if ( empty($list) == FALSE ) {
			return $list;
		}

		return FALSE;
	}

}
?>
