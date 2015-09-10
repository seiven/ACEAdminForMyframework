<?php
/**
 * 块级元素不缓存插件
 * @author ChenChao
 * @param $args
 * @param $content
 */
function smarty_block_nocache($args,$content)
{
    return $content;
}
?>
