<?php
/**
 * CMyFrame 控制器基类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
 */
class CController {
	/**
	 * 视图对象
	 */
	protected $viewObject;
	
	/**
	 * 页面信息
	 */
	public $pageInfo = array ();
	
	/**
	 * 获取参数
	 */
	protected function Args($key, $type = 'string', $from = null, $noFilter = null) {
		return CRequest::Args ( $key, $type, $from, $noFilter );
	}
	
	/**
	 * 创建URL
	 */
	protected function createUrl($a, $c, $params = array()) {
		
		// 将参数合并传给请求类生成URL
		$params ['c'] = $c;
		$params ['a'] = $a;
		return CRequest::createUrl ( $params );
	}
	
	/**
	 * 获取视图对象
	 */
	protected function getView() {
		return CView::factory ( 'smarty' );
	}
	
	/**
	 * 驱动模板
	 */
	protected function display($templateName = null, $isCache = false, $num = '') {
		
		// 确定模板名称
		if (false == stripos ( $templateName, '.html' )) {
			$templateName = $templateName . '.html';
		}
		if (isset ( $this->layout ) && ! empty ( $this->layout ) && false == stripos ( $this->layout, '.html' )) {
			$this->layout = $this->layout . '.html';
		} else {
			$this->layout = null;
		}

		// 编译模板时 获取视图对象
		$viewObject = $this->getView ();
		
		// 没有布局模板时则置空
		if (null != $templateName) {
			if (false == $isCache) {
				$viewObject->clear_cache ( "layout/" . $this->layout, $templateName . $num ); // 清除缓存
				$viewObject->clear_cache ( $templateName, $templateName . $num );
			}
			
			// 装载模板
			if ($viewObject->template_exists ( $templateName )) {
				$viewObject->assign ( 'pageInfo', $this->pageInfo );
				if (isset ( $this->layout ) && $this->layout != '') {
					$viewObject->assign ( 'CONTENT_INSERET_LAYOUT', $templateName );
					$viewObject->display ( "layout/" . $this->layout, $templateName . $num );
				} else {
					$viewObject->display ( $templateName, $templateName . $num );
				}
			} else {
				throw new CViewException ( '[视图错误]使用的视图文件不存在 : ' . $templateName );
			}
		}
		// 调用访问视图后的钩子函数
		CHooks::callHooks ( HOOKS_VIEW_SHOW, $templateName );
	}
	
	/**
	 * 输出模板
	 */
	protected function fetch($templateName = null) {
		
		// 确定模板名称
		if (false == stripos ( $templateName, '.html' )) {
			$templateName = $templateName . '.html';
		}
		
		// 编译模板时 获取视图对象
		$viewObject = $this->getView ();
		
		if ($viewObject->template_exists ( $templateName )) {
			return $viewObject->fetch ( $templateName );
		} else {
			throw new CViewException ( '[视图错误]使用的视图文件不存在 : ' . $templateName );
		}
	}
	
	/**
	 * 手动赋值方法
	 */
	protected function assign() {
		
		// 编译模板时 获取视图对象
		$viewObject = $this->getView ();
		
		if (2 == func_num_args ()) {
			$key = func_get_arg ( 0 );
			$value = func_get_arg ( 1 );
			
			$viewObject->assign ( $key, $value );
		} else if (1 == func_num_args () && is_array ( func_get_arg ( 0 ) )) {
			
			$valArr = func_get_arg ( 0 );
			$viewObject->assign ( $valArr );
		}
	}
	
	/**
	 * 设置标题
	 */
	protected function setTitle($title = '') {
		
		// 设置页面信息
		$this->pageInfo ['title'] = $title;
	}
	
	/**
	 * 设置关键字
	 */
	protected function setKeyword($keyword = '') {
		
		// 设置页面信息
		$this->pageInfo ['keyword'] = $keyword;
	}
	
	/**
	 * 设置描述信息
	 */
	protected function setDescription($desc) {
		
		// 设置页面信息
		$this->pageInfo ['desc'] = $desc;
	}
	
	/**
	 * 设置页面信息
	 */
	protected function setPageData($title = '', $keyword = '', $desc = '') {
		$this->setTitle ( $title );
		
		$this->setKeyword ( $keyword );
		
		$this->setDescription ( $desc );
	}
	
	/**
	 * 魔术函数 赋值
	 */
	public function __set($name, $value) {
		
		// 编译模板时 获取视图对象
		$viewObject = $this->getView ();
		
		if (is_object ( $viewObject )) {
			
			$viewObject->assign ( $name, $value );
		} else {
			$this->$name = $value;
		}
		
		return @$this->$name = $value;
	}
}