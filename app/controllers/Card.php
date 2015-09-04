<?php 
class CardController extends rest
{
	/**
	 * 应用首页
	 */
	public function GET_indexAction()
	{
		$uid = $this->auth();
		$user = UserModel::field('phone,status')->find($uid);
		if (! $user['phone'])
		{
			$this->response(0,'使用此功能必须绑定手机号')
		}
		elseif ($user['status'] < 0)
		{
			$this->response(0,'已封号');
		}
		elseif (! $card = CardModel::find($uid))  //第一次使用，记录用户信息到card表
		{
			$card['id'] = $uid;
			CardModel::addCard($card);
			$this->response(1,null);
		}
		else //返回记录总数
		{
			$count = CardModel::where('find_id', '=', $uid)->orWhere('lost_id', '=', $uid)->count();
			$this->response(1,$count);
		}
	}

	/**
	 * GET_logAction()
	 * 记录页
	 */
	public function GET_logAction($id = 0)
	{
		$uid = $this->auth($id);
		$Cardlog = new CardlogModel;
		$log['lost'] = $Cardlog->where('lost_id','=',$uid)->order('id','desc')->select();
		$find = $Cardlog->where('find_id','=',$uid)->order('id','desc')->select();
		foreach ($find as &$value)  
		{
			if (! $value['lost_id'])
			{
				$value['lost_name'] = '非云印用户';
				$value['lost_number'] = '尚未注册';
			}
		}
		$log['find'] = $find;
		$this->response = (1,$log);
	}

	/**
	 * POST_findAction()
	 * 发送短信、邮箱通知失主
	 * @param number 学号
	 * @param name   姓名
	 */
	public function POST_findAction($id = 0)
	{
		$uid = $this->auth($id);
		I('number', $number, 'number.all');
		I('name', $name, 'trim');
		$finder = UserModel::field('sch_id,number,name,phone,email')->find($uid);
		$response['status'] = 0;
		if ($finder['phone'])
		{
			$this->error('尚未绑定手机');
		}
		elseif ($finder['number'] == $number)
		{
			$this->error('不要用自己的做实验哦！');
		}
		elseif (CardModel::field('blocked')->find($uid))
		{
			$this->error('由于恶意使用,您的此功能已被禁用');
		}
		elseif (! $name || (! $number))
		{
			$this->error('信息不足');
		}
		else
		{
			/*尝试 验证 匹配 通知*/
			$receiver = UserModel::where('number','=',$number)->field('id,name,number,sch_id,phone,email')->select();
			if (! $receiver) //失主未加入平台
			{
				/* 判断学校 */
				if (Validate::card($number,'nku')) //南开
				{
					$this->_saveReceiver($name, $number, 1, false);
				}
				elseif (Validate::card($number,'tju')) //天大
				{
					$this->_saveReceiver($name, $number, 2, false);
				}
				//elseif (Validate::card($number,'tifert')) {
					
				//}
				else //其他
				{
					$this->error(0, '对不起，目前平台仅对南开大学和天津大学在校生开放，其他需求或者学校请联系我们！');
				}
				$this->response = ['status' => 0, 'info' => $name . '(' . $number . ')尚未加入，你可以在此广播到社交网络', 'url' => '/Card/broadcast'];
			}
			elseif ($name != $receiver['name'])
			{
				$this->error('失主信息核对失败！');
			}
			elseif ($recvr_off = CardModel::field('off')->find($receiver['id']))
			{
				$this->error('对方关闭了此功能,不希望你打扰TA，我们爱莫能助╮(╯-╰)╭');
			}
			elseif (! ($receiver['phone'] || $receiver['email']))
			{
				$this->_saveReceiver($receiver['name'], $receiver['number'], $receiver['sch_id'], $receiver['id']);
				$this->response = ['status' => 0, 'info' => $name . '(' . $number . ')尚未绑定联系方式，你可以在此广播到社交网络', 'url' => '/Card/broadcast'];
			}
			else
			{
				/*验证成功 ，手机或者邮箱存在 通知并记录*/
				if ($recvr_off === null)  //该同学不在card记录之中,则先创建
				{
					CardModel::addCard(array('id' => $receiver['id']))
				}
				$msg = '';
				$success = false;
				$finder_phone = Encrypt::decryptPhone($finder['phone'], $finder['number'], $uid);
				if ($receiver['phone']) //手机存在
				{
					/*发送短信通知*/
					$recvr_phone = decryptPhone($receiver['phone'], $receiver['number'], $receiver['id']);
					$info = array('send_phone' => $finder_phone, 'send_name' => $finder['name'], 'recv_name' => $receiver['name']);
					$sms_result = Sms::findCard($recvr_phone, $info);
					$success |= $sms_result;
					if ($sms_result)
					{
						$msg = '短信已发送!<br/>';
					}
					else
					{
						$msg = '短信发送失败!<br/>';
					}
				}
				if ($receiver['email'])
				{
					/*发送邮件通知*/
					$recvr_email = Encrypt::decryptEmail($receiver['email']);
					$finder_school = School::getName($finder['sch_id']);
					if ($finder['email'])
					{
						$finder_email = Encrypt::decryptEmail($finder['email']);
					}
					$msg = "<p>亲爱的<b>$receiver['name']</b>同学：</p><p>
						非常高兴地告诉你，你离家出走的校园卡已经被{$school}的<b>$finder['name']</b>
						同学捡到啦^_^</p><p>尽快通过Ta的手机号: <b><a href='tel:$finder_phone'>$finder_phone'</a></b> 
						或Ta的邮箱: <b><a href='mailto:$finder_email'>$finder_email</a></b> 与其联系吧。</p>
						<p>然后请登录云印校园卡记录中心（ <a href='http://yunyin.org/Card/log'>yunyin.org/Card/log</a> ） 
						确认一下结果哦！（好人好事，我们要感谢；恶意骚扰，我们要举报◕ω◕）</p>";
					$mail_result = Mail::sendNotify($recvr_email, $receiver['name'], $msg);
					$success |= $mail_result;
					if ($mail_result)
					{
						$msg .= '邮件已发送!<br/>';
					}
					else
					{
						$msg .= '邮件发送失败!';
					}
				}
				if ( ! $success) //判断发送结果
				{
					$this->_saveReceiver($receiver['name'], $receiver['number'], $receiver['sch_id'], $receiver['id']);
					$this->response = ['status' => 0, 'info' => '消息发送失败！请重试或者交由第三方平台！', 'url' => '/Card/broadcast'];
				}
				else
				{
					/*记录招领信息*/
					$log = array('find_id' => $finder['id'], 'lost_id' => $receiver['id']);
					if ( ! CardlogModel::add($log))
					{
						$this->response(0,'记录失败<br/>'.$msg);
					}
					else
					{
						$this->response(1,$msg);
					}

				}
			}
		}
	}

