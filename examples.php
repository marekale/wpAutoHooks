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

wpAdminMenuController::insert_menu_elem_after( 'Media', new wpAdminMenuElem() );
wpAdminMenuController::insert_menu_elem_before( 'Media', new wpAdminMenuElem() );