<?php
namespace HuiLib\Module\Acl;

/**
 * 角色权限模块
 * @author 祝景法
 * @date 2013/06/18
 */
class AccessList extends \HuiLib\Module\ModuleBase
{
	/**
     * 绑定类型
     * role 针对角色的权限绑定
     * staff 针对员工的权限绑定
     */
	private $bindType = '';
	private $relatedid;

	function __construct($bindType, $relatedid)
	{
		$this->bindType = ucfirst ( $bindType );
		$this->relatedid = intval ( $relatedid );
	}

	/**
     * 获取允许权限列表
     */
	public function getAllowed()
	{
		if (! in_array ( $this->bindType, array ('Role', 'User' ) ) || intval ( $this->relatedid ) < 1) {
			return array ();
		}
		
		$method = "get" . ucfirst ( $this->bindType ) . 'Allowed';
		
		return $this->$method ( $this->relatedid );
	}

	/**
     * 获取禁止权限列表
     */
	public function getBanned()
	{
		if (! in_array ( $this->bindType, array ('Role', 'User' ) ) || intval ( $this->relatedid ) < 1) {
			return array ();
		}
		$method = "get" . ucfirst ( $this->bindType ) . 'Banned';
		return $this->$method ( $this->relatedid );
	}

	/**
     * 添加允许权限列表
     */
	public function addAllowed($ids)
	{
		if (! in_array ( $this->bindType, array ('Role', 'User' ) ) || intval ( $this->relatedid ) < 1) {
			return array ();
		}
		
		$method = "add" . ucfirst ( $this->bindType ) . 'Allowed';
		
		return $this->$method ( $ids );
	}

	/**
     * 添加禁止权限列表
     */
	public function addBanned($ids)
	{
		if (! in_array ( $this->bindType, array ('Role', 'User' ) ) || intval ( $this->relatedid ) < 1) {
			return array ();
		}
		$method = "add" . ucfirst ( $this->bindType ) . 'Banned';
		return $this->$method ( $ids );
	}

	/**
     * 计算员工的允许权限
     * @param int $UserID
     */
	private function getUserAllowed($UserID)
	{
		$pList = $this->getUserAllowedPids ( $UserID );
		
		return $this->_staffPrivilegeOrder ( $this->_mapIdsToData ( $pList ) );
	}

	/**
     * 登录初始化，获取用户权限接口
     */
	public function getUserPrivilege($UserID)
	{
		return $this->getUserAllowedPids ( $UserID );
	}

	/**
     * 计算员工的允许权限IDS
     * @param int $UserID
     * 算法：允许的权限+用户组的权限-禁止的权限
     */
	private function getUserAllowedPids($UserID)
	{
		if ($UserID == 1) { //超级用户
			$table = new Application_Model_DbTable_AclResources ();
			return $table->getAllIDs ();
		}
		//单独赋值的权限
		$fd = new Application_Model_DbTable_AclUserPrivileges ();
		$UserP = $fd->getUserAllowedList ( $UserID );
		
		//获取用户组
		$sfd = new Application_Model_DbTable_Users ();
		$User = $sfd->getUser ( $UserID );
		$roles = explode ( ',', $User->RoleIDs );
		$pList = $UserP; //以用户单独定义权限初始化
		foreach ( $roles as $RoleID ) {
			$RoleP = $this->getRoleAllowed ( $RoleID );
			foreach ( $RoleP as $punit ) {
				$pList [$punit ['AclResourceID']] = $punit ['AclResourceID'];
			}
		}
		
		//单独赋值的权限
		$UserBanP = $fd->getUserBannedList ( $UserID );
		foreach ( $UserBanP as $pid ) {
			unset ( $pList [$pid] );
		}
		return $pList;
	}

	/**
     * 计算员工的禁止权限
     * @param int $UserID
     * 算法：所有权限-允许的权限
     */
	private function getUserBanned($UserID)
	{
		$pAllowList = $this->getUserAllowedPids ( $UserID );
		
		$fd = new Application_Model_DbTable_AclResources ();
		$all = $fd->getAllIDs ();
		
		$ids = array_diff ( $all, $pAllowList );
		
		return $this->_staffPrivilegeOrder ( $this->_mapIdsToData ( $ids ) );
	}

	private function getRoleAllowed($RoleID)
	{
		$fd = new Application_Model_DbTable_AclRolePrivileges ();
		$ids = $fd->getRoleAllowedList ( $RoleID );
		
		return $this->_staffPrivilegeOrder ( $this->_mapIdsToData ( $ids ) );
	}

