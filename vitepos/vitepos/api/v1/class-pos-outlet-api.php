<?php
/**
 * Its api for outlet
 *
 * @since: 12/07/2021
 * @author: Sarwar Hasan
 * @version 1.0.0
 * @package VitePos\Api\V1
 */

namespace VitePos\Api\V1;

use Appsbd\V1\libs\API_Data_Response;
use VitePos\Libs\API_Base;
use Vitepos\Models\Database\Mapbd_Pos_Cash_Drawer;
use Vitepos\Models\Database\Mapbd_Pos_Cash_Drawer_Log;
use Vitepos\Models\Database\Mapbd_Pos_Cash_Drawer_Types;
use Vitepos\Models\Database\Mapbd_Pos_Counter;
use Vitepos\Models\Database\Mapbd_pos_warehouse;
use VitePos\Modules\POS_Settings;

/**
 * Class pos_outlet_api
 *
 * @package VitePos\Api\V1
 */
class Pos_Outlet_Api extends API_Base {

	/**
	 * The set api base is generated by appsbd
	 *
	 * @return mixed|string
	 */
	public function set_api_base() {
		return 'outlet';
	}

	/**
	 * The routes is generated by appsbd
	 *
	 * @return mixed|void
	 */
	public function routes() {
		$this->register_rest_route( 'GET', 'list', array( $this, 'outlet_list' ) );
		$this->register_rest_route( 'GET', 'all-outlet-list', array( $this, 'get_all_outlet' ) );
		$this->register_rest_route( 'GET', 'cash-drawer-info', array( $this, 'get_cash_drawer_info' ) );
		$this->register_rest_route( 'POST', 'cash-drawer-log', array( $this, 'cash_drawer_log' ) );
		$this->register_rest_route( 'GET', 'details/(?P<id>\d+)', array( $this, 'drawer_log_details' ) );

	}

	/**
	 * The outlet list is generated by appsbd
	 *
	 * @return API_Data_Response
	 */
	public function outlet_list() {
		$response_data          = new API_Data_Response();
		$user_id                = $this->get_current_user_id();
		$user                   = get_user_by( 'id', $user_id ? $user_id : 1 );
		$response_data->rowdata = Mapbd_pos_warehouse::get_outlet_details( $user );
		return $response_data;
	}

	/**
	 * The get all outlet is generated by appsbd
	 *
	 * @return API_Data_Response
	 */
	public function get_all_outlet() {
		$response_data          = new API_Data_Response();
		$response_data->rowdata = Mapbd_pos_warehouse::get_outlet_details( null, true );
		return $response_data;
	}

