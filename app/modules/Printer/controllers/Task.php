<?php
/**
 * 打印店任务管理
 */
class TaskController extends PrinterRest
{
	/**
	 * 打印店登录
	 * @method loginAction
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	public function GET_listAction()
	{
		$userid = $this->auth();
		Input::get('page', $page, 'int', 1);
		$tasks = TaskModel::where('use_id', '=', $userid)->belongs('user')->page($page)->select();
		$this->response(1, $tasks);
	}

	public function PUT_infoAction($id)
	{
		$userid = $this->auth();
	}
}
?>