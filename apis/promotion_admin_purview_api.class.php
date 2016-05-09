<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 后台权限API
 * @author royalwang
 *
 */
class promotion_admin_purview_api extends Component_Event_Api {
    
    public function call(&$options) {
        $purviews = array(
        	array('action_name' => __('促销商品管理'), 'action_code' => 'promotion_manage',	'relevance'   => ''),
        	array('action_name' => __('添加促销商品'), 'action_code' => 'promotion_add', 		'relevance'   => ''),
        	array('action_name' => __('编辑促销商品'), 'action_code' => 'promotion_update', 	'relevance'   => ''),
        	array('action_name' => __('删除促销商品'), 'action_code' => 'promotion_delete', 	'relevance'   => ''),
        );
        
        return $purviews;
    }
}

// end