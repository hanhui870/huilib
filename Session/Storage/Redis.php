<?php 
namespace HuiLib\Session\Storage;

/**
 * Session Redis实现
 * 
 * @author 祝景法
 * @since 2013/09/27
 */
class Redis extends \HuiLib\Session\SessionBase
{
	protected function __construct($driverConfig)
	{
		$this->driver=\HuiLib\Cache\CacheBase::create($driverConfig);
		if (! $this->driver instanceof \HuiLib\Cache\CacheBase) {
			throw new \HuiLib\Error\Exception ( 'Session cache driver initialized failed' );
		}
	}
	
	public function open ( $savePath , $name )
	{
		echo 444;
	}
	
	public function read ( $sessionId )
	{
		echo 222;
	}
	
	public function write ( $sessionId , $sessionData )
	{
		echo 111;
	}
	
	public function close ()
	{
		echo 999;
	}
	
	public function destroy ( $sessionId )
	{
		echo 666;
	}
	
	public function gc ( $maxlifetime )
	{
		echo 555;
	}
}