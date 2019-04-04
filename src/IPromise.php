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
	function then( callable$onResolve, callable$onReject=null ):self;
	
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
	 * Method finally
	 * 
	 * @abstract
	 * @access public
	 * 
	 * @param  callable $finally
	 * 
	 * @return self
	 */
	function finally( callable$finally ):self;
	
}
