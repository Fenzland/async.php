<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

interface IEventLoop extends EventLoop\IProxy
{
	
	/**
	 * Valid statuses of an event loop.
	 */
	const STATUSES= [
		'FRESH'   =>  0,
		'RUNNING' =>  1,
		'PAUSED'  =>  2,
		'DONE'    => -1,
		'CLOSED'  => -2,
	];
	
	/**
	 * Tasks return this value to tell event loop to run the task again
	 */
	const AGAIN= INF;
}
