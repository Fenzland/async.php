<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

interface IEventLoop extends EventLoop\IProxy
{
	
	/**
	 * valid statuses of an event loop.
	 */
	const STATUSES= [
		'FRESH'   =>  0,
		'RUNNING' =>  1,
		'PAUSED'  =>  2,
		'DONE'    => -1,
		'CLOSED'  => -2,
	];
	
	/**
	 * run at constructing or later.
	 */
	const RUN_IMMEDIATELY= true;
	const RUN_LATER= false;
	
}
