<?php

declare( strict_types= 1 );

namespace Async\EventLoop;

use Async\EventLoop;

////////////////////////////////////////////////////////////////

abstract class AProxyDeep
{
	
	/**
	 * The event loop.
	 * 
	 * @access private
	 * 
	 * @var    \Async\EventLoop
	 */
	private $_event_loop;
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * 
	 * @param  EventLoop $event_loop
	 */
	final public function __construct( EventLoop$event_loop )
	{
		$this->_event_loop= $event_loop;
	}
	
	/**
	 * Push a process to the event loop.
	 * 
	 * @access protected
	 * 
	 * @param  callable $process
	 * 
	 * @return void
	 */
	protected function push( callable$process ):void
	{
		$this->_event_loop->push( $process );
	}
	
	/**
	 * Push a timeout process to this event loop.
	 * 
	 * @access protected
	 * 
	 * @param  callable $process
	 * @param  int $timeout
	 * 
	 * @return void
	 */
	protected function setTimeout( callable$process, int$timeout ):void
	{
		$this->_event_loop->setTimeout( $process, $timeout );
	}
	
}
