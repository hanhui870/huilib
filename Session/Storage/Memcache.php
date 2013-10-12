<?php 
namespace HuiLib\Session\Storage;

/**
 * Session Memcache实现
 * 
 * Session通过prefix隔离命名空间
 * 
 * @author 祝景法
 * @since 2013/09/27
 */
class Memcache extends \HuiLib\Session\SessionBase
{
	public function open ( $savePath , $name )
	{
	
	}
	
	public function read ( $sessionId )
	{
		return $this->driver->get(self::$prefix.$sessionId);
	}
	
	public function write ( $sessionId , $sessionData )
	{
		return $this->driver->add(self::$prefix.$sessionId, $sessionData, FALSE, $this->lifeTime);
	}
	
	public function close ()
	{
	
	}
	
	public function destroy ( $sessionId )
	{
	
	}
	
	public function gc ( $maxlifetime )
	{
	
	}
}