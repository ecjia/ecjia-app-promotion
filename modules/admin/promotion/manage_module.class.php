<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 促销商品添加编辑处理
 * @author 
 *
 */
class manage_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$priv = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($priv)) {
			$privilege = 0;
			EM_Api::outPut(array(), null, $privilege);
		}
		
		$goods_id = _POST('goods_id', 0);
		if ($goods_id <= 0) {
			EM_Api::outPut(101);
		}
		
		$promotion = array(
			'goods_id'				=> $goods_id,
			'is_promote'    		=> '1',
			'promote_price' 		=> _POST('promote_price'),
			'promote_start_date'    => RC_Time::local_strtotime(_POST('start_time')),
			'promote_end_date'      => RC_Time::local_strtotime(_POST('end_time')),
		);
		
		/* 检查促销时间 */
		if ($promotion['promote_start_date'] >= $promotion['promote_end_date']) {
			return new ecjia_error('time_error', __('促销开始时间不能大于或等于结束时间'));
		}
		
		RC_Model::Model('goods/goods_model')->promotion_manage($promotion);

		$db =  RC_Model::Model('goods/goods_model');
		$goods_name = $db->where(array('goods_id' => $goods_id))->get_field('goods_name');
		RC_Loader::load_app_func('global', 'promotion');
		assign_adminlog_content();
		ecjia_admin::admin_log($goods_name, 'edit', 'promotion');
		return array();
	}
}
// end