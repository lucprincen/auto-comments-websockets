<?php
/**
 * Plugin Name: Auto updating Comments
 * Plugin URI: http://www.chefduweb.nl
 * Description: Auto-updating comments using the Heartbeat API
 * Version: 1.0
 * Author: Chef du Web
 * Author URI: http://www.chefduweb.nl
 * Requires at least: 3.6
 * Tested up to: 3.6.1
 *
 */

namespace AutoComment

use ZMQContext;

class AutoComment{

    	public function __construct()
    	{
        	add_action( 'comment_hold_to_approved', [ $this, 'push_new_comment' ], 100, 2 );
        	add_action( 'comment_post', [ $this, 'check_new_comment' ], 100, 2 );
    	}

    	/**
     	* Check a comment's status before pushing it:
     	*
     	* @param int $comment_id
     	* @param int $status
     	* 
     	* @return void
     	*/
	public function check_new_comment( $comment_id, $status )
	{
	 	//comment_post sends along a status, if it's "succes", $status == 1
		if( $status == 1 ){
			$comment = get_comment( $comment_id );
			$this->push_new_comment( $comment );
		}   		
	}    

	/**
	 * Push a new comment to every client registered
	 * 
	 * @param  WP_Comment $comment
	 * 
	 * @return void
	 */
	public function push_new_comment( $comment )
	{
		$context = new ZMQContext();
		$socket = $context->getSocket( ZMQ::SOCKET_PUSH, 'comment pusher' );
		$socket->connect( 'tcp://localhost:5555' );
		$socket->send( json_encode( $comment ) );	
	}
}

new AutoComment\Autocomment();

	
?>
