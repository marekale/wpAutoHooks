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
	
	public function __construct( $arr=[] ) {
		if ( empty( (array)$arr) ) { return; }
		
		$empty = [
			'title'   => '', 'capability' => '', 'slug' => '', 'page_title' => '', 
			'classes' => '', 'hookname'   => '', 'icon' => '', 'count'      => 0,
		];
		
		$_arr = array_merge( $empty ,$arr );

		foreach ( array_keys( $empty ) as $key ) {
			switch ( $key ) {
				case 'title':
					$this->menu_title( $_arr['title'] );
					break;
				case 'capability':
					$this->capability( $_arr['capability'] );
					break;
				case 'slug':
					$this->menu_slug( $_arr['slug'] );
					break;
				case 'page_title':
					$this->page_title( $_arr['page_title'] );
					break;
				case 'classes':
					$this->classes( $_arr['classes'] );
					break;
				case 'hookname':
					$this->hookname( $_arr['hookname'] );
					break;
				case 'icon':
					$this->icon_url( $_arr['icon'] );
					break;
				case 'count':
					$this->menu_count( $_arr['count'] );
					break;
				default:
					break;
			}
		}
		
	}
	
	public function menu_title( $s=NULL ) { 
		
		if (is_null($s)) { return $this->menu_title; } 
		
		$this->menu_title = $this->menu_count() > 0 ? $s . ' ' .
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
	{ if (is_null($s)) { return $this->capability; } $this->capability = $s; return $this; }
	public function  menu_slug( $s=NULL ) 
	{ if (is_null($s)) { return $this->menu_slug;  } $this->menu_slug  = $s; return $this; }
	public function   menu_url( $s=NULL ) 
	{	return $this->menu_slug(); }
	public function page_title( $s=NULL ) 
	{ if (is_null($s)) { return $this->page_title; } $this->page_title = $s; return $this; }
	public function    classes( $s=NULL ) 
	{ if (is_null($s)) { return $this->classes;    } $this->classes    = $s; return $this; }
	
	public function add_class( $class ) {
		return $this->classes( self::add_css_class($class, $this->classes() ) );
	}
	
	private static function add_css_class($add, $class) {
		$class = empty($class) ? $add : $class .= ' ' . $add;
		return $class;
	}
	
	public function   hookname( $s=NULL ) 
	{ if (is_null($s)) { return $this->hookname;   } $this->hookname   = $s; return $this; }
	public function   icon_url( $s=NULL ) 
	{ if (is_null($s)) { return $this->icon_url;   } $this->icon_url   = $s; return $this; }
	
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
	
	private static $menu_actions_queue  = [];
	private static $menu_updates_queue  = [];
	
	public static function custom_menu_order_wpfilter() { return TRUE; }
	
	public static function admin_body_class_wpaction() {
		self::hook_check(__FUNCTION__);
		global $menu;
		
		self::proccess_actions_queue( self::$menu_actions_queue );
		self::proccess_actions_queue( self::$menu_updates_queue );
		
        add_menu_classes($menu);
		self::$menu_actions_queue = [];
        self::static_disconnect();
	}
	
	private static function proccess_actions_queue( $menu_actions_queue ) {
		foreach ( $menu_actions_queue as $args ) {
			if ( in_array($args['type'], ['before','after']) && !current_user_can($args[1]->capability()) ) {
				continue;
			}
			switch ( $args['type'] ) {
				case 'before':
					self::_insert_menu_item_before( $args[0], $args[1] );
					break;
				case 'after':
					self::_insert_menu_item_after( $args[0], $args[1] );
					break;
				case 'last':
					self::_insert_menu_item_last( $args[0] );
					break;
				case 'remove':
					self::_remove_menu_item( $args[0] );
					break;
				case 'update':
					self::_update_menu_item( $args[0], $args[1] );
					break;
				case 'update_all':
					global $menu;
					foreach ( $menu as $i => $k ) {
						self::_update_menu_item( $i+1, $args[0] );
					}
					break;
				default:
					break;
			}
		}
	}
	
	private static function init() {
		self::did_hook('adminmenu');
		self::default_priority(1000000000);
        self::static_connect();
	}
	
	public static function insert_menu_item_last( wpAdminMenuElem $elem ) {
		self::init();
		self::$menu_actions_queue[] = [ $elem, 'type' => 'last' ];
	}
	
	public static function insert_menu_item_after( $elem_after, wpAdminMenuElem $elem ) {
		self::init();
		self::$menu_actions_queue[] = [ $elem_after, $elem, 'type' => 'after' ];
	}
	
	public static function insert_menu_item_before( $elem_after, wpAdminMenuElem $elem ) {
		self::init();
		self::$menu_actions_queue[] = [ $elem_after, $elem, 'type' => 'before' ];
	}
	
	public static function insert_menu_item_first( wpAdminMenuElem $elem ) {
		self::insert_menu_item_before( 1, $elem );
	}
	
	public static function remove_menu_item( $elem ) {
		self::init();
		self::$menu_actions_queue[] = [ $elem, 'type' => 'remove' ];
	}
	
	public static function update_menu_item( $menu_item,  $update_info ) {
		if ( !$update_info ) { return; }
		if ( isset( $update_info['count'] ) && !isset( $update_info['title'] ) ) {
			$update_info['title'] = '%s';
		}
		self::init();
		self::$menu_updates_queue[] = [ $menu_item, (array)$update_info, 'type' => 'update' ];
	}
	
	public static function bulk_update_menu_items( $items, $update_info ) {
		foreach ( (array)$items as $item ) {
			self::update_menu_item( $item, $update_info );
		}
	}
	
	public static function update_all_menu_items( $update_info ) {
		self::init();
		self::$menu_updates_queue[] = [ (array)$update_info, 'type' => 'update_all' ];
	}

	private static function _insert_menu_item_last( wpAdminMenuElem $elem ) {
		global $menu;
		$menu[] = $elem->to_array();
	}
	
	private static function _insert_menu_item_after( $elem_after, wpAdminMenuElem $elem ) {
		global $menu;
	
		$_menu = [];
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			$_menu[] = $e;
			if ( !$lock && ( $e[2] === $elem_after || $e[0] === $elem_after || $i+1 === $elem_after ) ) {
				$_menu[] = $elem->to_array();
				$lock = TRUE;
			}
		}
		$menu = $_menu;
	}
	
	private static function _insert_menu_item_before( $elem_before, wpAdminMenuElem $elem ) {
		global $menu;
		
		$_menu = [];
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			if ( !$lock && ( $menu[$i][2] === $elem_before || $menu[$i][0] === $elem_before || $i+1 ===$elem_before ) ) {
				$_menu[] = $elem->to_array();
				$lock = TRUE;
			}
			$_menu[] = $e;
		}
		$menu = $_menu;
	}	
	
	private static function _remove_menu_item( $elem ) {
		global $menu;
		
		$_menu = [];
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			if ( !$lock && ( $menu[$i][2] === $elem || $menu[$i][0] === $elem || $i+1 === $elem ) ) {
				continue;
				$lock = TRUE;
			} else {
				$_menu[] = $e;
			}
		}
		$menu = $_menu;
	}

	private static function _update_menu_item( $menu_item,  $update_info ) {
		global $menu;
		
		$lock = FALSE;
		foreach ( $menu as $i => $e ) {
			if ( !$lock && ( $menu[$i][2] === $menu_item || $menu[$i][0] === $menu_item || $i+1 === $menu_item ) ) {
				$elem = new wpAdminMenuElem($update_info);
				$new = array_filter( $elem->to_array() ) + $menu[$i]; 
				foreach ($new as $id => $k) { $new[$id] = sprintf( $new[$id], $menu[$i][$id] ); }
				$menu[$i] = $new;
				$lock = TRUE;
			}
		}
	}
}