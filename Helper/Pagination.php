<?php
namespace HuiLib\Helper;

use HuiLib\Error\Exception;
/**
 * 页码工具辅助类
 * 
 * 默认格式：1 last 3 4 5 6 7 netxt 90
 *
 * @author 祝景法
 * @since 2014/03/05
 */
class Pagination
{
    /**
     * 全局最大显示的页码数
     * 
     * 多了影响效率
     * 
     * @var int
     */
    const MAX_PAGE=500;
    
    /**
     * 页码占位符
     * @var string
     */
    const PAGE_HOLDER="{page}";
    
    /**
     * 当前页面
     * 
     * @var int
     */
    protected $currentPage=NULL;
    
    /**
     * 每页含有的数量
     * 
     * @var int
     */
    protected $perpage=NULL;
    
    /**
     * 中间部分显示的页码范围
     * 
     * @var int
     */
    protected $pageRange=5;
    
    /**
     * 数据总量
     * 
     * @var int
     */
    protected $itemCount=NULL;
    
    /**
     * 最大页码 
     * 
     * 根据实际数量和系统最大常量计算
     *
     * @var int
     */
    protected $maxPage=NULL;
    
    /**
     * 当前页面
     * 
     * 通过{page}替换页码。1的时候默认删除page删除。
     * 
     * @var int
     */
    protected $baseUri=NULL;

    //上一页的文字
    protected $lastText='&laquo;';
    //下一页的文字
    protected $nextText='&raquo;';

    /**
     * Select对象
     * @var \HuiLib\Db\Query\Select
     */
    protected $select = NULL;

    public function __construct()
    {
    }
    
    /**
     * 通过Select对象初始化
     * 
     * 需要初始化的字段：setPerpage setCurrentPage setItemCount
     *
     * @param \HuiLib\Db\Query\Select $select
     * @return \HuiLib\Db\RowSet
     */
    public function initBySelect(\HuiLib\Db\Query\Select $select)
    {
        $this->select = $select;
        $this->setPerpage($select->getLimit());
        
        $offset=$select->getOffset();
        $currentPage=$this->perpage ? ceil($offset/$this->perpage)+1 : 1;
        $this->setCurrentPage($currentPage);
        
        $this->setItemCount($select->getItemCount());
        
        return $this;
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
    
    /**
     * 设置每页数量
     *
     * @param int $perpage
     * @return \HuiLib\Helper\Pagination
     */
    public function setPerpage($perpage)
    {
        if ($perpage<=0) {
            return FALSE;
        }
        $this->perpage=$perpage;
    
        return $this;
    }
    
    /**
     * 设置页码链接
     *
     * @param string $baseUri
     * @return \HuiLib\Helper\Pagination
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri=$baseUri;
    
        return $this;
    }
    
    protected function getBaseUri()
    {
        if ($this->baseUri===NULL) {
            throw new Exception('Pagination baseUri has not been set.');
        }
    
        return $this->baseUri;
    }
    
    /**
     * 获取基本页码网址
     * 
     * /discuss/2/{page}
     * /discuss/2?page={page}
     * 
     * 首页都需要能够处理
     * 
     * @param int $page
     * @return number
     */
    public function getPageUri($page)
    {
        if ($page>1) {
            return str_ireplace(self::PAGE_HOLDER, $page, $this->getBaseUri());
        }
        return preg_replace('/\/?(\??&?page\=)?'.preg_quote(self::PAGE_HOLDER, '/').'/is', '', $this->getBaseUri());
    }
    
    /**
     * 设置页码链接
     *
     * @param string $baseUri
     * @return \HuiLib\Helper\Pagination
     */
    public function setPageRange($pageRange)
    {
        if ($pageRange<=0) {
            return FALSE;
        }
        
        $this->pageRange=$pageRange;
    
        return $this;
    }
    
    public function getPageRange()
    {
        return $this->pageRange;
    }
    
    protected function getRangeMiddle()
    {
        return floor($this->pageRange/2);
    }
    
    /**
     * 获取中间循环开始
     * @return number
     */
    public function getRangeStart()
    {
        $rangeMiddle=$this->getRangeMiddle();
        $rangeLeft=$this->pageRange-$rangeMiddle;
        
        if ($this->currentPage>$rangeMiddle) {
            if ($this->getMaxPage()>$this->pageRange && $this->getMaxPage()-$this->currentPage<$rangeLeft){
                //后部触顶
                return $this->maxPage-$this->pageRange+1;
            }
            return $this->currentPage-$rangeMiddle;
        }else{
            //当前落在中前部
            return 1;
        }
    }
    
    /**
     * 获取中间循环截止
     * @return number
     */
    public function getRangeEnd()
    {
        $rangeMiddle=$this->getRangeMiddle();
        $rangeLeft=$this->pageRange-$rangeMiddle;

        if ($this->getMaxPage()-$this->currentPage>$rangeLeft) {
            if ($this->pageRange>1 && $this->currentPage<$rangeLeft){
                //前部触顶
                return $this->pageRange;
            }
            return $this->currentPage+$rangeLeft-1;//floor  需要多减1
        }else{
            //当前落在中后部
            return $this->getMaxPage();
        }
    }
    
    public function getLastText()
    {
        return $this->lastText;
    }
    
    public function getNextText()
    {
        return $this->nextText;
    }
    
    /**
     * 获取上一页的页码
     * 
     * @return Int
     */
    public function getLastPage()
    {
        if ($this->getCurrentPage()>1) {
            return $this->getCurrentPage()-1;
        }else{
            return FALSE;
        }
    }
    
    /**
     * 获取下一页的页码
     *
     * @return Int
     */
    public function getNextPage()
    {
        if ($this->getCurrentPage()<$this->getMaxPage()) {
            return $this->getCurrentPage()+1;
        }else{
            return FALSE;
        }
    }
    
    /**
     * 获取下一页的页码
     *
     * @return Int
     */
    public function getCurrentPage()
    {
        if ($this->currentPage===NULL) {
            throw new Exception('Pagination currentPage has not been set.');
        }
        
        return $this->currentPage;
    }
    
    /**
     * 获取第一页ID
     * @return number
     */
    public function getFirst()
    {
        if ($this->getRangeStart()>1) {
            return 1;
        }else{
            return FALSE;
        }
    }
    
    /**
     * 获取最末页ID
     * @return number
     */
    public function getEnd()
    {
        if ($this->getRangeEnd()<$this->getMaxPage()) {
            return $this->getMaxPage();
        }else{
            return FALSE;
        }
    }
    
    public function getMaxPage()
    {
        if ($this->maxPage===NULL) {
            throw new Exception('Pagination maxPage has not been set, is related with itemCount and perPage.');
        }
        
        return $this->maxPage;
    }
    
    /**
     * 设置数据集数量
     *
     * @param int $itemCount
     * @return \HuiLib\Helper\Pagination
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount=$itemCount;
        $max=$itemCount>=$this->perpage ? ceil($itemCount/$this->perpage) : 1;
        $this->maxPage=$max <= self::MAX_PAGE ? $max : self::MAX_PAGE;
    
        return $this;
    }
    
    /**
     * 获取数据集数量
     *
     * @return int
     */
    public function getItemCount()
    {
        if ($this->itemCount===NULL) {
            throw new Exception("itemCount has not been set.");
        }
        return $this->itemCount;
    }

    public static function create()
    {
        return new self();
    }
}