	/**
	 * PUT_offAction()
	 * 关闭该功能
	 * @param [integer] $value 权限值
	 */
	public function PUT_offAction($id = 0)
	{
		$uid = $this->auth($id);
		$response['status'] = 0;
		if (! Input::put('value', $value, 'int'))
		{
			$response['info'] = '未知错误';
		}
		else
		{
			if (CardModel::where('id','=',$uid)->update(['off' => $value]))
			{
				$response['status'] = 1;
				$response['info'] = '修改成功！';
			}
			else
			{
				$response['info'] = '修改失败!';
			}
		}
		$this->response = $response;
	}

	/**
	 * PUT_resultAction()
	 * 结果
	 * @param [integer]  	$id     记录id
	 * @param [integer]		$status 结果状态举报-1;感谢1;忽略0;
	 */
	public function PUT_resultAction($id = 0)
	{
		$uid = $this->auth($id);
		$response['status'] = 0;
		Input::put('id',$logid,'int');
		Input::put('status',$status,'int');
		if (! in_array($status, array(-1, 0, 1)) || (! $logid))
		{
			$response['info'] = '信息不足或参数不对';
		}
		else
		{
			$Cardlog = new CardlogModel;
			if ($Cardlog->where('id','=',$logid)->where('lost_id','=',$uid)->update(['status'=>$status]))
			{
				if ($status < 0)
				{
					$findid = $Cardlog->field('find_id')->find($id);
					if ($Cardlog->where('find_id','=',$findid)->where('status','<','0')->count() >= 2)
					{
						CardModel::where('id','=',$findid)->update(['blocked'=>1]);
					}
				}
				$response['status'] = 1;
				$response['info'] = '操作成功';
			}
			else
			{
				$response['info'] = '操作失败';
			}
		}
		$this->response = $response;
	}

