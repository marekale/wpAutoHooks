<?php

class wpAdminMenuElem {
// 0 = menu_title, 1 = capability, 2 = menu_slug, 3 = page_title, 4 = classes, 5 = hookname, 6 = icon_url	
	private $menu_title = 'Menu Title';
	private $menu_count = FALSE;
	private $capability = 'manage_options';
	private $menu_slug  = 'file.php';
	private $page_title = '';
	private $classes    = 'menu-top';
	private $hookname   = '';
	private $icon_url   = 'dashicons-wordpress';
	
	public function menu_title( $s=NULL ) { 
		
		if (!$s) { return $this->menu_title; } 
		
		$this->menu_title = $this->menu_count() > 0 ? $s .
				"<span class='update-plugins count-{$this->menu_count()}'>"
				. "<span class='update-count'>" 
						. number_format_i18n($this->menu_count()) 
				. "</span></span>" : $s; 
				
		return $this; 
	}
	
	public function menu_count( $c=NULL )      {	
		
		if (!$c) { return $this->menu_count; } 
		
		$this->menu_count = (int)$c; 
		$this->menu_title( $this->menu_title() ); 
		
		return $this; 
	}
	
	public function capability( $s=NULL ) { if (!$s) { return $this->capability; } $this->capability = $s; return $this; }
	public function  menu_slug( $s=NULL ) { if (!$s) { return $this->menu_slug;  } $this->menu_slug  = $s; return $this; }
	public function   menu_url( $s=NULL ) {	return $this->menu_slug(); }
	public function page_title( $s=NULL ) { if (!$s) { return $this->page_title; } $this->page_title = $s; return $this; }
	public function    classes( $s=NULL ) { if (!$s) { return $this->classes;    } $this->classes    = $s; return $this; }
	public function   hookname( $s=NULL ) { if (!$s) { return $this->hookname;   } $this->hookname   = $s; return $this; }
	public function   icon_url( $s=NULL ) { if (!$s) { return $this->icon_url;   } $this->icon_url   = $s; return $this; }
}

abstract class wpAdminMenuController {
	use wpAutoHooks;
	
	private static $insert_menu_elem  = [];
	
	public static function admin_body_class_wpaction() {
		self::hook_check(__FUNCTION__);
	
		foreach ( self::$insert_menu_elem as $args ) {
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
                self::$insert_menu_elem = [];
                self::disconnect();
	}
	
	public static function insert_menu_elem_after( $elem_after, wpAdminMenuElem $elem ) {
		self::did_hook('adminmenu');
                self::connect();
		self::$insert_menu_elem[] = [ $elem_after, $elem, 'type' => 'after' ];
	}
	
	public static function insert_menu_elem_before( $elem_after, wpAdminMenuElem $elem ) {
		self::did_hook('adminmenu');
                self::connect();
		self::$insert_menu_elem[] = [ $elem_after, $elem, 'type' => 'before' ];
	}

	private static function _insert_menu_elem_after( $elem_after, wpAdminMenuElem $elem ) {
		global $menu;
	
		$_menu = [];
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			$_menu[] = $e;
			if ( !$lock && ( $e[0] === $elem_after || $i === $elem_after ) ) {
				$_menu[] = [
					$elem->menu_title(),
					$elem->capability(),
					$elem->menu_slug(),
					$elem->page_title(),
					$elem->classes(),
					$elem->hookname(),
					$elem->icon_url(),
				];
				$lock = TRUE;
			}
		}
		$menu = $_menu;
	}
	
	private static function _insert_menu_elem_before( $elem_after, wpAdminMenuElem $elem ) {
		global $menu;
		
		$_menu = [];
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			$_menu[] = $e;
			if ( !$lock && ( $menu[$i+1][0] === $elem_after || $i+1 === $elem_after ) ) {
				$_menu[] = [
					$elem->menu_title(),
					$elem->capability(),
					$elem->menu_slug(),
					$elem->page_title(),
					$elem->classes(),
					$elem->hookname(),
					$elem->icon_url(),
				];
				$lock = TRUE;
			}
		}
		$menu = $_menu;
	}	
}