<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 后台促销菜单API
 * @author royalwang
 *
 */
class promotion_admin_menu_api extends Component_Event_Api {
	
	public function call(&$options) {	

		$menus 		= ecjia_admin::make_admin_menu('03_promotion', __('促销管理'), '', 3);
		$submenus 	= array(
			ecjia_admin::make_admin_menu('promotion_manage', __('促销商品'), RC_Uri::url('promotion/admin/init'), 1)->add_purview('promotion_manage'),
		    
// 		    ecjia_admin::make_admin_menu('05_favourable_list', __('促销管理'), RC_Uri::url('promotion/admin/init'), 101)->add_purview('promotion_manage'),
		);
		
		$menus->add_submenu($submenus);
		$menus = RC_Hook::apply_filters('promotion_admin_menu_api', $menus);
		
		if ($menus->has_submenus()) {
		    return $menus;
		}
		
		return false;
	}
}

// end