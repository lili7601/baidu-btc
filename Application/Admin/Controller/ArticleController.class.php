<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace Admin\Controller;

class ArticleController extends AdminController
{
	private $Model;

	public function __construct()
	{
		parent::__construct();
		$this->Model = M('Article');
		$this->Title = 'Article management ';
	}

	public function index($name = NULL)
	{
		if ($name) {
			$where['title'] = array('like', '%' . $name . '%');
		}

		$count = $this->Model->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = $this->Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['adminid'] = M('Admin')->where(array('id' => $v['adminid']))->getField('username');
			$list[$k]['type'] = M('ArticleType')->where(array('name' => $v['type']))->getField('title');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function edit($id = NULL)
	{
		$list = M('ArticleType')->select();

		foreach ($list as $k => $v) {
			$listType[$v['name']] = $v['title'];
		}

		$this->assign('list', $listType);

		if ($id) {
			$this->data = $this->Model->where(array('id' => trim($id)))->find();
		}
		else {
			$this->data = null;
		}

		$this->display();
	}

	public function save($type = NULL)
	{
		if (APP_DEMO) {
			$this->error('Test station temporarily unable to modify ！');
		}

		if ($type == 'images') {
			$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
			$upload = new \Think\Upload();
			$upload->maxSize = 3145728;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->rootPath = './Upload/article';
			$upload->autoSub = false;
			$info = $upload->uploadOne($_FILES['imgFile']);

			if ($info) {
				$data = array('url' => str_replace('./', '/', $upload->rootPath) . $info['savename'], 'error' => 0);
				exit(json_encode($data));
			}
			else {
				$error['error'] = 1;
				$error['message'] = '';
				exit(json_encode($error));
			}
		}
		else {
			$upload = new \Think\Upload();
			$upload->maxSize = 3145728;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->rootPath = './Upload/article';
			$upload->autoSub = false;
			$info = $upload->upload();

			if ($info) {
				foreach ($info as $k => $v) {
					$_POST[$v['key']] = $v['savename'];
				}
			}

			if ($_POST['id']) {
				$rs = $this->Model->save($_POST);
			}
			else {
				$_POST['addtime'] = time();
				$_POST['adminid'] = session('admin_id');
				$rs = $this->Model->add($_POST);
			}

			if ($rs) {
				$this->success('Editor success！');
			}
			else {
				$this->error('Editor failed ！');
			}
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
			$this->error('Please select the operating data !');
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
			if ($this->Model->where($where)->delete()) {
				$this->success('Successful operation ！');
			}
			else {
				$this->error('operation failed ！');
			}

			break;

		default:
			$this->error('invalid parameter ');
		}

		if ($this->Model->where($where)->save($data)) {
			$this->success('Successful operation ！');
		}
		else {
			$this->error('operation failed ！');
		}
	}
}

?>
