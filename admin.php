<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * ECJIA 促销管理程序
 */
class admin extends ecjia_admin {
    
	private $db_goods;
    public function __construct() {
        parent::__construct();
        $this->db_goods = RC_Model::model('goods/goods_model');
        
        RC_Loader::load_app_func('global');
        assign_adminlog_content();
        
        RC_Script::enqueue_script('jquery-validate');
        RC_Script::enqueue_script('jquery-form');
        RC_Script::enqueue_script('smoke');
        RC_Script::enqueue_script('jquery-chosen');
        RC_Style::enqueue_style('chosen');
        RC_Script::enqueue_script('jquery-uniform');
        RC_Style::enqueue_style('uniform-aristo');
        RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'));
        RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
        RC_Script::enqueue_script('promotion', RC_App::apps_url('statics/js/promotion.js', __FILE__), array(), false, true);
        
        ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('promotion::promotion.promotion'), RC_Uri::url('mobilebuy/admin/init')));
    }
    	
	/**
	 * 促销商品列表页
	 */
	public function init() {
		$this->admin_priv('promotion_manage', ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('promotion::promotion.promotion')));
		$this->assign('ur_here', RC_Lang::get('promotion::promotion.promotion_list'));
		$this->assign('action_link', array('href' => RC_Uri::url('promotion/admin/add'), 'text' => RC_Lang::get('promotion::promotion.add_promotion')));
		
		$type = isset($_GET['type']) && in_array($_GET['type'], array('on_sale', 'coming', 'finished')) ? trim($_GET['type']) : '';
		$promotion_list = $this->promotion_list($type);
		$time = RC_Time::gmtime();
		$where = array('is_promote' => 1);
		$where['is_delete'] = array('neq' => 1);
		if (!empty($_GET['keywords'])) {
			$where['goods_name'] = array('like' => '%'.$_GET['keywords'].'%');
		}
		$field = 'count(*) as count, SUM(IF(promote_start_date <'.$time.' and promote_end_date > '.$time.', 1, 0)) as on_sale,'.
				'SUM(IF(promote_start_date >'.$time.', 1, 0)) as coming, SUM(IF(promote_end_date <'.$time.', 1, 0)) as finished';
		$type_count = $this->db_goods->field($field)->where($where)->find();
		$this->assign('promotion_list', $promotion_list);
		$this->assign('type', $type);
		$this->assign('time',  RC_Time::local_date(ecjia::config('date_format'), $time));
		$this->assign('type_count', $type_count);
		
		$this->assign('form_search', RC_Uri::url('promotion/admin/init'));
		
		$this->display('promotion_list.dwt');
	}

	/**
	 * 添加促销商品
	 */
	public function add() {
		$this->admin_priv('promotion_update', ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('promotion::promotion.add_promotion')));
		$this->assign('ur_here', RC_Lang::get('promotion::promotion.add_promotion'));
		$this->assign('action_link', array('href' => RC_Uri::url('promotion/admin/init'), 'text' => RC_Lang::get('promotion::promotion.promotion_list')));
		
		$this->assign('form_action', RC_Uri::url('promotion/admin/insert'));
		
		$this->display('promotion_info.dwt');
	}
	
	/**
	 * 处理添加促销商品
	 */
	public function insert() {
		$this->admin_priv('promotion_update', ecjia::MSGTYPE_JSON);
		
		$goods_id 	= intval($_POST['goods_id']);
		$price		= $_POST['price'];
		
		$time = RC_Time::gmtime();
		$info = $this->db_goods->find(array('is_promote' => 1, 'goods_id' => $goods_id, 'promote_start_date' => array('elt' => $time), 'promote_end_date' => array('egt' => $time)));
		if (!empty($info)) {
			$this->showmessage(RC_Lang::get('promotion::promotion.promotion_exist'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		$start_time = RC_Time::local_strtotime($_POST['start_time']);
		$end_time 	= RC_Time::local_strtotime($_POST['end_time']);
		
		if ($start_time >= $end_time) {
			$this->showmessage(RC_Lang::get('promotion::promotion.promotion_invalid'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$this->db_goods->where(array('goods_id' => $goods_id))->update(array('is_promote' => 1, 'promote_price' => $price, 'promote_start_date' => $start_time, 'promote_end_date' => $end_time));
		
		ecjia_admin::admin_log($goods_name, 'add', 'promotion');
		$links[] = array('text' => RC_Lang::get('promotion::promotion.return_promotion_list'), 'href'=> RC_Uri::url('promotion/admin/init'));
		$links[] = array('text' => RC_Lang::get('promotion::promotion.continue_add_promotion'), 'href'=> RC_Uri::url('promotion/admin/add'));
		$this->showmessage(RC_Lang::get('promotion::promotion.add_promotion_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('promotion/admin/edit', array('id' => $goods_id))));
	}
	
	/**
	 * 编辑促销商品
	 */
	public function edit() {	
		$this->admin_priv('promotion_update', ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('promotion::promotion.edit_promotion')));
		$this->assign('ur_here', RC_Lang::get('promotion::promotion.edit_promotion'));
		$this->assign('action_link', array('href' => RC_Uri::url('promotion/admin/init'), 'text' => RC_Lang::get('promotion::promotion.promotion_list')));
		
		$id = intval($_GET['id']);
		$promotion_info = $this->db_goods->field(array('goods_id', 'goods_name', 'promote_price', 'promote_start_date', 'promote_end_date'))->find(array('goods_id' => $id));
		$promotion_info['promote_start_date'] = RC_Time::local_date(ecjia::config('date_format'), $promotion_info['promote_start_date']);
		$promotion_info['promote_end_date'] = RC_Time::local_date(ecjia::config('date_format'), $promotion_info['promote_end_date'] );
		$this->assign('promotion_info', $promotion_info);
		
		$this->assign('form_action', RC_Uri::url('promotion/admin/update'));

		$this->display('promotion_info.dwt');
	}
	
	/**
	 * 更新促销商品
	 */
	public function update() {
		$this->admin_priv('promotion_update', ecjia::MSGTYPE_JSON);
		
		$goods_id		= intval($_POST['goods_id']);
		$price	  	  	= $_POST['price'];
		$goods_name 	= $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		$start_time 	= RC_Time::local_strtotime($_POST['start_time']);
		$end_time 		= RC_Time::local_strtotime($_POST['end_time']);
		$old_goods_id   = intval($_POST['old_goods_id']);
		
		if ($start_time >= $end_time) {
			$this->showmessage(RC_Lang::get('promotion::promotion.promotion_invalid'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$this->db_goods->where(array('goods_id' => $goods_id))->update(array('is_promote' => 1, 'promote_price' => $price, 'promote_start_date' => $start_time, 'promote_end_date' => $end_time));
		//更新原来的商品为非促销商品
		if ($goods_id != $old_goods_id) {
			$this->db_goods->where(array('goods_id'=> $old_goods_id))->update(array('is_promote' => 0, 'promote_price' => 0, 'promote_start_date' => 0, 'promote_end_date' => 0));
		}
		
		ecjia_admin::admin_log($goods_name, 'edit', 'promotion');
		$this->showmessage(RC_Lang::get('promotion::promotion.edit_promotion_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('promotion/admin/edit', array('id' => $goods_id))));
	}
	
	/**
	 * 删除促销商品
	 */
	public function remove() {
		$this->admin_priv('promotion_delete', ecjia::MSGTYPE_JSON);
		
		$id = intval($_GET['id']);
		$goods_name = $this->db_goods->where(array('goods_id' => $id))->get_field('goods_name');
		$this->db_goods->where(array('goods_id'=> $id ))->update(array('is_promote' => 0, 'promote_price' => 0, 'promote_start_date' => 0, 'promote_end_date' => 0));
		
		ecjia_admin::admin_log($goods_name, 'remove', 'promotion');
		$this->showmessage(RC_Lang::get('promotion::promotion.remove_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	/**
	 * 添加/编辑页搜索商品
	 */
	public function search_goods() {
		$goods_list = array();
		$row = RC_Api::api('goods', 'get_goods_list', array('keyword' => $_POST['keyword']));
		if (!is_ecjia_error($row)) {
			if (!empty($row)) {
				foreach ($row AS $key => $val) {
					$goods_list[] = array(
						'value' => $val['goods_id'],
						'text'  => $val['goods_name'],
						'data'  => $val['shop_price']
					);
				}
			}
		}
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $goods_list));
	}
	
	/**
	 * 获取活动列表
	 *
	 * @access  public
	 *
	 * @return void
	 */
	private function promotion_list($type = '') {
		$filter['keywords'] = empty($_GET['keywords']) ? '' : trim($_GET['keywords']);
		$where = array('is_promote' => 1);
		$where['is_delete'] = array('neq' => 1);
		if (!empty($filter['keywords'])) {
			$where['goods_name'] = array('like' => '%'.$filter['keywords'].'%');
		}
		
		$time = RC_Time::gmtime();
		if ($type == 'on_sale') {
			$where['promote_start_date'] = array('elt' => $time);
			$where['promote_end_date'] = array('egt' => $time);
		}
		
		if ($type == 'coming') {
			$where['promote_start_date'] = array('egt' => $time);
		}
		
		if ($type == 'finished') {
			$where['promote_end_date'] = array('elt' => $time);
		}
		
		$count = $this->db_goods->where($where)->count();
		$page = new ecjia_page($count, 10, 5);
		
		$field = 'goods_id, goods_name, promote_price, promote_start_date, promote_end_date, goods_thumb';
		$result = $this->db_goods->field($field)->where($where)->order(array('promote_start_date' => 'desc'))->limit($page->limit())->select();
		
		if (!empty($result)) {
			foreach ($result as $key => $val) {
				$result[$key]['start_time'] = RC_Time::local_date(ecjia::config('date_format'), $val['promote_start_date']);
				$result[$key]['end_time']   = RC_Time::local_date(ecjia::config('date_format'), $val['promote_end_date']);
				if (!file_exists(RC_Upload::upload_path() . $val['goods_thumb']) || empty($val['goods_thumb'])) {
					$result[$key]['goods_thumb'] = RC_Uri::admin_url('statics/images/nopic.png');
				} else {
					$result[$key]['goods_thumb'] = RC_Upload::upload_url() . '/' . $val['goods_thumb'];
				}
			}
		}
		$filter['keywords'] = stripslashes($filter['keywords']);
		$arr = array('item' => $result, 'filter' => $filter, 'page' => $page->show(5), 'desc' => $page->page_desc());
		return $arr;
	}
}

// end