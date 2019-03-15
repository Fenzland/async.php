<?php

declare( strict_types= 1 );

use Async;
use PHPUnit\Framework\TestCase;

////////////////////////////////////////////////////////////////

class EventLoopTest extends TestCase
{
	
	/**
	 * Test creating a event loop and must implements the IEventLoop
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testNewEventLoop():void
	{
		$event_loop= new Async\EventLoop( function(){} );
		
		$this->assertInstanceOf( Async\EventLoop::class, $event_loop );
		$this->assertInstanceOf( Async\IEventLoop::class, $event_loop );
	}
	
	/**
	 * Test the context of processes is a proxy of the event loop.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testContext():void
	{
		$test_case= $this;
		
		$event_loop= new Async\EventLoop( function()use( $test_case ){
			
			$test_case->assertInstanceOf( Async\EventLoop\Proxy::class, $this );
			$test_case->assertInstanceOf( Async\EventLoop\IProxy::class, $this );
			
			$this->push( function()use( $test_case ){
				
				$test_case->assertInstanceOf( Async\EventLoop\Proxy::class, $this );
				$test_case->assertInstanceOf( Async\EventLoop\IProxy::class, $this );
			} );
		} );
	}
	
	/**
	 * Test status of event loop.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testStatus():void
	{
		$test_case= $this;
		
		$event_loop= new Async\EventLoop( function()use( $test_case, &$event_loop ){
			
			$test_case->assertSame( Async\IEventLoop::STATUSES['RUNNING'], $event_loop->status );
			
			$this->push( function()use( $test_case, $event_loop ){
				
				$test_case->assertSame( Async\IEventLoop::STATUSES['RUNNING'], $event_loop->status );
			} );
			
		}, Async\IEventLoop::RUN_LATER );
		
		$this->assertSame( Async\IEventLoop::STATUSES['FRESH'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( Async\IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->run();
		
		$this->assertSame( Async\IEventLoop::STATUSES['DONE'], $event_loop->status );
		
		
		$event_loop->close();
		
		$this->assertSame( Async\IEventLoop::STATUSES['CLOSED'], $event_loop->status );
	}
	
	/**
	 * Test running step by step.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testRunStepByStep():void
	{
		$sign= null;
		
		$event_loop= new Async\EventLoop( function()use( &$sign ){
			$this->push( function()use( $proxy, &$sign ){
				$sign= 1;
				
				$this->push( function()use( &$sign ){
					$sign= 4;
				} );
			} );
			
			$this->push( function()use( &$sign ){
				$sign= 2;
			} );
			
			$this->push( function()use( &$sign ){
				$sign= 3;
			} );
			
			$sign= 0;
		}, Async\IEventLoop::RUN_LATER );
		
		$this->assertSame( null, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['FRESH'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 0, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 1, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 2, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 3, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 4, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['DONE'], $event_loop->status );
		
		
		$event_loop->push( function()use( &$sign ){
			$sign= 5;
		} );
		$this->assertSame( 4, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		$event_loop->step();
		$this->assertSame( 5, $sign );
		$this->assertSame( Async\IEventLoop::STATUSES['DONE'], $event_loop->status );
	}
	
	/**
	 * Test status and throwing exceptions of a closed event loop.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testClosed():void
	{
		$event_loop= new Async\EventLoop( function(){} );
		
		$event_loop->close();
		
		$this->assertSame( Async\IEventLoop::STATUSES['CLOSED'], $event_loop->status );
		
		
		$this->expectException( Async\Exceptions\Closed::class );
		
		$event_loop->step();
		
		
		$this->expectException( Async\Exceptions\Closed::class );
		
		$event_loop->run();
		
		
		$this->expectException( Async\Exceptions\Closed::class );
		
		$event_loop->push( function(){} );
	}
	
	/**
	 * Test method setTimeout.
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testSetTimeout():void
	{
		$test_case= $this;
		
		$event_loop= new Async\EventLoop( function()use( $test_case ){
			
			$setting_at= microtime( true );
			
			$this->setTimeout( function()use( $test_case, $setting_at ){
				
				$test_case->assertGreaterThanOrEqual( $setting_at + 0.1, microtime( true ) );
				
			}, 100 );
		} );
	}
	
}
