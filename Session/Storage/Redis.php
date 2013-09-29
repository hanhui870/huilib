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
	protected $lifeTime=0;
	
	protected function __construct($driverConfig)
	{
		$this->driver=\HuiLib\Cache\CacheBase::create($driverConfig);
		if (! $this->driver instanceof \HuiLib\Cache\CacheBase) {
			throw new \HuiLib\Error\Exception ( 'Session cache driver initialized failed' );
		}
		
		$life=intval(ini_get('session.cookie_lifetime'));
		if ($life>0) {
			$this->lifeTime=$life;
		}
	}
	
	public function open ( $savePath , $name )
	{
	}
	
	public function read ( $sessionId )
	{
		return $this->driver->get($sessionId);
	}
	
	public function write ( $sessionId , $sessionData )
	{
		return $this->driver->add($sessionId, $sessionData, $this->lifeTime);
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