<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace Admin\Controller;

class AdminUserController extends AdminController
{
	public function index()
	{
		$this->field = '';
		$this->name = '';
		$this->status = '';
		$where = array();
		$parameter = array();
		$rows = 15;
		$input = I('get.');

		if ($input['status']) {
			$this->status = $input['status'];
			$where['status'] = $input['status'];
			$parameter['status'] = $input['status'];
		}

		if ($input['name'] && $input['field']) {
			$this->name = $input['name'];
			$this->field = $input['field'];
			$where[$input['field']] = $input['name'];
			$parameter['name'] = $input['name'];
			$parameter['field'] = $input['field'];
		}

		$count = M('Admin')->where($where)->count();
		$Page = new \Think\Page($count, 15, $parameter);
		$show = $Page->show();
		$list = M('Admin')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function add()
	{
		if (IS_POST) {
			if (APP_DEMO) {
				$this->error('Test station temporarily unable to modify ！');
			}

			$input = I('post.');

			if (!check($input['username'], 'username')) {
				$this->error('User name format error ！');
			}

			if (!check($input['nickname'], 'A')) {
				$this->error('Nickname format error! ');
			}

			if (!check($input['password'], 'password')) {
				$this->error('Login password format error! ');
			}

			if (!check($input['moble'], 'moble')) {
				$this->error('Mobile phone number format error ');
			}

			if (!check($input['email'], 'email')) {
				$this->error('Email format error ');
			}

			$input['status'] = 1;
			$input['addtime'] = time();
			$input['updatetime'] = time();

			if (M('Admin')->add($input)) {
				$this->success('Add success! ', U('AdminUser/index'));
			}
			else {
				$this->error('Add failed! ');
			}
		}
		else {
			$this->display();
		}
	}

	public function detail()
	{
		if (empty($_GET['id'])) {
			redirect(U('AdminUser/index'));
		}
		else {
			$this->data = M('Admin')->where(array('id' => trim($_GET['id'])))->find();
		}

		$this->display();
	}

	public function edit()
	{
		if (IS_POST) {
			if (APP_DEMO) {
				$this->error('Test station temporarily unable to modify! ');
			}

			$input = I('post.');

			if ($input['password']) {
				$input['showpassword'] = $input['password'];
				$input['password'] = md5($input['password']);
			}
			else {
				unset($input['password']);
			}

			$input['updatetime'] = time();

			if (M('Admin')->save($input)) {
				$this->success('Editor success ！');
			}
			else {
				$this->error('Editor failed ！');
			}
		}
		else {
			if (empty($_GET['id'])) {
				$this->data = null;
			}
			else {
				$this->data = M('Admin')->where(array('id' => trim($_GET['id'])))->find();
			}

			$this->display();
		}
	}

	public function status()
	{
		if (APP_DEMO) {
			$this->error('Test station temporarily unable to modify ！');
		}

		if (IS_POST) {
			$id = array();
			$id = implode(',', $_POST['id']);
		}
		else {
			$id = $_GET['id'];
		}

		if (empty($id)) {
			$this->error('Please select the operation data !');
		}

		$where['id'] = array('in', $id);
		$method = $_GET['method'];

		switch (strtolower($method)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'delete':
			if (M('Admin')->where($where)->delete()) {
				$this->success('Successful operation ！');
			}
			else {
				$this->error('operation failed ！');
			}

			break;

		default:
			$this->error('invalid parameter ');
		}

		if (M('Admin')->where($where)->save($data)) {
			$this->success('Successful operation ！');
		}
		else {
			$this->error('operation failed ！');
		}
	}
}

?>