	/**
     * 重新整理员工权限按照Code排序
     */
	private function _staffPrivilegeOrder($data)
	{
		$code = array ();
		foreach ( $data as $key => $item ) {
			$code [$key] = $item ['Code'];
		}
		asort ( $code, SORT_STRING );
		$result = array ();
		foreach ( $code as $key => $value ) {
			$result [] = $data [$key];
		}
		return $result;
	}

	private function getRoleBanned($RoleID)
	{
		$fd = new Application_Model_DbTable_AclRolePrivileges ();
		$ids = $fd->getRoleBannedList ( $RoleID );
		
		return $this->_mapIdsToData ( $ids );
	}

	/**
     * 将权限ID转换为权限数据列表
     * @param array $ids
     */
	private function _mapIdsToData($ids)
	{
		$mod = new Application_Model_DbTable_AclResources ();
		$data = $mod->getAll ();
		
		//返回前端的，JSON格式必须去除主键
		$result = array ();
		foreach ( $ids as $id ) {
			if (empty ( $data [$id] ))
				continue;
			$result [] = $data [$id];
		}
		
		return $result;
	}

	/**
     * 添加计算员工的允许权限IDS
     * @param array $ids
     */
	private function addUserAllowed($ids)
	{
		$ids = $this->formatID ( $ids );
		$fd = new Application_Model_DbTable_AclUserPrivileges ();
		return $fd->addUserAllowed ( $this->relatedid, $ids );
	}

	/**
     * 添加员工的禁止权限
     * @param array $ids
     */
	private function addUserBanned($ids)
	{
		$ids = $this->formatID ( $ids );
		$fd = new Application_Model_DbTable_AclUserPrivileges ();
		return $fd->addUserBanned ( $this->relatedid, $ids );
	}

	/**
     * 添加角色允许操作
     * @param array $ids
     */
	private function addRoleAllowed($ids)
	{
		$ids = $this->formatID ( $ids );
		$fd = new Application_Model_DbTable_AclRolePrivileges ();
		return $fd->addRoleAllowed ( $this->relatedid, $ids );
	}

	/**
     * 添加角色禁止操作， 即删除角色允许操作
     * @param array $ids
     */
	private function addRoleBanned($ids)
	{
		$ids = $this->formatID ( $ids );
		$fd = new Application_Model_DbTable_AclRolePrivileges ();
		return $fd->removeRoleAllowed ( $this->relatedid, $ids );
	}

	private function formatID($ids)
	{
		$result = array ();
		foreach ( $ids as $id ) {
			$id = intval ( $id );
			if ($id <= 0)
				continue;
			$result [$id] = $id;
		}
		
		return $result;
	}

	/**
     * 将某些员工添加到某个角色组
     */
	public function addRoleHave($UserIDs)
	{
		$ids = $this->formatID ( $UserIDs );
		if (empty ( $ids ))
			return false;
		
		$staffTable = new Application_Model_DbTable_Users ();
		foreach ( $ids as $UserID ) {
			$staff = $staffTable->getUser ( $UserID );
			//不存在该员工
			if (empty ( $staff->UserID ))
				continue;
			
			$temp = explode ( ',', $staff->RoleIDs );
			$roles = array ();
			foreach ( $temp as $rid ) {
				$roles [$rid] = $rid;
			}
			$roles [$this->relatedid] = $this->relatedid;
			
			$staff->RoleIDs = implode ( ',', $roles );
			$staff->save ();
		}
		
		return false;
	}

	/**
     * 批量从角色组删除员工
     */
	public function remRoleHave($UserIDs)
	{
		$ids = $this->formatID ( $UserIDs );
		if (empty ( $ids ))
			return false;
		
		$staffTable = new Application_Model_DbTable_Users ();
		foreach ( $ids as $UserID ) {
			$staff = $staffTable->getUser ( $UserID );
			
			//删除，可以非空检测
			if (! empty ( $staff->RoleIDs )) {
				$temp = explode ( ',', $staff->RoleIDs );
				$roles = array ();
				foreach ( $temp as $rid ) {
					$roles [$rid] = $rid;
				}
				
				if (isset ( $roles [$this->relatedid] )) {
					unset ( $roles [$this->relatedid] );
				}
				
				$staff->RoleIDs = implode ( ',', $roles );
				$staff->save ();
			}
		}
		
		return false;
	}
}