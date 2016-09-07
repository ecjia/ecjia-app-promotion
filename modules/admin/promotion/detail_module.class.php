<?php
use Doctrine\Common\Persistence\ObjectManager;
defined('IN_ECJIA') or exit('No permission resources.');

/**
 *促销商品信息
 * @author 
 *
 */
class detail_module extends api_admin implements api_interface {
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
			EM_Api::outPut(101);
		}
	
		$result = RC_Model::Model('goods/goods_model')->promote_goods_info($id);
		
		/* 多商户处理*/
		if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] > 0 && $result['seller_id'] != $_SESSION['seller_id']) {
			EM_Api::outPut(8);
		}
		
		if (!empty($result)) {
			$privilege = 3;
			return array('data' => $result, 'pager' => null, 'privilege' => $privilege);
			
		} else {
			EM_Api::outPut(13);
		}
	}
}
// end