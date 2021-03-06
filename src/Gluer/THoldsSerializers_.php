<?php

declare( strict_types= 1 );

namespace Fenzland\HttpApiGluer\Gluer;

use Fenzland\HttpApiGluer\serializers as S;

////////////////////////////////////////////////////////////////

trait THoldsSerializers_
{

	/**
	 * Static method registerSerializer_
	 *
	 * @static
	 *
	 * @access public
	 *
	 * @param  string $mime
	 * @param  class|S\ASerializer $serializer
	 *
	 * @return class
	 */
	static public function registerSerializer_( string$mime, $serializer ):string
	{
		static::checkSerializerNotRegistered_( $mime );

		static::checkSerializer_( $serializer );

		static::$serializers_[$mime]= $serializer;

		return self::class;
	}

	/**
	 * Static method getSerializer_
	 *
	 * @static
	 *
	 * @access public
	 *
	 * @param  string $mime
	 *
	 * @return mixed
	 */
	static public function getSerializer_( string$mime )
	{
		return static::$serializers_[$mime]??null;
	}

	/**
	 * Static method getSerializers_
	 *
	 * @static
	 *
	 * @access public
	 *
	 * @return array
	 */
	static public function getSerializers_():array
	{
		return static::$serializers_;
	}

	/**
	 * Static var serializers
	 *
	 * @static
	 * @access protected
	 *
	 * @var    array
	 */
	static protected $serializers_= [
		'application/octet-stream'=> S\Raw::class,
		'application/php-serialized'=> S\PHPSerialized::class,
		'application/json'=> S\JSON::class,
		'application/x-www-form-urlencoded'=> S\Form::class,
	];

	/**
	 * Static method checkSerializer_
	 *
	 * @static
	 *
	 * @access protected
	 *
	 * @param  mixed $serializer
	 *
	 * @return void
	 */
	static protected function checkSerializer_( $serializer ):void
	{
		if(!(
			(
				is_object( $serializer )
			&&
				$serializer instanceof S\ASerializer
			)
		or
			(
				is_string( $serializer )
			&&
				class_exists( $serializer )
			&&
				isset( class_parents( $serializer )[S\ASerializer::class] )
			)
		)){
			$caller= debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1];

			throw new \TypeError(
				"Argument 2 passed to {$caller['class']}{$caller['type']}{$caller['function']}() must be an class extends ".S\ASerializer::class.' or it\'s instance'
			);
		}
	}

	/**
	 * Static method checkSerializerNotRegistered_
	 *
	 * @static
	 *
	 * @access protected
	 *
	 * @param  string $mime
	 *
	 * @return void
	 */
	static protected function checkSerializerNotRegistered_( string$mime ):void
	{
		if( isset( static::$serializers_[$mime] ) )
		{
			throw new \Exception(
				"Content type $mime is already registered."
			);
		}
	}

	/**
	 * Static method checkSerializerSupported_
	 *
	 * @static
	 *
	 * @access protected
	 *
	 * @param  string $mime
	 *
	 * @return void
	 */
	static protected function checkSerializerSupported_( string$mime ):void
	{
		if(!( isset( static::$serializers_[$mime] ) ))
		{
			throw new \Exception(
				"Content type $mime is not supported. Please register serialize first."
			);
		}
	}

	/**
	 * Static method achieveSerializer_
	 *
	 * @static
	 *
	 * @access protected
	 *
	 * @param  string $mime
	 *
	 * @return S\ASerializer
	 */
	static protected function achieveSerializer_( string$mime ):S\ASerializer
	{
		static::checkSerializerSupported_( $mime );

		$serializer= static::getSerializer_( $mime );

		return static::instantiateSerializer_( $serializer );
	}

	/**
	 * Static method instantiateSerializer_
	 *
	 * @static
	 *
	 * @access protected
	 *
	 * @param  mixed $serializer
	 *
	 * @return S\ASerializer
	 */
	static protected function instantiateSerializer_( $serializer ):S\ASerializer
	{
		return is_object( $serializer )? $serializer : new $serializer;
	}

}
