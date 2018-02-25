<?php

require('trait-wp-auto-hooks.php');

abstract class Class1 {
	use wpAutoHooks;
	static public function test1_wpaction() {esc_html_e(current_filter());}	
	static public function test2_wpfilter( $var ) {esc_html_e([current_filter(),$var]);return $var;}
	static public function test3_wpfilter11() {esc_html_e(current_filter());}	
	static public function test4_wpaction11() {esc_html_e(current_filter());}
	static public function test5_wpfilter11_wpaction() {esc_html_e(current_filter());}	
	static public function test6_wpaction11_wpfilter11() {esc_html_e(current_filter());}	
	public    function test7_wpaction() {esc_html_e(current_filter());}	
	protected function test8_wpaction() {esc_html_e(current_filter());}	
	private   function test9_wpaction() {esc_html_e(current_filter());}	
	abstract static public function test10_wpaction() ;	
}

class Class2 extends Class1 {
	public static function test10_wpaction() { esc_html_e(current_filter()); }
}

(new Class2())->connect();
//Class2::connect(new Class2());
//Class2::connect();

do_action('test1');
apply_filters('test2', 'test');
do_action('test4');
do_action('test5_wpfilter11');
do_action('test7');
do_action('test8');
do_action('test9');
do_action('test10');

require('abstract-admin-menu-controller.php');

wpAdminMenuController::insert_menu_item_before('WooCommerce', new wpAdminMenuElem());
wpAdminMenuController::insert_menu_item_first(new wpAdminMenuElem());
add_action( 'plugins_loaded', function () {wpAdminMenuController::remove_menu_item('Tools');} );
wpAdminMenuController::insert_menu_item_after(2, (new wpAdminMenuElem())->menu_count(3));
wpAdminMenuController::insert_menu_item_after( 'Media', new wpAdminMenuElem() );
wpAdminMenuController::insert_menu_item_before( 2, (new wpAdminMenuElem())->menu_count( 9 )->menu_title('int test')->capability( 'read' )->add_class( 'marale-test1' )->add_class( 'marale-test2' ) );
wpAdminMenuController::insert_menu_item_before( 'Menu Title', (new wpAdminMenuElem())->menu_title('Before Menu Title') );
wpAdminMenuController::insert_menu_item_before('plugins.php', new wpAdminMenuElem());
wpAdminMenuController::insert_menu_item_after('plugins.php', new wpAdminMenuElem());
wpAdminMenuController::insert_menu_item_last((new wpAdminMenuElem())->menu_title( 'Last Menu Item') );
wpAdminMenuController::update_menu_item('users.php', ['title'=>'%s - Updated Menu Item', 'icon'=>'dashicons-info','classes'=>'%s marale-test1','count'=> 666] );
wpAdminMenuController::bulk_update_menu_items( [ 'Pages', 'Posts' ], ['count'=>123] );
wpAdminMenuController::update_all_menu_items([ 'title' => 'All - %s', 'icon' => 'dashicons-info' ]);