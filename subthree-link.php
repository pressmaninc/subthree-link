<?php
/**
 * Plugin Name: Subthree Link
 * Description: Restore subsubsub link parameter for search & sort.
 * Version: 1.0.0
 * Author: PRESSMAN
 * Author URI: https://www.pressman.ne.jp/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @author    PRESSMAN
 * @link      https://www.pressman.ne.jp/
 * @copyright Copyright (c) 2018, PRESSMAN
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 or higher
 */

defined( 'ABSPATH' ) or die();

/**
 * Class Subthree_Link
 */
class Subthree_Link {

	/**
	 * The target screen_ids.
	 *
	 * @var array
	 */
	public $screen_ids = [];

	/**
	 * construct function
	 *
	 * @return void
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'get_target_screens' ), 10 );
		add_action( 'admin_init', array( $this, 'add_views_filter' ), 11 );
	}

	/**
	 * Add target page.
	 *
	 * @access public
	 * @return void
	 */
	function get_target_screens() {
		$post_types = get_post_types( array( 'public' => true ) );
		foreach( $post_types as $post_type ) {
			$this->screen_ids[] = 'edit-' . $post_type;
		}
		$this->screen_ids = apply_filters( 'subthree_link_screen_id', $this->screen_ids );
	}

	/**
	 * Add each page to "views_hook" filter.
	 *
	 * @access public
	 * @return void
	 */
	function add_views_filter() {
		foreach( $this->screen_ids as $screen_id ) {
			add_filter( 'views_' . $screen_id, array( $this, 'views_screen' ) );
		}
	}

	/*
	 * The views link change.
	 *
	 * @access public
	 * @param array $views 
	 * @return array $views
	 */
	function views_screen( $views ) {
		foreach( $views as $class => $view ) {
			$views[$class] = $this->change_view_link( $view, $class );
		}

		return $views;
	}

	/**
	 * Add query to the link.
	 *
	 * @access public
	 * @param string $view 
	 * @param string $class 
	 * @return string $view
	 */
	function change_view_link( $view, $class ) {
		$keys = [
			'orderby',
			'order',
			's',
			'm',
			'cat'
		];
		$keys = apply_filters( 'subthree_link_query_vars', $keys );

		$link_start = mb_strpos( $view, 'href="' ) + 6;
		$link_end = mb_strpos( $view, '"', 9 );
		$link = mb_substr( $view, $link_start, $link_end - $link_start );

		foreach( $keys as $key ) {
			$value = get_query_var( $key );
			if ( $value !== '' ) {
				$link .= '&' . $key . '=' . $value;
			}
		}

		$link = apply_filters( 'subthree_link_change', $link );
		$view = preg_replace( '/href=".*?\"/', 'href="' . esc_html( $link ) . '"', $view );

		// If class is all, add [class="current"].
		if ( $class === 'all' && get_query_var( 'post_status' ) === '' ) {
			$tag_close_num = mb_strpos( $view, '>' );
			$view = mb_substr( $view, 0, $tag_close_num ) . '  class="current" aria-current="page"' . mb_substr( $view, $tag_close_num );
		}

		return $view;
	}
}

new Subthree_Link();