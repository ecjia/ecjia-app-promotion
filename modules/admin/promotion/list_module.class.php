<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 促销商品列表
 * @author 
 *
 */
class list_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$priv = $ecjia->admin_priv('goods_manage');
	
		if (is_ecjia_error($priv)) {
			$privilege = 0;
			EM_Api::outPut(array(), null, $privilege);
		}
		
		$status 	= $this->requestData('status', '');
		$keywords   = $this->requestData('keywords', '');
		$size = $this->requestData('pagination.count', 15);
		$page = $this->requestData('pagination.page', 1);
		
		$filter = array(
			'status' 	=> $status,
			'keywords'  => $keywords,
			'size'	 	=> !empty($size) ? intval($size) : 15,
			'page'	 	=> !empty($page) ? intval($page) : 1,
		);
		$result = RC_Model::Model('goods/goods_viewmodel')->promotion_list($filter);
		$privilege = 3;
		$data = array();
		
		if (!empty($result['item'])) {
			foreach ($result['item'] as $key => $val) {
				$data[] = array(
					'goods_id'					=> $val['goods_id'],
					'goods_name'				=> $val['goods_name'],
					'formatted_shop_price'		=> price_format($val['shop_price']),
					'formatted_market_price'	=> price_format($val['market_price']),
					'formatted_promote_price'	=> price_format($val['promote_price']),
					'formatted_promote_start_date'		=> $val['promote_start_date'],
					'formatted_promote_end_date'			=> $val['promote_end_date'],
					'img'   					=> array(
													'thumb'	=>  $val['goods_img'],
													'url'	=>  $val['original_img'],
													'small'	=>  $val['goods_thumb']
												 ),
				);
			}
		} 
		
		$pager = array(
				'total' => $result['page']->total_records,
				'count' => $result['page']->total_records,
				'more'	=> $result['page']->total_pages <= $page ? 0 : 1,
		);
		EM_Api::outPut($data, $pager, $privilege);
	}
}
// end