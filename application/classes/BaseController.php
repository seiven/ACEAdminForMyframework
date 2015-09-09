<?php

class BaseController extends CController
{

    public $layout = 'layout_main';

    public function display($templateName = '', $isCache = false, $num = '')
    {
        if (empty($templateName))
            $templateName = CRequest::getController() . '/' . str_replace(CConfig::getInstance()->load('ACTION_PREFIX'), '', CRequest::getAction());
        
        parent::display($templateName, $isCache, $num);
    }

    public $_assign_ajax_param = array();

    public function assignAjax($k, $v)
    {
        $this->_assign_ajax_param[$k] = $v;
    }

    public function displayAjax($status = false, $message = '操作成功')
    {
        $result = array(
            'status' => $status,
            'message' => $message
        );
        die(json_encode(array_merge($this->_assign_ajax_param, $result)));
    }

    /**
     *
     * @param string $status            
     * @param unknown $urls            
     * @param number $sec            
     * @param string $title            
     * @param string $message            
     */
    public function displayRedirect($status = true, $urls = array(), $sec = 0, $title = null, $message = null)
    {
        $this->assign('status', $status);
        $this->assign('urls', $urls);
        if ($title)
            $this->assign('title', $title);
        if ($message)
            $this->assign('message', $message);
        $this->display('alert/redirect');
        exit();
    }
}