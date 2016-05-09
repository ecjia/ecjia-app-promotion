<?php
use Doctrine\Common\Persistence\ObjectManager;
defined('IN_ECJIA') or exit('No permission resources.');

/**
 *促销商品信息
 * @author 
 *
 */
class detail_module implements ecjia_interface {
	
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
	
		$result = RC_Model::Model('goods/goods_model')->promote_goods_info($id);
		
		if (!empty($result)) {
			$privilege = 3;
			EM_Api::outPut($result, null, $privilege);
		} else {
			EM_Api::outPut(13);
		}
	}
}
// end