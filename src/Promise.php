<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

class Promise implements IPromise
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
	 * Status of this promise.
	 * 
	 * @access private
	 * 
	 * @var    int
	 */
	private $_status= self::STATUSES['PENDING'];
	
	/**
	 * The value if this promise is resolved.
	 * 
	 * @access private
	 * 
	 * @var    mixed
	 */
	private $_value;
	
	/**
	 * The reason why this promise is rejected, if it does.
	 * 
	 * @access private
	 * 
	 * @var    \Throwable
	 */
	private $_reason;
	
	/**
	 * Wheter this promise is catched.
	 *
	 * @access private
	 *
	 * @var    bool
	 */
	private $_catched= false;
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * 
	 * @param  IEventLoop $event_loop
	 * @param  callable   $make
	 */
	public function __construct( IEventLoop$event_loop, callable$make )
	{
		$this->_event_loop= $event_loop;
		
		$resolve= function( $value ){
			$this->_value= $value;
			$this->_status= self::STATUSES['RESOLVED'];
		};
		
		$reject= function( $reason ){
			$this->_reason= $reason;
			$this->_status= self::STATUSES['REJECTED'];
		};
		
		try
		{
			$make( $resolve, $reject );
		}
		catch( \Throwable$reason )
		{
			$this->_reason= $reason;
		}
	}
	
	/**
	 * Destructor
	 * 
	 * @access public
	 */
	public function __destruct()
	{
		if( $this->_reason && !$this->_catched )
		{
			throw $this->_reason;
		}
	}
	
	/**
	 * Method then
	 * 
	 * @access public
	 * 
	 * @param  ?callable $onResolve
	 * @param  ?callable $onReject
	 * 
	 * @return IPromise
	 */
	public function then( ?callable$onResolve, ?callable$onReject=null ):IPromise
	{
		if( $onReject )
			$this->_catched= true;
		
		return new self( $this->_event_loop, function( $resolve, $reject )use( $onResolve, $onReject ){
			$this->_event_loop->push( function()use( $resolve, $reject, $onResolve, $onReject ){
				switch( $this->_status )
				{
					case self::STATUSES['PENDING']:
						return EventLoop::AGAIN;
					break;
					
					case self::STATUSES['RESOLVED']:
						$resolve(
							$onResolve( $this->_value )
						);
					break;
					
					case self::STATUSES['REJECTED']:
						$resolve(
							$onReject( $this->_reason )
						);
					break;
				}
			} );
		} );
	}
	
	/**
	 * Method catch
	 * 
	 * @access public
	 * 
	 * @param  callable $onReject
	 * 
	 * @return IPromise
	 */
	public function catch( callable$onReject ):IPromise
	{
		return $this->then( null, $onReject );
	}
	
	/**
	 * Method finally
	 * 
	 * @access public
	 * 
	 * @param  callable $finally
	 * 
	 * @return IPromise
	 */
	public function finally( callable$finally ):IPromise
	{
		return new self( $this->_event_loop, function( $resolve, $reject )use( $finally ){
			$this->_event_loop->push( function()use( $resolve, $reject, $finally ){
				switch( $this->_status )
				{
					case self::STATUSES['PENDING']:
						return EventLoop::AGAIN;
					break;
					
					case self::STATUSES['RESOLVED']:
						$finally();
						
						$resolve( $this->_value );
					break;
					
					case self::STATUSES['REJECTED']:
						$finally();
						
						$reject( $this->_reason );
					break;
				}
			} );
		} );
	}
	
	/**
	 * Build a immediately resolved promise.
	 * 
	 * @static
	 * 
	 * @access public
	 * 
	 * @param  IEventLoop $make
	 * @param  mixed $value
	 * 
	 * @return IPromise
	 */
	static public function resolve( IEventLoop$event_loop, $value ):IPromise
	{
		return new self( $event_loop, function( $resolve, $reject )use( $value ){
			$resolve( $value );
		} );
	}
	
	/**
	 * Build a immediately rejected promise.
	 * 
	 * @static
	 * 
	 * @access public
	 * 
	 * @param  IEventLoop $make
	 * @param  mixed $reason
	 * 
	 * @return IPromise
	 */
	static public function rejected( IEventLoop$event_loop, $reason ):IPromise
	{
		return new self( $event_loop, function( $resolve, $reject )use( $reason ){
			$reject( $reason );
		} );
	}
	
}
