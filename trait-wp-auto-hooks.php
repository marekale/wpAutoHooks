<?php

trait wpAutoHooks {
	
	private static $WP_ACTION_HOOK_SUFFIX           = '_wpaction' ;
	private static $WP_FILTER_HOOK_SUFFIX           = '_wpfilter' ;
	private static $WP_DEFAULT_HOOK_PRIORITY        = 10;
	
	private static $VALIDATE_METHOD_NAME            = 'method_is_wp_hook';
	private static $VALIDATE_FILTER_METHOD_NAME     = "method_is_wp_filter";
	private static $VALIDATE_ACTION_METHOD_NAME     = "method_is_wp_action";	
	
	private static $METHOD_NAME_ERROR_MESSAGE       = 'Invalid method name';
	private static $UNKNOWN_HOOK_TYPE_ERROR_MESSAGE = 'Unknown hook type' ;
	
	private static $methods_names_filters = [];
	private static $methods_names_actions = [];

	public static function connect( $instance=NULL ) {
            
		if ( !( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) ) {
			throw new Exception();
		}
		
		self::create_hook_connections_from_names( self::get_method_names_hooks(), $instance );
	}

	public static function disconnect( $instance=NULL ) {
            
		if ( !( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) ) {
			throw new Exception();
		}
		
		self::remove_hook_connections_from_names( self::get_method_names_hooks(), $instance );
	}
        
        private static function hook_check( $name ) {
		$tag = self::get_tag_from_method($name);
		return $tag && $tag === current_filter();
	}

	private static function create_hook_connections_from_names( $method_names, $instance=NULL ) {
		if ( !is_array( $method_names ) || empty( $method_names ) ) {
			return;
		}
		
		foreach ( $method_names as $name ) {
			
			$rm = new ReflectionMethod ( self::class, $name );
			$static = $rm->isStatic();
			$params = $rm->getNumberOfRequiredParameters();
			$tag      = self::get_tag_from_method($name);
			$priority = self::get_priority_from_method($name);
			
			if ( !$static && !$instance || 'all' === $tag ) { continue; }

			if ( in_array( $name, self::$methods_names_actions ) ) {
				add_action( $tag, [ $static  ? static::class : $instance, $name ], 
						$priority, $params );
			} elseif ( in_array( $name, self::$methods_names_filters ) ) {
				add_filter( $tag, [ $static ? static::class : $instance, $name ], 
						$priority, $params );
			}
		}
	}
        private static function remove_hook_connections_from_names( $method_names, $instance=NULL ) {
		if ( !is_array( $method_names ) || empty( $method_names ) ) {
			return;
		}
		
		foreach ( $method_names as $name ) {
			
			$rm = new ReflectionMethod ( self::class, $name );
			$static = $rm->isStatic();
			$params = $rm->getNumberOfRequiredParameters();
			$tag      = self::get_tag_from_method($name);
			$priority = self::get_priority_from_method($name);
			
			if ( !$static && !$instance || 'all' === $tag ) { continue; }

			if ( in_array( $name, self::$methods_names_actions ) ) {
				remove_action( $tag, [ $static  ? static::class : $instance, $name ], 
						$priority );
			} elseif ( in_array( $name, self::$methods_names_filters ) ) {
				remove_filter( $tag, [ $static ? static::class : $instance, $name ], 
						$priority );
			}
		}
	}
	private static function get_priority_from_method( $name ) {
		
		$chunks     = self::get_method_chunks( $name ) ;
		$last_chunk = str_replace( '_', '',end($chunks) );
		
		$priority = absint( $last_chunk==='' ? 
						self::$WP_DEFAULT_HOOK_PRIORITY :
						$last_chunk );	
		
		return $priority;
	}
	
	private static function get_tag_from_method( $name ) {
		$chunks     = self::get_method_chunks( $name ) ;
		return $chunks[0];
	}
	
	private static function get_method_chunks( $name ) {		
		if ( self::method_is_wp_action( $name ) ) {
			$method_chunks = explode( self::$WP_ACTION_HOOK_SUFFIX , $name );
			$count = count( $method_chunks );
			if ( $count > 2 ) {
				foreach ( $method_chunks as $i => $chunk ) {
					if ( $i > 0 && $i < $count - 1 ) {
						$method_chunks[0] .= self::$WP_ACTION_HOOK_SUFFIX . $method_chunks[$i];
					}
				}
			} elseif ( $count === 2 ) {
				return $method_chunks;
			} else {
				throw new Exception( self::$METHOD_NAME_ERROR_MESSAGE );
			}
		} elseif ( self::method_is_wp_filter( $name ) ) {
			$method_chunks = explode( self::$WP_FILTER_HOOK_SUFFIX , $name );
			$count = count( $method_chunks );
			if ( $count > 2 ) {
				foreach ( $method_chunks as $i => $chunk ) {
					if ( $i > 0 && $i < $count - 1 ) {
						$method_chunks[0] .= self::$WP_FILTER_HOOK_SUFFIX . $method_chunks[$i];
					}
				}
			} elseif ( $count === 2 ) {
				return $method_chunks;
			} else {
				throw new Exception( self::$METHOD_NAME_ERROR_MESSAGE );
			}
		} else {
				throw new Exception( self::$UNKNOWN_HOOK_TYPE_ERROR_MESSAGE );
		}

		return $method_chunks;
	}
	private static function get_method_names_hooks() {

		$methods_names          = self::get_this_class_methods( self::class );
		self::$methods_names_filters = 
			array_filter( $methods_names, [self::class, self::$VALIDATE_FILTER_METHOD_NAME] );
		self::$methods_names_actions = 
			array_filter( $methods_names, [self::class, self::$VALIDATE_ACTION_METHOD_NAME] );

		return array_merge(self::$methods_names_actions,self::$methods_names_filters);
	}
	
	private static function get_this_class_methods( $class ){
		$array1 = get_class_methods( $class );
		if( $parent_class = get_parent_class( $class ) ) {
			$array2 = get_class_methods( $parent_class );
			$array3 = array_diff( $array1, $array2 );
		} else {
			$array3 = $array1;
		}
		return $array3;
	}
	
	private static function method_is_wp_hook( $method_name ) {
		return 
			(bool)self::method_is_wp_action( $method_name ) || 
			(bool)self::method_is_wp_filter( $method_name )  ;
	}
	
	private static function method_is_wp_action( $method_name ) {
		$exlode = explode( self::$WP_ACTION_HOOK_SUFFIX, $method_name );
		return 
		strpos( $method_name , self::$WP_ACTION_HOOK_SUFFIX ) !== false &&
		self::method_is_wp_hook( end( $exlode ) ) === false ;
	}
	
	private static function method_is_wp_filter( $method_name ) {
		$exlode = explode( self::$WP_FILTER_HOOK_SUFFIX, $method_name );
		return 
		strpos( $method_name , self::$WP_FILTER_HOOK_SUFFIX ) !== false &&
		self::method_is_wp_hook( end( $exlode ) ) === false ;
	}
}

