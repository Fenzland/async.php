<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

class Async
{
	use TWithEventLoop;
	
	/**
	 * Run a function asynchronously.
	 * 
	 * @access public
	 * 
	 * @param  callable $task
	 * 
	 * @return Promise
	 */
	public function run( callable$task ):Promise
	{
		return new Promise( $this->_event_loop, function( $resolve, $reject )use( $task ){
			
			$gen= $task();
			
			if(!( $gen instanceof \Generator ) )
				return $resolve( $gen );
			
			$connect= $step= null;
			
			$connect= function()use( $gen, &$step ){
				$current= $gen->current();
				
				if( !self::is_thenable( $current ) )
					$current= Promise::resolve( $this->_event_loop, $current );
				
				$current->then( $step );
			};
			
			$step= function( $value )use( $gen, $resolve, &$connect ){
				$gen->send( $value );
				
				if( $gen->valid() )
					$connect();
				else
					$resolve( $gen->getReturn() );
			};
			
			$connect();
		} );
	}
	
	/**
	 * Static method is_thenable
	 * 
	 * @static
	 * 
	 * @access public
	 *
	 * @param  mixed $value
	 * 
	 * @return bool
	 */
	static public function is_thenable( $value ):bool
	{
		return is_object( $value ) && method_exists( $value, 'then' );
	}
	
}
