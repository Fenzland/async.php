#!/usr/bin/env php
<?php

error_reporting(E_ALL);
ini_set( 'display_errors', 'on' );

require __DIR__.'/vendor/autoload.php';

$event_loop= new Async\EventLoop();

// $promise= new Async\Promise( $event_loop, function($a,$b){
// 	$a( 'value-value' );
// } );

// z( 'ready' );
// $promise->then( function($v){
// 	z( $v );
// } );
// z( 'ok' );

$async= new Async\Async( $event_loop );

$a= 1;
z( '1' );
$async->run( function()use( &$a ){
z( '2' );
	$a=2;
z( '3' );
	$a= yield 3;
z( '5' );
} );
z( '4' );

z( 2===$a );

$event_loop->push( function()use( $a ){
	z( 9===$a );
} );

$event_loop->run();
return;

$a= function(){
	
	yield 0;
	yield 0;
	return 0;
};
$g= $a();
var_dump( $g->current() );
var_dump( $g->send(1) );
var_dump( $g->valid()?$g->current():$g->getReturn() );
var_dump( $g->send(1) );
var_dump( $g->valid()?$g->current():$g->getReturn() );


return;
use Async\EventLoop;

$el= new EventLoop( function( $eventLoop ){
	echo "main function start\n";
	
	echo "push the secondary process\n";
	$eventLoop->setTimeout( function(){
		echo "secondary process called\n";
	}, 1000 );
	echo "secondary process pushed\n";
	echo "main end\n";
	
	$promise= $eventLoop->promise( function( $resolve, $reject ){
		// ...
		$resolve();
	} );
	
	$asyncFunc= $eventLoop->async( function()use( $eventLoop ){
		
		$value= yield $eventLoop->promise();
		
		return $value;
	} );
	
	$promise= $asyncFunc();
	
	$eventLoop->asyncCall( function()use( $eventLoop ){
		// ...
	} );
} );

exit;

class Promise
{
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * 
	 * @param  callable $job
	 */
	public function __construct( callable$job )
	{
		
	}
	
	/**
	 * Method then
	 * 
	 * @access public
	 * 
	 * @param  callable $onResolve
	 * @param  callable $onReject
	 * 
	 * @return self
	 */
	public function then( callable$onResolve, callable$onReject ):self
	{
		#
	}
	
}

function request()
{
	return new Promise( function( $resolve, $reject ){
		`./sleep.php`;
		$resolve();
	} );
}

function async( callable$genFunc )
{
	return function()use( $genFunc ){
		$gen= $genFunc();
		$awaitFor= $gen->current();
		if( is_object( $awaitFor ) && method_exists( $awaitFor, 'then' ) )
		{
			$awaitFor->then( function( $value ){
				$gen->send( $value );
			} );
		}
		else
		{
			$gen->send( $awaitFor );
		}
	};
}

async(function(){
	
});

