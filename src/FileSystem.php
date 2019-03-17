<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

class FileSystem
{
	use TWithEventLoop;
	
	/**
	 * Method read
	 * 
	 * @access public
	 *
	 * @param  string $file_name
	 * 
	 * @return Promise
	 */
	public function read( string$file_name ):Promise
	{
		return new Promise( $this->_event_loop, function( $resolve, $reject ){
			$handle= fopen( $file_name, 'r' );
			stream_set_blocking( $handle, false );
			
			$content= '';
			
			$this->_event_loop->push( function()use( &$content, $resolve ){
				$line= fgets( $handle );
				if( false !== $line )
					$content.= $line;
				
				if( feof( $handle ) )
					return function()use( $content ){
						$resolve( $content );
					};
				else
					return EventLoop::AGAIN;
			} );
		} );
	}
	
}
