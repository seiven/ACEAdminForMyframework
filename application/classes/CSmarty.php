<?php

/**
 * CMyFrame Smarty辅助类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CSmarty
{

    public static function showPageData($thisObject)
    {
        
        // 站点配置
        $siteConfigs = CConfig::getInstance('site')->load('siteName');
        $siteAppendName = CConfig::getInstance('site')->load('siteAppend');
        
        // 页面数据
        $userSetData = isset($thisObject['content']) ? $thisObject['content'] : array();
        
        // 整合
        $title = isset($userSetData['title']) ? $userSetData['title'] : $siteConfigs;
        if (! empty($siteAppendName)) {
            $title .= ' - ' . $siteAppendName;
        }
        
        // 标题
        $titleHtml = '<title>' . $title . '</title>';
        
        // 关键字
        $keywordHtml = '';
        if (isset($userSetData['keyword']) && ! empty($userSetData['keyword'])) {
            $keywordHtml = '<meta name="keywords" content="' . $userSetData['keyword'] . '" />';
        } else {
            $confKey = CConfig::getInstance('site')->load('siteKeyword');
            if (! empty($confKey)) {
                $keywordHtml = '<meta name="keywords" content="' . $confKey . '" />';
            }
        }
        
        // 描述
        $descHtml = '';
        if (isset($userSetData['desc']) && ! empty($userSetData['desc'])) {
            $descHtml = '<meta name="description" content="' . $userSetData['desc'] . '" />';
        } else {
            $confKey = CConfig::getInstance('site')->load('siteDescription');
            if (! empty($confKey)) {
                $descHtml = '<meta name="description" content="' . $confKey . '" />';
            }
        }
        
        // 输出
        $content = $titleHtml . "\r\n" . $keywordHtml . "\r\n" . $descHtml;
        return $content;
    }

    /**
     * 设置基础数据
     */
    static public function setInitData($viewObject)
    {
        $prefix = CConfig::getInstance()->load('ACTION_PREFIX');
        $viewObject->assign('thisUrl', urlencode(CRequest::getUrl()));
        $viewObject->assign('base64Url', CEncrypt::safe_b64encode(CRequest::getUrl()));
        $viewObject->assign('controller', CRequest::getController());
        $viewObject->assign('action', CRequest::getAction());
        $viewObject->assign('actionPre', $prefix);
        $viewObject->assign('ip', CRequest::getIp());
        $viewObject->assign('module', CRequest::getModule());
        $viewObject->assign('time', time());
        $viewObject->assign('sessionID', session_id());
        $viewObject->assign('path', CRequest::getPath());
        $viewObject->assign('staticUrl', CConfig::getInstance('site')->load('staticUrl'));
        $viewObject->assign('uploadStaticUrl', CConfig::getInstance('site')->load('uploadStaticUrl'));
        $viewObject->assign('siteName',CConfig::getInstance('site')->load('siteName'));
    }

    /**
     * 中文截取
     *
     * @param unknown_type $params            
     */
    static public function cn_substr($params)
    {
        $sourcestr = isset($params['str']) ? $params['str'] : '';
        $cutlength = isset($params['l']) ? $params['l'] : 80;
        $etc = isset($params['append']) ? $params['append'] : '...';
        
        $returnstr = '';
        $i = 0;
        $n = 0.0;
        $str_length = strlen($sourcestr); // 字符串的字节数
        while (($n < $cutlength) and ($i < $str_length)) {
            $temp_str = substr($sourcestr, $i, 1);
            $ascnum = ord($temp_str); // 得到字符串中第$i位字符的ASCII码
            if ($ascnum >= 252) {
                // 如果ASCII位高与252
                $returnstr = $returnstr . substr($sourcestr, $i, 6); // 根据UTF-8编码规范，将6个连续的字符计为单个字符
                $i = $i + 6; // 实际Byte计为6
                $n ++; // 字串长度计1
            } elseif ($ascnum >= 248) {
                // 如果ASCII位高与248
                $returnstr = $returnstr . substr($sourcestr, $i, 5); // 根据UTF-8编码规范，将5个连续的字符计为单个字符
                $i = $i + 5; // 实际Byte计为5
                $n ++; // 字串长度计1
            } elseif ($ascnum >= 240) {
                // 如果ASCII位高与240
                $returnstr = $returnstr . substr($sourcestr, $i, 4); // 根据UTF-8编码规范，将4个连续的字符计为单个字符
                $i = $i + 4; // 实际Byte计为4
                $n ++; // 字串长度计1
            } elseif ($ascnum >= 224) {
                // 如果ASCII位高与224
                $returnstr = $returnstr . substr($sourcestr, $i, 3); // 根据UTF-8编码规范，将3个连续的字符计为单个字符
                $i = $i + 3; // 实际Byte计为3
                $n ++; // 字串长度计1
            } elseif ($ascnum >= 192) {
                // 如果ASCII位高与192
                $returnstr = $returnstr . substr($sourcestr, $i, 2); // 根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i = $i + 2; // 实际Byte计为2
                $n ++; // 字串长度计1
            } elseif ($ascnum >= 65 and $ascnum <= 90 and $ascnum != 73) {
                // 如果是大写字母 I除外
                $returnstr = $returnstr . substr($sourcestr, $i, 1);
                $i = $i + 1; // 实际的Byte数仍计1个
                $n ++; // 但考虑整体美观，大写字母计成一个高位字符
            } elseif (! (array_search($ascnum, array(
                37,
                38,
                64,
                109,
                119
            )) === FALSE)) {
                // %,&,@,m,w 字符按１个字符宽
                $returnstr = $returnstr . substr($sourcestr, $i, 1);
                $i = $i + 1; // 实际的Byte数仍计1个
                $n ++; // 但考虑整体美观，这些字条计成一个高位字符
            } else {
                // 其他情况下，包括小写字母和半角标点符号
                $returnstr = $returnstr . substr($sourcestr, $i, 1);
                $i = $i + 1; // 实际的Byte数计1个
                $n = $n + 0.5; // 其余的小写字母和半角标点等与半个高位字符宽...
            }
        }
        if ($i < $str_length) {
            $returnstr = $returnstr . $etc; // 超过长度时在尾处加上省略号
        }
        
        return $returnstr;
    }

    /**
     * 得到一个口语化的时间
     */
    public static function sayTime($params)
    {
        $time = $params['time'];
        
        $now = time();
        
        $toCast = $now - $time;
        
        if ($toCast < 86400) {
            return ceil($toCast / 3600) . '小时前';
        } else {
            return ceil($toCast / 86400) . '天前';
        }
    }

    /**
     * 检查权限
     */
    public static function checkRight($params, $content, &$smarty, &$repeat)
    {
        $controller = isset($params['c']) ? $params['c'] : '';
        $action = isset($params['a']) ? $params['a'] : '';
        
        $hasRight = AdminController::checkRight($controller . '@' . $action);
        
        if (true == $hasRight) {
            return $content;
        } else {
            return null;
        }
    }
}