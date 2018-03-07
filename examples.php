<?php

require('trait-wp-auto-hooks.php');
class Class1 {
	use wpAutoHooks;
        
        private $id;
        public function __construct( $id ) {
            $this->id = $id;
        }
        
        public static function action1_wpaction()     {  } // static method action with default priority (10)
        public static function action1_wpaction11()   {  } // action with priority
        
        public function action1_wpaction12()          {  } // instance action
        public function action2_wpaction( $v1, $v2 )  {  } // action with 2 arguments
        
        public static function filter1_wpfilter( $v1 ) { return $v1 . '_filtered'; } // filter
        
        public static function all_wpaction() {  } // Nothing happens
		
	public static function action3_wpaction_wpaction() {} // 'action3_wpaction' action
		
        public function action4_wpaction() {
                self::hook_check(__FUNCTION__); // Throw exception if current action is not 'action4'
                self::did_hook('action1');      // Throw exception if 'action1' has already fired
        }
}

Class1::static_connect();               // Add static actions and filters
$b = Class1::static_connected();        // Are static actions and filters added ?
Class1::static_disconnect();            // Remove static actions and filters
Class1::default_priority( 100 );        // Set default hooks priority
$priority = Class1::default_priority(); // Get default hooks priority
$instance = new Class1(1);              // Create instance ( hooks not added )
$instance->connect();                   // Add instance hooks
$instance->connected();			// Does instance have hooks and filters added ?
$instance->disconnect();                // Remove instance hooks

