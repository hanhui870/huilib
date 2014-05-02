<?php
namespace HuiLib\Module\Runtime;

/**
 * TODO 通过程序自动检测，cron脚本是否有新版本，避免kill
 * 
 * 来自Dcoin项目时的开发优化经验，需要改成非静态版本的
 * 
 * @author 祝景法
 * @since 2013/09/24
 */
class DaemonRelease extends  \HuiLib\Module\ModuleBase
{
    /**
     * 新版本检测
     */
    public function newReleaseCheck()
    {
        //非cli不用检测
        if ((php_sapi_name() != 'cli')) {
            return true;
        }
    
        //检测时效 单位时间内不用重复检测
        if (time()<self::$lastReleaseCheck+self::RELEASE_CHECK_INTERVAL){
            return true;
        }
    
        $release=self::getRelease();
        if (self::$lastRelease===NULL){
            //第一次是初始化上次版本，然后直接返回
            self::$lastRelease=$release;
            return TRUE;
        }
    
        self::$lastReleaseCheck=time();
    
        //可以活性设置生效时间
        if (!empty($release) && strtotime($release)!=strtotime(self::$lastRelease) && strtotime($release)<time()){
            ////////////发现新版本，自动更新/////////////
            $message="!!!!!NOTICE!!!!!: A new release:".$release." found, will exit for apply new code.";
            if (self::$instance!==NULL && is_array(self::$instance)){
                foreach (self::$instance as $log){
                    $log->add($message);
                    $log->flush();
                }
            }else{
                $log=Transaction_Log_Error::getInstance();
                $log->add($message);
                $log->flush();
            }
    
            exit();
        }
    
        return TRUE;
    }
}