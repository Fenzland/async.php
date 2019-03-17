<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

interface IPromise
{
	
	/**
	 * Statuses of an event loop.
	 */
	const STATUSES= [
		'PENDING'   =>  0,
		'FULFILLED' =>  1,
		'RESOLVED'  =>  1,
		'REJECTED'  => -1,
	];
	
	/**
	 * Method then
	 * 
	 * @abstract
	 * @access public
	 * 
	 * @param  callable $onResolve
	 * @param  callable $onReject
	 * 
	 * @return self
	 */
	function then( callable$onResolve, callable$onReject ):self;
	
	/**
	 * Method catch
	 * 
	 * @abstract
	 * @access public
	 * 
	 * @param  callable $onReject
	 * 
	 * @return self
	 */
	function catch( callable$onReject ):self;
	
	/**
	 * Method finaly
	 * 
	 * @abstract
	 * @access public
	 * 
	 * @param  callable $finaly
	 * 
	 * @return self
	 */
	function finaly( callable$finaly ):self;
	
}
