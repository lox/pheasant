<?php

namespace Pheasant\Types;

function Integer($length=11, $params=null)
{
	return new Integer($length, $params);
}

function String($length=255, $params=null)
{
	return new String($length, $params);
}

function Sequence($params=null)
{
	return new Sequence($params);
}
