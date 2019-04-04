<?php

declare( strict_types= 1 );

use Async\Async;
use Async\Promise;
use Async\EventLoop;
use PHPUnit\Framework\TestCase;

////////////////////////////////////////////////////////////////

class AsyncTest extends TestCase
{
	
	/**
	 * Method testRun
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function mestRunSync()
	{
		$event_loop= new EventLoop();
		
		$async= new Async( $event_loop );
		
		$payload= new stdClass;
		
		$payload->num= 1;
		
		$async->run( function()use( $payload ){
			$payload->num= 2;
		} );
		
		$this->assertSame( 2, $payload->num );
	}
	
	/**
	 * Method testRunAsyncWithOneYield
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function mestRunAsyncWithOneYield()
	{
		$event_loop= new EventLoop();
		
		$async= new Async( $event_loop );
		
		$payload= new stdClass;
		
		$payload->num= 1;
		
		$async->run( function()use( $payload ){
			$payload->num= 2;
			$payload->num= yield 3;
		} );
		
		$this->assertSame( 2, $payload->num );
		
		$event_loop->push( function()use( $payload ){
			$this->assertSame( 3, $payload->num );
		} );
		
		$event_loop->run();
	}
	
	/**
	 * Method testRunAsyncWithTweYield
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function mestRunAsyncWithTweYield()
	{
		$event_loop= new EventLoop();
		
		$async= new Async( $event_loop );
		
		$payload= new stdClass;
		
		$payload->num= 1;
		
		$async->run( function()use( $payload ){
			$payload->num= 2;
			$payload->num= yield 3;
			$payload->num= yield 4;
		} );
		
		$this->assertSame( 2, $payload->num );
		
		$event_loop->push( function()use( $payload, $event_loop ){
			$this->assertSame( 3, $payload->num );
			
			$event_loop->push( function()use( $payload ){
				$this->assertSame( 4, $payload->num );
			} );
		} );
		
		$event_loop->run();
	}
	
	/**
	 * Method testRunAsyncWithReturn
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function mestRunAsyncWithReturn()
	{
		$event_loop= new EventLoop();
		
		$async= new Async( $event_loop );
		
		$payload= new stdClass;
		
		$payload->num= 1;
		
		$async->run( function()use( $payload ){
			$payload->num= 2;
			$payload->num= yield 3;
			$payload->num= yield 4;
			return 5;
		} )->then( function( $value ){
			$this->assertSame( 5, $value );
		} );
		
		$event_loop->run();
	}
	
	/**
	 * Method testRunAsyncWithPromise
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testRunAsyncWithPromise()
	{
		$event_loop= new EventLoop();
		
		$async= new Async( $event_loop );
		
		$payload= new stdClass;
		
		$payload->num= 1;
		
		$async->run( function()use( $payload, $event_loop ){
			$payload->num= 2;
			$payload->num= yield new Promise( $event_loop, function( $resolve, $reject )use( $payload ){
				$payload->resolve= $resolve;
				$payload->reject= $reject;
			} );
			
			return 5;
		} );
		
		$event_loop->push( function()use( $payload, $event_loop ){
			$this->assertSame( 2, $payload->num );
			
			($payload->resolve)( 3 );
			
			$event_loop->push( function()use( $payload ){
				$this->assertSame( 3, $payload->num );
			} );
		} );
		
		$event_loop->run();
	}
	
}
