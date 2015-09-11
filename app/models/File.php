<?php

class FileModel extends FacadeModel
{
	protected $table = 'file';

	/**
	 * 保存文件
	 * @method saveName
	 * @param  [type]   $name [description]
	 * @return [type]         [description]
	 * @author NewFuture
	 */
	public static function saveName($fid, $name)
	{
		if ($name = File::filterName($name))
		{
			return parent::where('use_id', Auth::id())->set('name', $name)->save($fid);
		}
	}
}