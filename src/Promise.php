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
	 * Constructor
	 * 
	 * @access public
	 * 
	 * @param  IEventLoop $make
	 * @param  callable $make
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
			$this->reason= $reason;
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
	 * @return self
	 */
	public function then( ?callable$onResolve, ?callable$onReject ):self
	{
		new self( $this->_event_loop, function( $resolve, $reject ){
			$this->_event_loop->push( function(){
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
						$reject(
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
	 * @return self
	 */
	public function catch( callable$onReject ):self
	{
		return $this->then( null, $onReject );
	}
	
	/**
	 * Method finaly
	 * 
	 * @access public
	 * 
	 * @param  callable $finaly
	 * 
	 * @return self
	 */
	public function finaly( callable$finaly ):self
	{
		$finaly= function()use( $finaly ){
			$finaly();
		};
		
		return $this->then( $finaly, $finaly );
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
	 * @return self
	 */
	static public function resolve( IEventLoop$event_loop, $value ):self
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
	 * @return self
	 */
	static public function rejected( IEventLoop$event_loop, $reason ):self
	{
		return new self( $event_loop, function( $resolve, $reject )use( $reason ){
			$reject( $reason );
		} );
	}
	
}
