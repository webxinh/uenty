<?php
namespace common\components;
use Aabc;
use aabc\base\Component;
class d extends Component { 

public $i = 'd-i';  //data controller
public $u = 'd-u';	//data url
public $s = 'd-s';  //data  reload
public $r = 'd-r';
public $c = 'd-c';
public $t = 'd-t'; // = sea: Tìm kiếm // = show: radio 
public $ty = 'd-ty';
public $m = 'd-m';  //data modal
public $ct = 'd-ct';  //data modal


public $tp = 'd-tp';
public $lt = 'd-lt';
public $wh = 'd-wh';
public $ht = 'd-ht';

public $st = 'd-st';
public $gr = 'd-gr';
// public $lk = 'd-lk';

public $add = 'd-add'; //data add 

public $lk = 'd-lk'; //data link

public $postimg = 'ImagePost'; //image post

public $type = 'd-type'; //Type danh muc


public $lcs = 'd-lcs'; //ListChinhSach, list chinh sach ap dung cho cac danh muc cu ther (Form San pham)
public $cc = 'd-cc'; //Count click, đếm 1 cs có bao nhiêu danh mục áp dụng cs đó.

public function decodeview($kq = '')
{
	$kq2 = $kq;
	$kq = str_replace('q' ,'QRU159ADG',$kq);
	$kq = str_replace('w' ,'q',$kq);
	$kq = str_replace('e' ,'w',$kq);
	$kq = str_replace('r' ,'e',$kq);
	$kq = str_replace('t' ,'r',$kq);
	$kq = str_replace('y' ,'t',$kq);
	$kq = str_replace('u' ,'y',$kq);
	$kq = str_replace('i' ,'u',$kq);
	$kq = str_replace('o' ,'i',$kq);
	$kq = str_replace('p' ,'o',$kq);
	$kq = str_replace('a' ,'p',$kq);
	$kq = str_replace('s' ,'a',$kq);
	$kq = str_replace('d' ,'s',$kq);
	$kq = str_replace('f' ,'d',$kq);
	$kq = str_replace('g' ,'f',$kq);
	$kq = str_replace('h' ,'g',$kq);
	$kq = str_replace('j' ,'h',$kq);
	$kq = str_replace('k' ,'j',$kq);
	$kq = str_replace('l' ,'k',$kq);
	$kq = str_replace('z' ,'l',$kq);
	$kq = str_replace('x' ,'z',$kq);
	$kq = str_replace('c' ,'x',$kq);
	$kq = str_replace('v' ,'c',$kq);
	$kq = str_replace('b' ,'v',$kq);
	$kq = str_replace('n' ,'b',$kq);
	$kq = str_replace('m' ,'n',$kq);

	$kq = str_replace('0' ,'m',$kq);
	$kq = str_replace('1' ,'0',$kq);
	$kq = str_replace('2' ,'1',$kq);
	$kq = str_replace('3' ,'2',$kq);
	$kq = str_replace('4' ,'3',$kq);
	$kq = str_replace('5' ,'4',$kq);
	$kq = str_replace('6' ,'5',$kq);
	$kq = str_replace('7' ,'6',$kq);
	$kq = str_replace('8' ,'7',$kq);
	$kq = str_replace('9' ,'8',$kq);  

	$kq = str_replace('>' ,'9',$kq);         
	$kq = str_replace('"' ,'>',$kq);
	$kq = str_replace('=' ,'"',$kq);
	$kq = str_replace('-' ,'=',$kq);
	$kq = str_replace('<' ,'-',$kq);
	$kq = str_replace("'" ,'<',$kq);

	$kq = str_replace('/' ,"'",$kq);
	
	// md5(mt_rand())
	return $kq;
	// return $kq2;
}
} ?>