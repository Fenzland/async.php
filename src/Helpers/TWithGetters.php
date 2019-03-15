<?php

declare( strict_types= 1 );

namespace Async\Helpers;

////////////////////////////////////////////////////////////////

trait TWithGetters
{
	
	/**
	 * If you access a non-exists attribute like this $obj->foo, will find and call __get__foo
	 * 
	 * @access public
	 *
	 * @param  string $attribute
	 * 
	 * @return mixed
	 */
	public function __get( string$attribute )
	{
		$getter= "__get__$attribute";
		
		if( is_callable( [ $this, $getter ] ) )
			return $this->$getter();
		else
		if( get_parent_class( self::class ) && is_callable( [ $this, parent::__get, ] ) )
			return parent::__get( $attribute );
		else
			return null;
	}
	
}