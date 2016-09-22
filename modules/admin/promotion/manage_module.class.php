<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 促销商品添加编辑处理
 * @author
 *
 */
class manage_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {

        $this->authadminSession();

        $priv = $this->admin_priv('goods_manage');
        if (is_ecjia_error($priv)) {
            return $priv;
        }

        $goods_id = $this->requestData('goods_id', 0);
        if ($goods_id <= 0) {
            return new ecjia_error(101, '参数错误');
        }

        $promotion_info = RC_Model::Model('goods/goods_model')->promote_goods_info($goods_id);
        /* 多商户处理*/
        if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] > 0 && $promotion_info['seller_id'] != $_SESSION['seller_id']) {
            return new ecjia_error(8, 'fail');
        }

        $promotion = array(
            'goods_id'              => $goods_id,
            'is_promote'            => '1',
            'promote_price'         => $this->requestData('promote_price'),
            'promote_start_date'    => RC_Time::local_strtotime($this->requestData('start_time')),
            'promote_end_date'      => RC_Time::local_strtotime($this->requestData('end_time')),
        );

        /* 检查促销时间 */
        if ($promotion['promote_start_date'] >= $promotion['promote_end_date']) {
            return new ecjia_error('time_error', __('促销开始时间不能大于或等于结束时间'));
        }

        RC_Model::Model('goods/goods_model')->promotion_manage($promotion);

        RC_Loader::load_app_func('global', 'promotion');
        assign_adminlog_content();
        ecjia_admin::admin_log($promotion_info['goods_name'], 'edit', 'promotion');
        return array();
    }
}
// end
