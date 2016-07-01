<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 促销商品删除(取消商品促销活动)
 * @author will
 *
 */
class delete_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$priv = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($priv)) {
			$privilege = 0;
			EM_Api::outPut(array(), null, $privilege);
		}
		$id = _POST('goods_id', '0');
		if ($id <= 0) {
			EM_Api::outPut(101);
		}
		
		$promotion_info = RC_Model::Model('goods/goods_model')->promote_goods_info($id);
		/* 多商户处理*/
		if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] > 0 && $promotion_info['seller_id'] != $_SESSION['seller_id']) {
			EM_Api::outPut(8);
		}
		
		if (empty($promotion_info)) {
			EM_Api::outPut(13);
		}
		
		$goods_name = $promotion_info['goods_name'];
		$result = RC_Model::Model('goods/goods_model')->promotion_remove($id);
		RC_Loader::load_app_func('global', 'promotion');
		assign_adminlog_content();
		ecjia_admin::admin_log($goods_name, 'remove', 'promotion');
		return array();
	}
}
// end