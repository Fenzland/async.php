<?php

declare( strict_types= 1 );

namespace Async\EventLoop;

////////////////////////////////////////////////////////////////

interface IProxy
{
	
	/**
	 * push a process to the event loop
	 * 
	 * @abstract
	 * @access public
	 * 
	 * @param  callable $process
	 * 
	 * @return void
	 */
	function push( callable$process ):void;
	
	/**
	 * push a process to this event loop
	 * 
	 * @abstract
	 * @access public
	 * 
	 * @param  callable $process
	 * @param  int $timeout
	 * 
	 * @return void
	 */
	function setTimeout( callable$process, int$timeout ):void;
	
}
