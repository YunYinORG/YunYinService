<?php

class PrinterModel extends FacadeModel
{
	protected $table = 'printer';

	public static function pasrePrice($price)
	{
		return $price ? json_decode($price) : null;
	}
}