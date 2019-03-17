<?php

declare( strict_types= 1 );

use Async\EventLoop;
use Async\IEventLoop;
use Async\Exceptions;
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
		$event_loop= new EventLoop();
		
		$this->assertInstanceOf( EventLoop::class, $event_loop );
		$this->assertInstanceOf( IEventLoop::class, $event_loop );
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
		$event_loop= new EventLoop( function( $proxy ){
			
			$this->assertInstanceOf( EventLoop\Proxy::class, $proxy );
			$this->assertInstanceOf( EventLoop\IProxy::class, $proxy );
			
			$proxy->push( function()use( $proxy ){
				
				$this->assertInstanceOf( EventLoop\Proxy::class, $proxy );
				$this->assertInstanceOf( EventLoop\IProxy::class, $proxy );
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
		$event_loop= new EventLoop();
		
		$event_loop->push( function()use( $event_loop ){
			
			$this->assertSame( IEventLoop::STATUSES['RUNNING'], $event_loop->status );
			
			$event_loop->push( function()use( $event_loop ){
				
				$this->assertSame( IEventLoop::STATUSES['RUNNING'], $event_loop->status );
			} );
			
		} );
		
		$this->assertSame( IEventLoop::STATUSES['FRESH'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->run();
		
		$this->assertSame( IEventLoop::STATUSES['DONE'], $event_loop->status );
		
		
		$event_loop->close();
		
		$this->assertSame( IEventLoop::STATUSES['CLOSED'], $event_loop->status );
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
		
		$event_loop= new EventLoop();
		
		$event_loop->push( function()use( $event_loop, &$sign ){
			$event_loop->push( function()use( $event_loop, &$sign ){
				$sign= 1;
				
				$event_loop->push( function()use( &$sign ){
					$sign= 4;
				} );
			} );
			
			$event_loop->push( function()use( &$sign ){
				$sign= 2;
			} );
			
			$event_loop->push( function()use( &$sign ){
				$sign= 3;
			} );
			
			$sign= 0;
		} );
		
		$this->assertSame( null, $sign );
		$this->assertSame( IEventLoop::STATUSES['FRESH'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 0, $sign );
		$this->assertSame( IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 1, $sign );
		$this->assertSame( IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 2, $sign );
		$this->assertSame( IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 3, $sign );
		$this->assertSame( IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		
		$event_loop->step();
		
		$this->assertSame( 4, $sign );
		$this->assertSame( IEventLoop::STATUSES['DONE'], $event_loop->status );
		
		
		$event_loop->push( function()use( &$sign ){
			$sign= 5;
		} );
		$this->assertSame( 4, $sign );
		$this->assertSame( IEventLoop::STATUSES['PAUSED'], $event_loop->status );
		
		$event_loop->step();
		$this->assertSame( 5, $sign );
		$this->assertSame( IEventLoop::STATUSES['DONE'], $event_loop->status );
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
		$event_loop= new EventLoop( function(){} );
		
		$event_loop->close();
		
		$this->assertSame( IEventLoop::STATUSES['CLOSED'], $event_loop->status );
		
		
		$this->expectException( Exceptions\Closed::class );
		
		$event_loop->step();
		
		
		$this->expectException( Exceptions\Closed::class );
		
		$event_loop->run();
		
		
		$this->expectException( Exceptions\Closed::class );
		
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
		$event_loop= new EventLoop( function( $proxy ){
			
			$setting_at= microtime( true );
			
			$proxy->setTimeout( function()use( $setting_at ){
				
				$this->assertGreaterThanOrEqual( $setting_at + 0.1, microtime( true ) );
				
			}, 100 );
		} );
	}
	
}
