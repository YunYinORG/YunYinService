<?php

class SchoolModel extends FacadeModel
{
	protected $pk    = 'id';
	protected $table = 'school';

	public static function getName($id)
	{
		if (is_numeric($id))
		{
			return parent::getInstance()->getModel()->where('id', '=', $id)->get('name');
		}

	}
}