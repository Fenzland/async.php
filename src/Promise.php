<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

class Promise implements IPromise
{
	
	/**
	 * Method then
	 * 
	 * @access public
	 * 
	 * @param  ?callable $onResolve
	 * @param  ?callable $onReject
	 * 
	 * @return self
	 */
	function then( ?callable$onResolve, ?callable$onReject ):self
	{
		
	}
	
	/**
	 * Method catch
	 * 
	 * @access public
	 * 
	 * @param  ?callable $onReject
	 * 
	 * @return self
	 */
	function catch( ?callable$onReject ):self
	{
		return $this->then( null, $onReject );
	}
	
	/**
	 * Method finaly
	 * 
	 * @access public
	 * 
	 * @param  ?callable $finaly
	 * 
	 * @return self
	 */
	function finaly( ?callable$finaly ):self
	{
		
	}
	
}
