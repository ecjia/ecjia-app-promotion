<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 促销商品删除(取消商品促销活动)
 * @author will
 *
 */
class delete_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$priv = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($priv)) {
			$privilege = 0;
			return array('data' => array(), 'pager' => null, 'privilege' => $privilege);
		}
		$id = $this->requestData('goods_id', '0');
		if ($id <= 0) {
			return new ecjia_error(101, '参数错误');
		}
		
		$promotion_info = RC_Model::Model('goods/goods_model')->promote_goods_info($id);
		/* 多商户处理*/
		if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] > 0 && $promotion_info['seller_id'] != $_SESSION['seller_id']) {
			return new ecjia_error(8, 'fail');
		}
		
		if (empty($promotion_info)) {
			return new ecjia_error(13, '不存在的信息');
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