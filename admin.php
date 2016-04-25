<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * ECJIA 促销管理程序
 */
class admin extends ecjia_admin {
    
	private $db_goods;
    public function __construct() {
        parent::__construct();
        
        RC_Loader::load_app_func('global');
        assign_adminlog_content();
        
        RC_Script::enqueue_script('jquery-validate');
        RC_Script::enqueue_script('jquery-form');
        RC_Script::enqueue_script('smoke');
        
        //下拉框
        RC_Script::enqueue_script('jquery-chosen');
        RC_Style::enqueue_style('chosen');
        
        RC_Script::enqueue_script('jquery-uniform');
        RC_Style::enqueue_style('uniform-aristo');
        
        //时间控件
        RC_Style::enqueue_style('datepicker',   RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'));
        RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
        
        RC_Script::enqueue_script('运营经理', RC_App::apps_url('statics/js/promotion.js', __FILE__), array(), false, true);
        
        $this->db_goods = RC_Loader::load_app_model('goods_model', 'goods');
        
        ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here('促销商品列表', RC_Uri::url('mobilebuy/admin/init')));
    }
    
    	
	/**
	 * 促销商品列表页
	 */
	public function init () 
	{
		$this->admin_priv('promotion_manage', ecjia::MSGTYPE_JSON);
		
		$type = isset($_GET['type']) && in_array($_GET['type'], array('on_sale', 'coming', 'finished')) ? trim($_GET['type']) : '';
		$promotion_list = $this->promotion_list($type);
		
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
		
		$field = 'count(*) as count, SUM(IF(promote_start_date <'.$time.' and promote_end_date > '.$time.', 1, 0)) as on_sale,'.
				'SUM(IF(promote_start_date >'.$time.', 1, 0)) as coming, SUM(IF(promote_end_date <'.$time.', 1, 0)) as finished';
		$type_count = $this->db_goods->field($field)->where(array('is_promote' => 1, 'is_delete' => array('neq' => 1)))->find();
		
		
		$this->assign('ur_here', '促销商品列表');
		$this->assign('action_link',  array('href' => RC_Uri::url('promotion/admin/add'), 'text' => '添加促销活动商品'));
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here('促销商品列表'));
		$this->assign('promotion_list', $promotion_list['promotion_list']);
		$this->assign('page', $promotion_list['page']);
		$this->assign('type', $type);
		$this->assign('time',  RC_Time::local_date(ecjia::config('date_format'), $time));
		$this->assign('type_count', $type_count);
		
