<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

trait TWithEventLoop
{
	
	/**
	 * The event loop which handle this promise.
	 *
	 * @access private
	 *
	 * @var    mixed
	 */
	private $_event_loop;
	
	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  IEventLoop $make
	 */
	public function __construct( IEventLoop$event_loop )
	{
		$this->_event_loop= $event_loop;
	}
	
}
