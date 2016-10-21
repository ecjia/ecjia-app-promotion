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
        if ($_SESSION['admin_id'] <= 0 && $_SESSION['staff_id'] <= 0) {
            return new ecjia_error(100, 'Invalid session');
        }
		$priv = $this->admin_priv('goods_manage');
		if (is_ecjia_error($priv)) {
			return $priv;
		}
		$id = $this->requestData('goods_id', '0');
		if ($id <= 0) {
			return new ecjia_error(101, '参数错误');
		}

		$promotion_info = RC_Model::Model('goods/goods_model')->promote_goods_info($id);
		/* 多商户处理*/
		if (isset($_SESSION['store_id']) && $_SESSION['store_id'] > 0 && $promotion_info['store_id'] != $_SESSION['store_id']) {
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
