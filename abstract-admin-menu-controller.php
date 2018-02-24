<?php

class wpAdminMenuElem {
// 0 = menu_title, 1 = capability, 2 = menu_slug, 3 = page_title, 4 = classes, 5 = hookname, 6 = icon_url	
	private $menu_title = 'Menu Title';
	private $capability = 'manage_options';
	private $menu_slug  = 'file.php';
	private $page_title = '';
	private $classes    = 'menu-top';
	private $hookname   = '';
	private $icon_url   = 'dashicons-wordpress';
	
	public function menu_title( $s=NULL ) { if (!$s) { return $this->menu_title; } $this->menu_title = $s; return $this; }
	public function capability( $s=NULL ) { if (!$s) { return $this->capability; } $this->capability = $s; return $this; }
	public function  menu_slug( $s=NULL ) { if (!$s) { return $this->menu_slug;  } $this->menu_slug  = $s; return $this; }
	public function page_title( $s=NULL ) { if (!$s) { return $this->page_title; } $this->page_title = $s; return $this; }
	public function    classes( $s=NULL ) { if (!$s) { return $this->classes;    } $this->classes    = $s; return $this; }
	public function   hookname( $s=NULL ) { if (!$s) { return $this->hookname;   } $this->hookname   = $s; return $this; }
	public function   icon_url( $s=NULL ) { if (!$s) { return $this->icon_url;   } $this->icon_url   = $s; return $this; }
}

abstract class wpAdminMenuController {
	use wpAutoHooks;
	
	private static $insert_menu_elem_after  = [];
	private static $insert_menu_elem_before = [];
	
	public static function admin_body_class_wpaction() {
		self::hook_check(__FUNCTION__);
	
		foreach ( self::$insert_menu_elem_after as $args ) {
			switch ( $args['type'] ) {
				case 'before':
					self::_insert_menu_elem_before( $args[0], $args[1] );

					break;
				case 'after':
					self::_insert_menu_elem_after( $args[0], $args[1] );

					break;

				default:
					break;
			}
		}
	}
	
	public static function insert_menu_elem_after( $elem_after, wpAdminMenuElem $elem ) {
		self::did_hook('adminmenu');
		self::$insert_menu_elem_after[] = [ $elem_after, $elem, 'type' => 'after' ];
	}
	
	public static function insert_menu_elem_before( $elem_after, wpAdminMenuElem $elem ) {
		self::did_hook('adminmenu');
		self::$insert_menu_elem_before[] = [ $elem_after, $elem, 'type' => 'before' ];
	}

	private static function _insert_menu_elem_after( $elem_after, wpAdminMenuElem $elem ) {
		global $menu;
	
		$_menu = [];
		foreach ( $menu as $i => $e ) {
			$_menu[] = $e;
			if ( $e[0] === $elem_after || $i === $elem_after ) {
				$_menu[] = [
					$elem->menu_title(),
					$elem->capability(),
					$elem->menu_slug(),
					$elem->page_title(),
					$elem->classes(),
					$elem->hookname(),
					$elem->icon_url(),
				];
			}
		}
		$menu = $_menu;
	}
	
	private static function _insert_menu_elem_before( $elem_after, wpAdminMenuElem $elem ) {
		global $menu;
		
		$_menu = [];
		foreach ( $menu as $i => $e ) {
			$_menu[] = $e;
			if ( $menu[$i+1][0] === $elem_after || $i+1 === $elem_after ) {
				$_menu[] = [
					$elem->menu_title(),
					$elem->capability(),
					$elem->menu_slug(),
					$elem->page_title(),
					$elem->classes(),
					$elem->hookname(),
					$elem->icon_url(),
				];
			}
		}
		$menu = $_menu;
	}	
}

wpAdminMenuController::connect();