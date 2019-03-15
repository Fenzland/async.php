<?php

declare( strict_types= 1 );

namespace Async\EventLoop;

use Async\EventLoop;

////////////////////////////////////////////////////////////////

final class Proxy extends AProxyDeep implements IProxy
{
	
	/**
	 * Push a process to the event loop.
	 * 
	 * @access public
	 * 
	 * @param  callable $process
	 * 
	 * @return void
	 */
	public function push( callable$process ):void
	{
		parent::push( $process );
	}
	
	/**
	 * Push a timeout process to this event loop.
	 * 
	 * @access public
	 * 
	 * @param  callable $process
	 * @param  int $timeout
	 * 
	 * @return void
	 */
	public function setTimeout( callable$process, int$timeout ):void
	{
		parent::setTimeout( $process, $timeout );
	}
	
}
