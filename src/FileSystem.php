<?php

declare( strict_types= 1 );

namespace Async;

////////////////////////////////////////////////////////////////

class FileSystem
{
	
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
		$handle= fopen( $file_name, 'r' );
		stream_set_blocking( $handle, false );
		
		$this->_event_loop->push( function(){
			
		} );
	}
	
}
