<?php

declare( strict_types= 1 );

use Async\Promise;
use Async\IPromise;
use Async\EventLoop;
use PHPUnit\Framework\TestCase;

////////////////////////////////////////////////////////////////

class PromiseTest extends TestCase
{
	
	/**
	 * Method testConstructor
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testConstructor()
	{
		$event_loop= new EventLoop();
		
		$payload= new stdClass();
		
		$payload->num= 1;
		
		$promise= new Promise( $event_loop, function( $resolve, $reject )use( $payload ){
			$this->assertIsCallable( $resolve );
			$this->assertIsCallable( $reject );
			
			$payload->num= 2;
		} );
		
		$this->assertInstanceOf( IPromise::class, $promise );
		$this->assertSame( 2, $payload->num );
	}
	
	/**
	 * Method testStaticResolve
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function testStaticResolve()
	{
		$event_loop= new EventLoop();
		
		$payload= new stdClass();
		
		$payload->num= 1;
		
		$promise= Promise::resolve( $event_loop, 8 );
		
		$promise->then( function( $value )use( $payload ){
			
			$this->assertSame( 8, $value );
		} );
		
		$this->assertInstanceOf( IPromise::class, $promise );
	}
	
}
