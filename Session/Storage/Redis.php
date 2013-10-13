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
	public function open ( $savePath , $name )
	{
		parent::open($savePath, $name);
		
		return true;
	}
	
	public function read ( $sessionId )
	{
		parent::read($sessionId);
		return $this->driver->get(self::$prefix.$sessionId);
	}
	
	public function write ( $sessionId , $sessionData )
	{
		parent::write($sessionId, $sessionData);
		return $this->driver->add(self::$prefix.$sessionId, $sessionData, $this->lifeTime);
	}
	
	public function close ()
	{
		parent::close();
		
		return true;
	}
	
	public function destroy ( $sessionId )
	{
		parent::destroy($sessionId);
		
		return true;
	}
}