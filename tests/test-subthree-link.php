<?php

/**
 * Class Subthree_Link_Test
 *
 * @package Subthree_Link
 */
class Subthree_Link_Test extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	############################################################################

	/**
	 * @covers Subthree_Link::__contruct
	 */
	public function test____construct() {
		$this->assertEquals( 10, has_action( 'admin_init', [ Subthree_Link::get_instance(), 'get_target_screens' ] ) );
		$this->assertEquals( 11, has_action( 'admin_init', [ Subthree_Link::get_instance(), 'add_views_filter' ] ) );
	}

	/**
	 * @covers Subthree_Link::get_instance
	 */
	public function test__get_instance() {
		$this->assertSame( Subthree_Link::get_instance(), Subthree_Link::get_instance() );
	}

	/**
	 * @covers Subthree_Link::get_target_screens
	 * @throws ReflectionException
	 */
	public function test__get_target_screens() {
		$screen_id = 'edit-tide';
		add_filter( 'subthree_link_screen_id', function ( $screen_ids ) use ( $screen_id ) {
			$screen_ids[] = $screen_id;

			return $screen_ids;
		} );
		Subthree_Link::get_instance()->get_target_screens();
		$ref  = new ReflectionClass( Subthree_Link::get_instance() );
		$prop = $ref->getProperty( 'screen_ids' );
		$prop->setAccessible( true );

		$this->assertContains( 'edit-post', $prop->getValue( Subthree_Link::get_instance() ) );
		$this->assertContains( 'edit-page', $prop->getValue( Subthree_Link::get_instance() ) );
		$this->assertContains( 'edit-attachment', $prop->getValue( Subthree_Link::get_instance() ) );
		$this->assertContains( $screen_id, $prop->getValue( Subthree_Link::get_instance() ) );
	}

	/**
	 * @covers Subthree_Link::add_views_filter
	 * @throws ReflectionException
	 */
	public function test__add_views_filter() {
		$screen_id = 'edit-tide';
		$ref       = new ReflectionClass( Subthree_Link::get_instance() );
		$prop      = $ref->getProperty( 'screen_ids' );
		$prop->setAccessible( true );
		$prop->setValue( Subthree_Link::get_instance(), [ $screen_id ] );
		Subthree_Link::get_instance()->add_views_filter();

		$this->assertNotFalse( has_filter( "views_{$screen_id}", [ Subthree_Link::get_instance(), 'views_screen' ] ) );
	}

	/**
	 * @covers Subthree_Link::views_screen
	 */
	public function test__views_screen() {
		add_filter( 'subthree_link_query_vars', function ( $keys ) {
			$keys[] = 'foo';

			return $keys;
		} );
		add_filter('subthree_link_change', function($link) {
			return $link . '&hoge=fuga';
		});
		$views = [
			'all'     => '<a href="edit.php?post_type=post&#038;all_posts=1">All<span class="count">(7)</span></a>',
			'mine'    => '<a href="edit.php?post_type=post&#038;author=1">Mine<span class="count">(6)</span></a>',
			'publish' => '<a href="edit.php?post_status=publish&#038;post_type=post">Published<span class="count">(4)</span></a>',
			'draft'   => '<a href="edit.php?post_status=draft&#038;post_type=post">Drafts<span class="count">(2)</span ></a>',
			'pending' => '<a href="edit.php?post_status=pending&#038;post_type=post">Pending<span class="count">(1)</span></a>'
		];
		global $wp_query;
		$wp_query->set( 'orderby', 'title' );
		$wp_query->set( 'order', 'asc' );
		$wp_query->set( 's', 'test' );
		$wp_query->set( 'm', 0 );
		$wp_query->set( 'cat', 2 );
		$wp_query->set( 'foo', 'bar' );
		$expected = [
			'all'     => '<a href="edit.php?post_type=post&#038;all_posts=1&amp;orderby=title&amp;order=asc&amp;s=test&amp;m=0&amp;cat=2&amp;foo=bar&amp;hoge=fuga"  class="current" aria-current="page">All<span class="count">(7)</span></a>',
			'mine'    => '<a href="edit.php?post_type=post&#038;author=1&amp;orderby=title&amp;order=asc&amp;s=test&amp;m=0&amp;cat=2&amp;foo=bar&amp;hoge=fuga">Mine<span class="count">(6)</span></a>',
			'publish' => '<a href="edit.php?post_status=publish&#038;post_type=post&amp;orderby=title&amp;order=asc&amp;s=test&amp;m=0&amp;cat=2&amp;foo=bar&amp;hoge=fuga">Published<span class="count">(4)</span></a>',
			'draft'   => '<a href="edit.php?post_status=draft&#038;post_type=post&amp;orderby=title&amp;order=asc&amp;s=test&amp;m=0&amp;cat=2&amp;foo=bar&amp;hoge=fuga">Drafts<span class="count">(2)</span ></a>',
			'pending' => '<a href="edit.php?post_status=pending&#038;post_type=post&amp;orderby=title&amp;order=asc&amp;s=test&amp;m=0&amp;cat=2&amp;foo=bar&amp;hoge=fuga">Pending<span class="count">(1)</span></a>'
		];

		$actual = Subthree_Link::get_instance()->views_screen( $views );
		foreach ( $expected as $exp_k => $exp_v ) {
			$this->assertArrayHasKey( $exp_k, $actual );
			$this->assertEquals( $exp_v, $actual[ $exp_k ] );
		}
	}
}
