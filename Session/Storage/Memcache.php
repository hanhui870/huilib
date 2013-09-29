<?php 
namespace HuiLib\Session\Storage;

/**
 * Session Memcache实现
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
		return $this->driver->get($sessionId);
	}
	
	public function write ( $sessionId , $sessionData )
	{
		return $this->driver->add($sessionId, $sessionData, FALSE, $this->lifeTime);
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