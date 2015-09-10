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

    protected function displayList($model, $where)
    {
        // 用户列表
        $list = CModel::factory($model)->getList($where);
        $this->assign('list', $list);
        
        // 分页
        $count = CModel::factory($model)->getCount($where);
        if (! empty($list)) {
            $pageObject = new Pagination($count, CModel::factory($model)->pageRows);
            $pagestr = $pageObject->fpage(array(
                3,
                4,
                5,
                6,
                7
            ));
            $this->assign('page', $pagestr);
        }
        $this->assign('count', $count);
        $this->assign('where', $where);
        $this->display();
    }
}