		$this->display('promotion_list.dwt');
	}

	/**
	 * 添加手机专享显示页
	 */
	public function add() 
	{
		$this->admin_priv('promotion_add', ecjia::MSGTYPE_JSON);
		$this->assign('ur_here', '添加促销活动商品');
		$this->assign('action_link', array('href' =>  RC_Uri::url('promotion/admin/init'), 'text' => '促销商品列表'));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here('添加促销活动商品'));
		$this->assign('form_action', RC_Uri::url('promotion/admin/insert'));
		$this->assign_lang();
		$this->display('promotion_info.dwt');
	}
	
	/**
	 * 添加手机专享执行
	 */
	public function insert()
	{
		$this->admin_priv('promotion_add',ecjia::MSGTYPE_JSON);
		
		$goods_id     = intval($_POST['goods_id']);
		$price	  	  = $_POST['price'];
		
		/* 判断商品是否已在活动中*/
		$time = RC_Time::gmtime();
		$info = $this->db_goods->find(array('is_promote' => 1, 'goods_id' => $goods_id, 'promote_start_date' => array('elt' => $time), 'promote_end_date' => array('egt' => $time)));
		if (!empty($info)) {
			$this->showmessage('您选择的商品目前正在进行促销活动。。,请选择其他商品！', ecjia::MSGTYPE_JSON|ecjia::MSGSTAT_ERROR);
		}
		
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		
		$start_time = RC_Time::local_strtotime($_POST['start_time']);
		$end_time = RC_Time::local_strtotime($_POST['end_time']);
		if ($start_time >= $end_time) {
			$this->showmessage('请输入一个有效的促销时间！',ecjia::MSGTYPE_JSON|ecjia::MSGSTAT_ERROR);
		}
		
		$this->db_goods->where(array('goods_id' => $goods_id))->update(array('is_promote' => 1, 'promote_price' => $price , 'promote_start_date' => $start_time, 'promote_end_date' => $end_time));
		
		ecjia_admin::admin_log($goods_name, 'add', 'promotion');
		$links[] = array('text' => '继续添加促销活动商品', 'href'=> RC_Uri::url('promotion/admin/add'));
		$this->showmessage('添加促销活动商品成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links , 'pjaxurl' => RC_Uri::url('promotion/admin/edit', array(id => $goods_id))));
	}
	
	/**
	 * 编辑手机专享显示页
	 */
	public function edit()
	{	
		$this->admin_priv('promotion_update', ecjia::MSGTYPE_JSON);
		$this->assign('ur_here',  '修改促销活动商品');
		$this->assign('action_link',  array('href' => RC_Uri::url('promotion/admin/init'), 'text' => '促销商品列表'));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('修改促销活动商品')));
		
		$id = intval($_GET['id']) ;
		$promotion_info = $this->db_goods->field(array('goods_id', 'goods_name', 'promote_price', 'promote_start_date', 'promote_end_date'))->find(array('goods_id' => $id));
		$promotion_info['promote_start_date'] = RC_Time::local_date(ecjia::config('date_format'), $promotion_info['promote_start_date'] );
		$promotion_info['promote_end_date'] = RC_Time::local_date(ecjia::config('date_format'), $promotion_info['promote_end_date'] );
		
		$this->assign('promotion_info', $promotion_info);
		
		$this->assign('form_action', RC_Uri::url('promotion/admin/update'));
		$this->assign_lang();
		$this->display('promotion_info.dwt');
	}
	
	/**
	 * 编辑手机专享执行
	 */
	public function update()
	{
		$this->admin_priv('promotion_update', ecjia::MSGTYPE_JSON);
		$goods_id     = intval($_POST['goods_id']);
		$price	  	  = $_POST['price'];
		
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		
		$start_time = RC_Time::local_strtotime($_POST['start_time']);
		$end_time = RC_Time::local_strtotime($_POST['end_time']);
		if ($start_time >= $end_time) {
			$this->showmessage('请输入一个有效的促销时间！',ecjia::MSGTYPE_JSON|ecjia::MSGSTAT_ERROR);
		}
		
		$this->db_goods->where(array('goods_id' => $goods_id))->update(array('is_promote' => 1, 'promote_price' => $price , 'promote_start_date' => $start_time, 'promote_end_date' => $end_time));
		
		/* 提示信息 */
		$links[] = array('text'=>'促销活动列表', 'href' => RC_Uri::url('promotion/admin/init'));
		
		/* 记录日志 */
		ecjia_admin::admin_log($goods_name, 'edit', 'promotion');
		$this->showmessage('编辑促销活动商品成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('promotion/admin/edit', array(id => $goods_id))));
	}
	
	public function remove()
	{
		$this->admin_priv('promotion_delete', ecjia::MSGTYPE_JSON);
		$id = intval($_GET['id']);
		$goods_name = $this->db_goods->where(array('goods_id' => $id))->get_field('goods_name');
		$this->db_goods->where(array('goods_id'=> $id ))->update(array('is_promote' => 0, 'promote_price' => 0 , 'promote_start_date' => 0, 'promote_end_date' => 0));
		
		/* 记录日志 */
		ecjia_admin::admin_log($goods_name, 'remove', 'promotion');
		$this->showmessage('删除成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	public function search_goods() 
	{
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
	private function promotion_list($type = '')
	{
		/* 查询条件 */
		$filter['keywords']   = empty($_POST['keywords']) ? '' : trim($_POST['keywords']);
		$where = array('is_promote' => 1);
		$where['is_delete'] = array('neq' => 1);
		
		if (!empty($filter['keywords'])) {
			$where['goods_name'] = array('like' => '"%'.$filter['keywords'].'%"');
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
		
		RC_Loader::load_sys_class ('ecjia_page', false);
		$page = new ecjia_page($count, 15, 5);
		
		$field = 'goods_id, goods_name, promote_price, promote_start_date, promote_end_date, goods_thumb';
		/* 获活动数据 */

		$result = $this->db_goods->field($field)->where($where)->order(array('promote_start_date' => 'desc'))->limit($page->limit())->select();
		
		foreach ($result AS $key => $val) {
			$result[$key]['start_time'] = RC_Time::local_date(ecjia::config('date_format'), $val['promote_start_date']);
			$result[$key]['end_time']   = RC_Time::local_date(ecjia::config('date_format'), $val['promote_end_date']);
			if (!file_exists(RC_Upload::upload_path() . $v['goods_thumb']) || empty($v['goods_thumb'])) {
				$result[$key]['goods_thumb'] = RC_Uri::admin_url('statics/images/nopic.png');
			} else {
				$result[$key]['goods_thumb'] = RC_Upload::upload_url() . '/' . $val['goods_thumb'];
			}
		}
		
		$filter['keywords'] = stripslashes($filter['keywords']);
		$arr = array('promotion_list' => $result, 'filter' => $filter, 'page' => $page->show (5), 'desc' => $page->page_desc());
		return $arr;
	}
}

// end