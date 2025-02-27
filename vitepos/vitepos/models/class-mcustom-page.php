<?php
/**
 * Pos Warehouse Database Model
 *
 * @package Vitepos\Models\Database
 */

namespace Vitepos\Models;

use VitePos\Core\VitePos;

/**
 * Class Mcustom_Page
 *
 * @package Vitepos\Models
 */
class Mcustom_Page {
	/**
	 * Its property _db_key
	 *
	 * @var string
	 */
	protected static $_db_key = '_vt_cus_pg';

	/**
	 * The valid data is generated by appsbd
	 *
	 * @param mixed $data Its data.
	 *
	 * @return bool
	 */
	public static function valid_data( &$data ) {
		$data = (object) $data;
		if ( ! empty( $data->custom_props ) && is_array( $data->custom_props ) ) {
			$data->custom_props = (object) $data->custom_props;
		}
		$is_ok = true;
		if ( ! isset( $data->custom_props->pg_width ) ) {
			$is_ok = false;
			VitePos::add_error( 'Page width is required' );
		}
		if ( ! isset( $data->custom_props->pg_pd_tb ) ) {
			$is_ok = false;
			VitePos::add_error( 'Page padding top,bottom can not be empty' );
		}
		if ( ! isset( $data->custom_props->cn_pd_tb ) ) {
			$is_ok = false;
			VitePos::add_error( 'Container margin top,bottom can not be empty' );
		}
		if ( ! isset( $data->custom_props->pg_pd_se ) ) {
			$is_ok = false;
			VitePos::add_error( 'Page padding left,right can not be empty' );
		}
		if ( ! isset( $data->custom_props->cn_pd_se ) ) {
			$is_ok = false;
			VitePos::add_error( 'Container margin top,bottom can not be empty' );
		}
		if ( ! isset( $data->custom_props->cn_width ) ) {
			$is_ok = false;
			VitePos::add_error( 'Barcode width is required' );
		}
		if ( ! isset( $data->custom_props->font_size ) ) {
			$is_ok = false;
			VitePos::add_error( 'Font size is required' );
		}
		if ( isset( $data->custom_props->pg_width ) && ! is_numeric( $data->custom_props->pg_width ) ) {
			$is_ok = false;
			VitePos::add_error( 'Page width must be numeric' );
		}
		if ( isset( $data->custom_props->pg_pd_tb ) && ! is_numeric( $data->custom_props->pg_pd_tb ) ) {
			$is_ok = false;
			VitePos::add_error( 'Page padding top,bottom must be numeric' );
		}
		if ( isset( $data->custom_props->cn_pd_tb ) && ! is_numeric( $data->custom_props->cn_pd_tb ) ) {
			$is_ok = false;
			VitePos::add_error( 'Container margin top,bottom must be numeric' );
		}
		if ( isset( $data->custom_props->cn_pd_se ) && ! is_numeric( $data->custom_props->cn_pd_se ) ) {
			$is_ok = false;
			VitePos::add_error( 'Container margin left,right must be numeric' );
		}
		if ( isset( $data->custom_props->pg_pd_se ) && ! is_numeric( $data->custom_props->pg_pd_se ) ) {
			$is_ok = false;
			VitePos::add_error( 'Page padding left,right must be numeric' );
		}
		if ( ! isset( $data->custom_props->pg_height ) && ! is_numeric( $data->custom_props->pg_height ) ) {
			$is_ok = false;
			VitePos::add_error( 'Page height must be numeric' );
		}
		if ( isset( $data->custom_props->cn_width ) && ! is_numeric( $data->custom_props->cn_width ) ) {
			$is_ok = false;
			VitePos::add_error( 'Barcode width must be numeric' );
		}
		if ( isset( $data->custom_props->font_size ) && ! is_numeric( $data->custom_props->font_size ) ) {
			$is_ok = false;
			VitePos::add_error( 'Font size must be numeric' );
		}

		if ( empty( $data->label ) ) {
			$is_ok = false;
			VitePos::add_error( 'Label is required' );
		}
		if ( $is_ok && empty( $data->id ) ) {
			$data->id = hash( 'crc32b', serialize( $data ) );
		}
				return $is_ok;
	}


	/**
	 * The save data is generated by appsbd
	 *
	 * @param mixed $data Its data.
	 */
	protected static function save_data( $data ) {
				$data = array_values( $data );
		if ( ! ( update_option( self::$_db_key, $data ) || add_option( self::$_db_key, $data ) ) ) {
			VitePos::add_error( 'Save failed' );
		} else {
			return true;
		}
	}

	/**
	 * The add page is generated by appsbd
	 *
	 * @param mixed $data Its data.
	 *
	 * @return false|void
	 */
	public static function add_page( $data ) {
		if ( ! self::valid_data( $data ) ) {
			return false;
		}
		$pages   = self::get_data();
		$pages[] = $data;
		return self::save_data( $pages );
	}

	/**
	 * The edit page is generated by appsbd
	 *
	 * @param mixed $id Its id.
	 * @param mixed $data Its data.
	 *
	 * @return false|void
	 */
	public static function edit_page( $id, $data ) {
		if ( ! self::valid_data( $data ) ) {
			return false;
		}
		$pages = self::get_data();
		foreach ( $pages as &$page ) {
			if ( $page->id == $id ) {
				$page = $data;
				break;
			}
		}
		return self::save_data( $pages );
	}

	/**
	 * The delete page is generated by appsbd
	 *
	 * @param mixed $id Its id.
	 */
	public static function delete_page( $id ) {
		$pages     = self::get_data();
		$found_key = null;
		foreach ( $pages as $key => $page ) {
			if ( $page->id == $id ) {
				$found_key = $key;
				break;
			}
		}
		if ( isset( $found_key ) ) {
			unset( $pages[ $found_key ] );
			return self::save_data( $pages );
		}
		return false;
	}

	/**
	 * The get page is generated by appsbd
	 *
	 * @param mixed $id Its id.
	 *
	 * @return mixed
	 */
	public static function get_page( $id ) {
		$pages = self::get_data();
		foreach ( $pages as $page ) {
			if ( $page['id'] == $id ) {
				return $page;
			}
		}
	}

	/**
	 * The GetData is generated by appsbd
	 *
	 * @return false|mixed
	 */
	public static function get_data() {
		return get_option( self::$_db_key, array() );
	}

}