	/**
	 * POST_phoneAction()
	 * 显示拾得者手机
	 * 若已感谢、举报或忽略，显示按钮失效
	 * @param [int] id 拾得者id
	 */
	public function POST_phoneAction($id = 0)
	{
		$uid = $this->auth($id);
		if (! Input::post('id',$id,'int'))
		{
			$findid = CardlogModel::where('id','=',$id)->where('lost_id','=',$uid)->get('find_id'))
			$finder = UserModel::field('phone,number')->find($findid);
			$phone = Encrypt::decryptPhone($finder['phone'], $finder['number'], $findid);
			echo $phone;
		}
		else
		{
			echo ('请求错误');
		}
	}

	/**
	 * 广播显示页
	 * @method POST_broadcastAction()
	 * @author NewFuture[newfuture@yunyin.org]
	 */
	public function POST_broadcastAction($id = 0)
	{
		$uid = $this->auth($id);
		if (! $receiver = Session::get('receiver')) //没有传递session信息
		{
			$this->response = ['status' => 0, 'info' => '此页仅供招领广播使用，请填写失主信息！', 'url' => '/Card/'];
		}
		else /*获取发送者和接收者信息*/
		{
			$finder = UserModel::field('name,sch_id,number,phone')->find($uid);
			$response['finder_phone'] = Encrypt::decryptPhone($finder['phone'], $finder['number'], $uid);
			$School = new SchoolModel;
			$msg_info = array(
      			'card_number' => $receiver['number'],
				'card_name' => $receiver['name'],
				'card_school' => $School->getName($receiver['sch_id']),
				'finder_name' => $finder['name'],
				'finder_school' => $School->getName($finder['sch_id']),
				'msg' => '',
			);
			$response['msg_info'] = $msg_info;
			$this->response = (1,$response);
		}
	}

	/**
	 * POST_sendAction()
	 * 失主尚未加入平台,或已加入平台但未绑定信息
	 * 向微博、人人平台发送消息
	 * @param [string]	add_phone	是否附加手机
	 * @param [int]		anonymity  	是否匿名
	 * @param [string]	add_msg		附加消息
	 */
	public function POST_sendAction($id = 0)
	{
		$uid = $this->auth($id);
		/*判断是否有接收者*/
		if (! $receiver = Session::get('receiver'))
		{
			$this->response = ['status' => 0, 'info' => '禁止乱发信息！', 'url' => '/Card/'];
		}
		else
		{	
			/*发送次数*/
			if ($times = Cache::get('send_' . $uid) > 5)
			{
				$this->response = ['status' => 0, 'info' => '发送次数过多!', 'url' => '/Card/'];
			}
			else
			{
				Cache::set('send_'.$uid,$times + 1);
			}
			$receiver_id = $receiver['uid'] ? $receiver['uid'] : 0;
			$log    = array('find_id' => $uid, 'lost_id' => $receiver_id, 'status' => 0);
			$msg_id = CardlogModel::add($log); //添加到丢失记录
			$finder = UserModel::field('name,sch_id,number,phone')->find($uid);
			I('add_phone',$add_phone,'int');
			$finder_phone = $add_phone ? Encrypt::decryptPhone($finder['phone'], $finder['number'], $uid) : '';
			I('anonymity',$anony,'int');
			$finder_name = $anony ? '云小印' : $finder['name'];
			$finder_school = SchoolModel::getName($finder['sch_id']);
			/*post数据到API*/
			$url = 'https://newfuturepy.sinaapp.com/broadcast';
			I('add_msg',$add_msg,'trim')
			$data = array(
				'key' 			=> Config::getSecret('broadcast')['weibo_api_pwd'],
				'school'        => $receiver['sch_id'],
				'card_id'       => $receiver['number'],
				'name'          => $receiver['name'],
				'contact_name'  => $finder_name.'【'.$msg_id.'】',
				'contact_phone' => $finder_phone,
				'msg'           => $add_msg,
			);
			if ($result = json_decode($this->_post($url, $data)))
			{
				Session::del('receiver');
				$result_info = '人人发送成功('.$result->renren.')条；微博发送成功('.$result->weibo.')条;BBS发送'.($result->bbs ? '成功' : '失败');
				$this->response = ['status' => 0, 'info' => $result_info, 'url' => '/Card/'];
			}
			else
			{
				CardlogModel::delete($msg_id);
				$this->response(0,'发送失败，请通过其他方式寻找失主或联系我们');
			}
		}
	}

	/**
	 * 保存接收者信息
	 * @method _saveReceiver
	 * @access private
	 *
	 * @author NewFuture[newfuture@yunyin.org]
	 * @param  [string] 	$name 		失主姓名
	 * @param  [string] 	$number     失主学号
	 * @param  [int] 		$sch_id     失主学校id
	 */
	private function _saveReceiver($name, $number, $sch_id, $uid = false)
	{
		$receiver = array('name' => $name, 'number' => $number, 'sch_id' => $sch_id, 'uid' => $uid);
		Session::set('receiver', $receiver);
	}

	/**
	 * curl post 数据
	 * @method _post
	 * @access private
	 *
	 * @author NewFuture[newfuture@yunyin.org]
	 * @param  [string] 	$url       	url接口
	 * @param  [array]  	$data      	要发送的数据
	 * @return [string] 	$result 	post结果
	 */
	private function _post($url, $data = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

}