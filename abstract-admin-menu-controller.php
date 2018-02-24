<?php

class wpAdminMenuElem {
// 0 = menu_title, 1 = capability, 2 = menu_slug, 3 = page_title, 4 = classes, 5 = hookname, 6 = icon_url	
	private $menu_title = 'Menu Title';
	private $menu_count = 0;
	private $capability = 'manage_options';
	private $menu_slug  = 'https://codex.wordpress.org/Adding_Administration_Menus';
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
	
	public function capability( $s=NULL ) 
	{ if (!$s) { return $this->capability; } $this->capability = $s; return $this; }
	public function  menu_slug( $s=NULL ) 
	{ if (!$s) { return $this->menu_slug;  } $this->menu_slug  = $s; return $this; }
	public function   menu_url( $s=NULL ) 
	{	return $this->menu_slug(); }
	public function page_title( $s=NULL ) 
	{ if (!$s) { return $this->page_title; } $this->page_title = $s; return $this; }
	public function    classes( $s=NULL ) 
	{ if (!$s) { return $this->classes;    } $this->classes    = $s; return $this; }
	public function   hookname( $s=NULL ) 
	{ if (!$s) { return $this->hookname;   } $this->hookname   = $s; return $this; }
	public function   icon_url( $s=NULL ) 
	{ if (!$s) { return $this->icon_url;   } $this->icon_url   = $s; return $this; }
	
	public function to_array() {
		return [
			$this->menu_title(),
			$this->capability(),
			$this->menu_slug(),
			$this->page_title(),
			$this->classes(),
			$this->hookname(),
			$this->icon_url(),
		];
	}
}

abstract class wpAdminMenuController {
	use wpAutoHooks;
	
	private static $menu_elem_action  = [];
	
	public static function admin_body_class_wpaction() {
		self::hook_check(__FUNCTION__);
		
		foreach ( self::$menu_elem_action as $args ) {
			if ( in_array($args['type'], ['before','after']) && !current_user_can($args[1]->capability()) ) {
				continue;
			}
			switch ( $args['type'] ) {
				case 'before':
					self::_insert_menu_elem_before( $args[0], $args[1] );
					break;
				case 'after':
					self::_insert_menu_elem_after( $args[0], $args[1] );
					break;
				case 'remove':
					self::_remove_menu_elem( $args[0] );
					break;
				default:
					break;
			}
		}
        self::$menu_elem_action = [];
        self::static_disconnect();
	}
	
	public static function insert_menu_elem_after( $elem_after, wpAdminMenuElem $elem ) {
		self::did_hook('adminmenu');
        self::static_connect();
		self::$menu_elem_action[] = [ $elem_after, $elem, 'type' => 'after' ];
	}
	
	public static function insert_menu_elem_before( $elem_after, wpAdminMenuElem $elem ) {
		self::did_hook('adminmenu');
        self::static_connect();
		self::$menu_elem_action[] = [ $elem_after, $elem, 'type' => 'before' ];
	}
	
	public static function remove_menu_elem( $elem ) {
		self::did_hook('adminmenu');
		self::static_connect();
		self::$menu_elem_action[] = [ $elem, 'type' => 'remove' ];
	}

	private static function _insert_menu_elem_after( $elem_after, wpAdminMenuElem $elem ) {
		global $menu;
	
		$_menu = [];
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			$_menu[] = $e;
			if ( !$lock && ( $e[0] === $elem_after || $i === $elem_after ) ) {
				$_menu[] = $elem->to_array();
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
				$_menu[] = $elem->to_array();
				$lock = TRUE;
			}
		}
		$menu = $_menu;
	}	
	
	private static function _remove_menu_elem( $elem ) {
		global $menu;
		
		$_menu = [];
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			if ( !$lock && ( $menu[$i][0] === $elem || $i === $elem ) ) {
				continue;
				$lock = TRUE;
			} else {
				$_menu[] = $e;
			}
		}
		$menu = $_menu;
	}	
}