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
		new Promise( $this->event_loop, function( $resolve, $reject ){
			
			$gen= $task();
			
			if(!( $gen instanceof \Generator ) )
				return $resolve( $gen );
			
			$Aa= $Bb= null;
			
			$Aa= function()use( $gen, &$Bb ){
				$current= $gen->current();
				
				if( !is_thenable( $current ) )
					$current= Promise::resolve( $this->event_loop, $current );
				
				$current->then( $Bb );
			};
			
			$Bb= function( $value )use( $gen, &$Aa ){
				$gen->send( $value );
				
				if( $gen->valid() )
					$Aa();
				else
					$resolve( $gen->getReturn() );
			};
			
			$this->event_loop->push( $Aa );
			
		} );
	}
	
}

function is_thenable( $value )
{
	return is_object( $result ) && method_exists( $result, 'then' );
}
