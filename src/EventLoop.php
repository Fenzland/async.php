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
	 * The proxy for access.
	 * 
	 * @access private
	 * 
	 * @var    callable
	 */
	private $_proxy;
	
	/**
	 * The process queue to run.
	 * 
	 * @access private
	 * 
	 * @var    array[callable]
	 */
	private $_queue= [];
	
	/**
	 * The process queue to run.
	 * 
	 * @access private
	 * 
	 * @var    array[[ 'process'=>callable, 'call_at'=>int, ]]
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
	public function __construct( callable$main, bool$runImmediately=self::RUN_IMMEDIATELY )
	{
		$this->_proxy= new EventLoop\Proxy( $this );
		
		$this->push( $main );
		
		if( $runImmediately )
			$this->run();
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
	private function _step()
	{
		$process= array_shift( $this->_queue );
		
		if(!( $process instanceof \Closure ))
			$process= \Closure::fromCallable( $process );
		
		$process->call( $this->_proxy );
	}
	
	/**
	 * Run a single step without status checking.
	 * 
	 * @access private
	 * 
	 * @return void
	 */
	private function _stepTimeout()
	{
		if( $this->_timeout_queue[0]['call_at'] <= microtime( true ) )
		{
			[ 'process'=>$process, ]= array_shift( $this->_timeout_queue );
			
			if(!( $process instanceof \Closure ))
				$process= \Closure::fromCallable( $process );
			
			$process->call( $this );
		}
		else
			usleep( 1000 );
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
	 * @access public
	 * 
	 * @return int
	 */
	public function __get__status():int
	{
		return $this->_status;
	}
	
	/**
	 * push a process to this event loop
	 * 
	 * @access public
	 * 
	 * @param  callable $process
	 * 
	 * @return viod
	 */
	public function push( callable$process ):void
	{
		$this->ensureUnclosed();
		
		array_push( $this->_queue, $process );
		
		if( $this->_status === self::STATUSES['DONE'] )
			$this->_status= self::STATUSES['PAUSED'];
	}
	
	/**
	 * push a timeout process to this event loop
	 * 
	 * @access public
	 * 
	 * @param  callable $process
	 * @param  int $timeout
	 * 
	 * @return viod
	 */
	public function setTimeout( callable$process, int$timeout ):void
	{
		$this->ensureUnclosed();
		
		$call_at= microtime( true ) + $timeout/1000;
		
		$this->_insertTimeoutQueue( $call_at, $process );
		
		if( $this->_status === self::STATUSES['DONE'] )
			$this->_status= self::STATUSES['PAUSED'];
	}
	
	/**
	 * Insert a process into the timeout queue.
	 * 
	 * @access private
	 *
	 * @param  float    $call_at
	 * @param  callable $process
	 * 
	 * @return void
	 */
	private function _insertTimeoutQueue( float$call_at, callable$process ):void
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
		
		array_splice( $this->_timeout_queue, $floor, 0, [ [ 'process'=>$process, 'call_at'=>$call_at, ], ] );
	}
	
}
