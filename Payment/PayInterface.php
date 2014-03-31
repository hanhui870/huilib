<?php
namespace HuiLib\Payment;

interface PayInterface
{
    /**
     * 添加一条账户明细
     */
    public function addDetail();
    
    /**
     * 更新账户明细记录
     */
    public function updateDetail();
    
    /**
     * 更新账户余额
     */
    public function updateBalance();
    
    /**
     * 获取账户余额
     */
    public function getBalance();
}