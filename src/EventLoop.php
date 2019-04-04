<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

/**
 * 
 */
final class EventLoop implements IEventLoop
{
	use Helpers\TWithGetters;
	
	/**
	 * The task queue to run.
	 * 
	 * @access private
	 * 
	 * @var    array[callable]
	 */
	private $_queue= [];
	
	/**
	 * The task queue to run.
	 * 
	 * @access private
	 * 
	 * @var    array[[ 'task'=>callable, 'call_at'=>int, ]]
	 */
	private $_timeout_queue= [];
	
	/**
	 * Status of this event loop.
	 * 
	 * @access private
	 * 
	 * @var    int
	 */
	private $_status= self::STATUSES['FRESH'];
	
	/**
	 * Create a event loop
	 * 
	 * @access public
	 * 
	 * @param  callable $main
	 * @param  bool     $runImmediately   valid values are self::RUN_IMMEDIATELY and self::RUN_LATER
	 */
	public function __construct( ?callable$main=null )
	{
		if( $main )
		{
			$this->push( function()use( $main ){
				$main( new EventLoop\Proxy( $this ) );
			} );
			
			$this->run();
		}
	}
	
	/**
	 * Method __destruct
	 * 
	 * @access public
	 */
	public function __destruct()
	{
		switch( $this->_status )
		{
			default:
			case self::STATUSES['DONE']:
			case self::STATUSES['CLOSED']:
			break;
			
			case self::STATUSES['RUNNING']:
				$this->_status= self::STATUSES['PAUSED'];
			
			case self::STATUSES['FRESH']:
			case self::STATUSES['PAUSED']:
				$this->run();
			break;
		}
	}
	
	/**
	 * Run the event loop.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function run():void
	{
		$this->ensureUnclosed();
		
		if( $this->_status === self::STATUSES['RUNNING'] )
			return;
		
		$this->_status= self::STATUSES['RUNNING'];
		
		$this->_loop();
		
		$this->_status= self::STATUSES['DONE'];
	}
	
	/**
	 * Run a single step.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function step():void
	{
		$this->ensureUnclosed();
		
		if( $this->_queue )
		{
			$this->_status= self::STATUSES['RUNNING'];
			
			$this->_step();
		}
		else
		if( $this->_timeout_queue )
		{
			$this->_status= self::STATUSES['RUNNING'];
			
			$this->_stepTimeout();
		}
		else
		{
			$this->_status= self::STATUSES['DONE'];
			
			return;
		}
		
		if( $this->_queue || $this->_timeout_queue )
			$this->_status= self::STATUSES['PAUSED'];
		else
			$this->_status= self::STATUSES['DONE'];
	}
	
	/**
	 * Run steps by a loop.
	 * 
	 * @access private
	 * 
	 * @return void
	 */
	private function _loop():void
	{
		while( true )
			if( $this->_status === self::STATUSES['PAUSED'] )
				break;
			else
			if( $this->_queue )
				$this->_step();
			else
			if( $this->_timeout_queue )
				$this->_stepTimeout();
			else
				break;
	}
	
	/**
	 * Run a single step without status checking.
	 * 
	 * @access private
	 * 
	 * @return void
	 */
	private function _step():void
	{
		$task= array_shift( $this->_queue );
		
		$this->_runTask( $task );
	}
	
	/**
	 * Run a single step without status checking.
	 * 
	 * @access private
	 * 
	 * @return void
	 */
	private function _stepTimeout():void
	{
		if( $this->_timeout_queue[0]['call_at'] <= microtime( true ) )
		{
			[ 'task'=>$task, ]= array_shift( $this->_timeout_queue );
			
			$this->_runTask( $task );
		}
		else
			usleep( 1000 );
	}
	
	/**
	 * Run a task.
	 * 
	 * @access private
	 * 
	 * @param  callable $task
	 * 
	 * @return void
	 */
	private function _runTask( callable$task ):void
	{
		if(!( $task instanceof \Closure ))
			$task= \Closure::fromCallable( $task );
		
		$next= $task();
		
		if( $next === self::AGAIN )
			$next= $task;
		
		if( $next && is_callable( $next ) )
			$this->push( $next );
	}
	
	/**
	 * close this event loop if it is done.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function close():void
	{
		if( $this->_status === self::STATUSES['DONE'] )
			$this->_status= self::STATUSES['CLOSED'];
	}
	
	/**
	 * Method ensureUnclosed
	 * 
	 * @access private
	 * 
	 * @return void
	 * @throws \Async\Exception\Closed
	 */
	private function ensureUnclosed():void
	{
		if( $this->_status === self::STATUSES['CLOSED'] )
			throw new Exceptions\Closed();
	}
	
	/**
	 * getter of status
	 * 
	 * @access protected
	 * 
	 * @return int
	 */
	protected function __get__status():int
	{
		return $this->_status;
	}
	
	/**
	 * push a task to this event loop
	 * 
	 * @access public
	 * 
	 * @param  callable $task
	 * 
	 * @return viod
	 */
	public function push( callable$task ):void
	{
		$this->ensureUnclosed();
		
		array_push( $this->_queue, $task );
		
		if( $this->_status === self::STATUSES['DONE'] )
			$this->_status= self::STATUSES['PAUSED'];
	}
	
	/**
	 * push a timeout task to this event loop
	 * 
	 * @access public
	 * 
	 * @param  callable $task
	 * @param  int $timeout
	 * 
	 * @return viod
	 */
	public function setTimeout( callable$task, int$timeout ):void
	{
		$this->ensureUnclosed();
		
		$call_at= microtime( true ) + $timeout/1000;
		
		$this->_insertTimeoutQueue( $call_at, $task );
		
		if( $this->_status === self::STATUSES['DONE'] )
			$this->_status= self::STATUSES['PAUSED'];
	}
	
	/**
	 * Insert a task into the timeout queue.
	 * 
	 * @access private
	 *
	 * @param  float    $call_at
	 * @param  callable $task
	 * 
	 * @return void
	 */
	private function _insertTimeoutQueue( float$call_at, callable$task ):void
	{
		$floor= 0;
		$ceil= sizeof( $this->_timeout_queue );
		
		while( $ceil > $floor )
		{
			$half= (int)( ($ceil + $floor)/2 );
			
			if( $call_at < $this->_timeout_queue[$half]['call_at'] )
				$ceil= $half;
			else
				$floor= $half + 1;
		}
		
		array_splice( $this->_timeout_queue, $floor, 0, [ [ 'task'=>$task, 'call_at'=>$call_at, ], ] );
	}
	
}
