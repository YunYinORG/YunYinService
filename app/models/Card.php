<?php
class CardModel extends FacadeModel
{
	protected $table = 'card';

	public function addCard($array)
	{
		if ($id = parent::getModel()->insert($array))
		{
			return $id;
		}
		else
		{
			return false;
		}
	}
}