	/**
	 * The get cash deawer info is generated by appsbd
	 *
	 * @return API_Data_Response
	 */
	public function get_cash_drawer_info() {
		$cash_drawer = Mapbd_Pos_Cash_Drawer::get_by_counter( $this->get_outlet_id(), $this->get_counter_id(), $this->get_current_user_id() );
		$return_obj  = new \stdClass();
		$outlets     = Mapbd_pos_warehouse::fetch_all( 'id,name' );
		$counters    = Mapbd_Pos_Counter::fetch_all( 'id,name' );
		if ( ! empty( $cash_drawer ) ) {
			$counter_obj                 = $this->get_counter_obj();
			$return_obj->id              = $cash_drawer->id;
			$return_obj->outlet_id       = $cash_drawer->outlet_id;
			$return_obj->opening_balance = $cash_drawer->opening_balance;
			$return_obj->closing_balance = $cash_drawer->closing_balance;
			$return_obj->counter_id      = $cash_drawer->counter_id;
			$return_obj->counter_name    = ! empty( $counter_obj->name ) ? $counter_obj->name : '';
			$return_obj->opening_time    = $cash_drawer->opening_time;
			$return_obj->closing_time    = $cash_drawer->closing_time;
			$return_obj->status          = $cash_drawer->status;
			$return_obj->order_list      = Mapbd_Pos_Cash_Drawer_Types::get_order_list( $cash_drawer->id );
			$return_obj->order_summary   = Mapbd_Pos_Cash_Drawer_Types::get_order_summary( $cash_drawer->id );
			$current_date                = gmdate( 'Y-m-d 23:59:59' );
			$current_date                = esc_sql( $current_date );
			$last_date                   = gmdate( 'Y-m-d 00:00:00', strtotime( '7 days ago' ) );
			$last_date                   = esc_sql( $last_date );

			$drawer  = new Mapbd_Pos_Cash_Drawer();
			$user_id = $this->get_current_user_id();
			$drawer->opened_by( ' = ' . "$user_id" . ' OR closed_by= ' . "$user_id", true );
			$btwn = "BETWEEN '$last_date' and '$current_date'";
			$drawer->opening_time( " $btwn OR (closing_time $btwn)", true );
			$drawer_list = $drawer->select_all( '', 'opening_time', 'DESC' );
			$drawer_data = array();
			foreach ( $drawer_list as $data ) {
				$data->closed_by    = $this->get_user_name_by_id( $data->closed_by );
				$data->opened_by    = $this->get_user_name_by_id( $data->opened_by );
				$data->opening_time = appsbd_get_wp_datetime_with_format( $data->opening_time );
				$data->closing_time = appsbd_get_wp_datetime_with_format( $data->closing_time );
				foreach ( $outlets as $item ) {
					if ( $item->id == $data->outlet_id ) {
						$data->outlet = $item->name;
					}
				}
				foreach ( $counters as $item ) {
					if ( $item->id == $data->counter_id ) {
						$data->counter = $item->name;
					}
				}
				$drawer_data[] = $data;
			}
			$return_obj->drawer_list = $drawer_data;
			$this->response->set_response( true, '', $return_obj );
		} else {
			$this->add_error( 'No cash drawer info found' );
			$this->response->set_response( false );
		}
		return $this->response->get_response();
	}
	/**
	 * The get cash drawer log is generated by appsbd
	 *
	 * @return API_Data_Response
	 */
	public function cash_drawer_log() {
		$response_data = new API_Data_Response();
		if ( current_user_can( 'drawer-log' ) ) {
			$mainobj              = new Mapbd_Pos_Cash_Drawer();
			$response_data->limit = $this->get_payload( 'limit', 20 );
			$response_data->page  = $this->get_payload( 'page', 1 );
			$src_props            = $this->get_payload( 'src_by', array() );
			$srt_props            = $this->get_payload( 'src_by', array() );
			$mainobj->set_search_by_param( $src_props );
			$mainobj->set_sort_by_param( $srt_props );
			if ( ! POS_Settings::is_admin_user() && ! current_user_can( 'any-drawer-log' ) ) {
				$outlets = get_user_meta( $this->get_current_user_id(), 'outlet_id', true );
				if ( is_array( $outlets ) ) {
					if ( ! $mainobj->is_set_prperty( 'outlet_id' ) || ! in_array( $mainobj->outlet_id, $outlets ) ) {
						$outlet_in = "'" . implode( "','", $outlets ) . "'";
						$mainobj->outlet_id( "IN ($outlet_in)", true );
					}
				} else {
					$response_data->set_total_records( 0 );
					return $response_data;
				}
			}
			$order_by = 'opening_time';
			$order    = 'DESC';
			if ( ! empty( $this->payload['sort_by'][0] ) ) {
				$order_by = $this->payload['sort_by'][0]['prop'];
				$order    = $this->payload['sort_by'][0]['ord'];
			}

			if ( $response_data->set_total_records( $mainobj->count_all() ) ) {
				$outlets                = Mapbd_Pos_Warehouse::find_all_by_key_value( 'status', 'A', 'id', 'name' );
				$counters               = Mapbd_Pos_Counter::fetch_all_key_value( 'id', 'name' );
				$response_data->rowdata = $mainobj->select_all_grid_data( '', $order_by, $order, $response_data->limit, $response_data->limit_start() );
				foreach ( $response_data->rowdata as &$data ) {
					$data->closed_by    = $this->get_user_name_by_id( $data->closed_by );
					$data->opened_by    = $this->get_user_name_by_id( $data->opened_by );
					$data->opening_time = appsbd_get_wp_datetime_with_format( $data->opening_time );
					$data->closing_time = appsbd_get_wp_datetime_with_format( $data->closing_time );
					$data->outlet       = appsbd_get_text_by_key( $data->outlet_id, $outlets );
					$data->counter      = appsbd_get_text_by_key( $data->counter_id, $counters );
				}
			}

			$this->response->set_response( true, '', $response_data );
			return $this->response->get_response();
		} else {
			$this->response->set_response( false, 'No Access to drawer log', $response_data );
			return $this->response->get_response();
		}

	}

	/**
	 * The purchase details is generated by appsbd
	 *
	 * @param any $data Its string.
	 *
	 * @return \Appsbd\V1\libs\API_Response
	 */
	public function drawer_log_details( $data ) {
		if ( ! empty( $data['id'] ) ) {
			$id      = intval( $data['id'] );
			$log_obj = new Mapbd_Pos_Cash_Drawer_Log();
			$log_obj->cash_drawer_id( $id );
			$data = $log_obj->select_all_grid_data();
			if ( ! empty( $data ) ) {
				$this->set_response( true, '', $data );
				return $this->response->get_response();
			}
		}
		$this->set_response( false, 'data not found or invalid param' );
		return $this->response->get_response();
	}

	/**
	 * The get user name by id is generated by appsbd
	 *
	 * @param any $id Its id param.
	 *
	 * @return string
	 */
	public function get_user_name_by_id( $id ) {
		$user = get_user_by( 'id', intval( $id ) );
		return $user->first_name ? $user->first_name . ' ' . $user->last_name : $user->user_nicename;
	}
}
