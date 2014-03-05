<?php
namespace HuiLib\Helper;

/**
 * TODO 页码工具辅助类
 *
 * @author 祝景法
 * @since 2014/03/05
 */
class Pagination
{
    /**
     * 当前页面
     * @var int
     */
    protected $currentPage=NULL;
    
    /**
     * 每页含有的数量
     * @var int
     */
    protected $perpage=0;
    
    /**
     * 当前页面
     * @var int
     */
    protected $baseUri=NULL;
    
    
    
    public function __construct()
    {
        
    }
    
    /**
     * 设置当前页码
     * 
     * @param int $page
     * @return \HuiLib\Helper\Pagination
     */
    public function setCurrentPage($page)
    {
        if ($page<=0) {
            return FALSE;
        }
        $this->currentPage=$page;
        
        return $this;
    }
    
    
    public static function create()
    {
        return new self();
    }
}