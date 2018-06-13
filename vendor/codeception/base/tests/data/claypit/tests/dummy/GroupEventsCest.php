<?php

class GroupEventsCest
{
	
	public function countGroupEvents(DumbGuy $I)
	{
		$I->wantTo('affirm that Group events fire only once');
	}
}