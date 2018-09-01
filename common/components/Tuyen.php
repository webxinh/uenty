<?php
namespace common\components;
use Aabc;
use common\components\MyComponent;
use aabc\helpers\Url;
use backend\models\Cauhinh;
use backend\models\Image;
use backend\models\Danhmuc;
use backend\models\Sanpham;
use backend\models\Sanphamngonngu;
use backend\models\Sanphamdanhmuc;

use backend\models\Chinhsach;
use backend\models\Danhmucchinhsach;
use backend\models\Sanphamchinhsach;

use frontend\models\SanphamFront;

class Tuyen { 

public function _icon($s = '')
{	
	$arr = explode('#',$s);//Tách tên và màu;
	$s = $arr['0'];
	$mau = '';
	if(!empty($arr['1'])){
		$mau = $arr['1'];
	}

	$s = self::_dir_icon($s);
	// echo $s;
	// die;
	// if(empty($s)) return '';
	if(file_exists("./../svg/".$s.".svg")){
		return '<div class="'.$mau.'"><div class="icon">'.file_get_contents("./../svg/".$s.".svg").'</div></div>';
	}
	if(file_exists("svg/".$s.".svg")){
		return '<div class="'.$mau.'"><div class="icon">'.file_get_contents("svg/".$s.".svg").'</div></div>';
	}
	return '';
}


public static function _list_icon()
{
	return [
		'computer1' => 'Computer 1',
		'computer2' => 'Computer 2',
		'computer3' => 'Computer 3',
		'computer4' => 'Computer 4',
		'computer5' => 'Computer 5',

		'---1' => '',

		"laptop1" =>"Laptop 1",
		"laptop2" =>"Laptop 2",
		"laptop3" =>"Laptop 3",
		"laptop4" =>"Laptop 4",
		"laptop5" =>"Laptop 5",
		"laptop6" =>"Laptop 6",
		"laptop7" =>"Laptop 7",
		"laptop8" =>"Laptop 8",
		"laptop9" =>"Laptop 9",
		"laptop10" =>"Laptop 10",
		"laptop11" =>"Laptop 11",
		"laptop12" =>"Laptop 12",
		"laptop13" =>"Laptop 13",
		"laptop14" =>"Laptop 14",
		"laptop15" =>"Laptop 15",
		"laptop16" =>"Laptop 16",
		"laptop17" =>"Laptop 17",
		"laptop18" =>"Laptop 18",
		"laptop19" =>"Laptop 19",
		"laptop20" =>"Laptop 20",
		"laptop21" =>"Laptop 21",
		"laptop22" =>"Laptop 22",
		"laptop23" =>"Laptop 23",
		"laptop24" =>"Laptop 24",
		"laptop25" =>"Laptop 25",
		"laptop26" =>"Laptop 26",
		"laptop27" =>"Laptop 27",
		"laptop28" =>"Laptop 28",
		"laptop29" =>"Laptop 29",
		"laptop30" =>"Laptop 30",
		"laptop31" =>"Laptop 31",
		"laptop32" =>"Laptop 32",
		"laptop33" =>"Laptop 33",
		"laptop34" =>"Laptop 34",
		"laptop35" =>"Laptop 35",
		"laptop36" =>"Laptop 36",
		"laptop37" =>"Laptop 37",
		"laptop38" =>"Laptop 38",
		"laptop39" =>"Laptop 39",
		"laptop40" =>"Laptop 40",
		"laptop41" =>"Laptop 41",
		"laptop42" =>"Laptop 42",
		"laptop43" =>"Laptop 43",
		"laptop44" =>"Laptop 44",
		"laptop45" =>"Laptop 45",
		"laptop46" =>"Laptop 46",

		'---2' => '',


		'icon1' => 'Icon 1',
		'icon2' => 'Icon 2',
		'icon3' => 'Icon 3',
		'icon4' => 'Back',
	];
}
public static function _dir_icon($s = '')
{
	$array = [
		'computer1' => 'computer/computer1',
		'computer2' => 'computer/computer2',
		'computer3' => 'computer/computer3',
		'computer4' => 'computer/computer4',
		'computer5' => 'computer/computer5',

		"laptop1" =>"laptop/laptop1",
		"laptop2" =>"laptop/laptop2",
		"laptop3" =>"laptop/laptop3",
		"laptop4" =>"laptop/laptop4",
		"laptop5" =>"laptop/laptop5",
		"laptop6" =>"laptop/laptop6",
		"laptop7" =>"laptop/laptop7",
		"laptop8" =>"laptop/laptop8",
		"laptop9" =>"laptop/laptop9",
		"laptop10" =>"laptop/laptop10",
		"laptop11" =>"laptop/laptop11",
		"laptop12" =>"laptop/laptop12",
		"laptop13" =>"laptop/laptop13",
		"laptop14" =>"laptop/laptop14",
		"laptop15" =>"laptop/laptop15",
		"laptop16" =>"laptop/laptop16",
		"laptop17" =>"laptop/laptop17",
		"laptop18" =>"laptop/laptop18",
		"laptop19" =>"laptop/laptop19",
		"laptop20" =>"laptop/laptop20",
		"laptop21" =>"laptop/laptop21",
		"laptop22" =>"laptop/laptop22",
		"laptop23" =>"laptop/laptop23",
		"laptop24" =>"laptop/laptop24",
		"laptop25" =>"laptop/laptop25",
		"laptop26" =>"laptop/laptop26",
		"laptop27" =>"laptop/laptop27",
		"laptop28" =>"laptop/laptop28",
		"laptop29" =>"laptop/laptop29",
		"laptop30" =>"laptop/laptop30",
		"laptop31" =>"laptop/laptop31",
		"laptop32" =>"laptop/laptop32",
		"laptop33" =>"laptop/laptop33",
		"laptop34" =>"laptop/laptop34",
		"laptop35" =>"laptop/laptop35",
		"laptop36" =>"laptop/laptop36",
		"laptop37" =>"laptop/laptop37",
		"laptop38" =>"laptop/laptop38",
		"laptop39" =>"laptop/laptop39",
		"laptop40" =>"laptop/laptop40",
		"laptop41" =>"laptop/laptop41",
		"laptop42" =>"laptop/laptop42",
		"laptop43" =>"laptop/laptop43",
		"laptop44" =>"laptop/laptop44",
		"laptop45" =>"laptop/laptop45",
		"laptop46" =>"laptop/laptop46",



		'icon1' => 'icon1',
		'icon2' => 'icon2',
		'icon3' => 'icon3',
		'icon4' => 'icon4',
	];
	if ($s === null || !array_key_exists($s, $array))
        return '';
    return $array[$s];
}


public static function _dulieu($controller='',$id = '',$type = 'array')
{		
	// $s = $controller . $id . Cauhinh::template();
	$bk_id = $id;//backup	
	$bk_controller = $controller;//backup	
	if($controller == 'spdm'){
		$controller = 'tssp';
		$id = str_replace('-','000',$id);
	}
	elseif($controller == 'csdm'){		
		$id = str_replace('-','000',$id);
	}
	elseif($controller == 'cssp'){		
		$id = str_replace('-','000',$id);
	}
	elseif($controller == 'baiviet'){
		$controller = 'sanpham';
	}
	elseif($controller == 'khuyenmai'){
		$controller = 'cs';
	}


	$s = $controller . $id;
	$cache = Aabc::$app->dulieu;

	if($controller == 'spnn'){
		$s = $s . '01';
	}


	$return = $cache->get($s);//Lấy từ cache ra

	if(empty($return)){//Rỗng
		//Thử tìm trong csdl
		if($controller == 'cauhinh'){
			$cauhinh = (Cauhinh::M)::find()->where(['ch_key' => $id])->one();
			if($cauhinh) $return = Cauhinh::cache($cauhinh);
		}

		elseif($controller == 'cs'){
			$model = Chinhsach::find()->where(['cs_id' => $id])->one();			
			if($model) $return = Chinhsach::cache($model);
		}

		elseif($controller == 'cssp'){// Chính sách áp dụng cho từng sản phẩm
			$a = explode('-',$bk_id);
			$model = Sanphamchinhsach::find()
								->andWhere(['spcs_id_chinhsach' => $a[0]])
								->andWhere(['spcs_id_sp' => $a[1]])
								->one();
			if($model) $return = Sanphamchinhsach::cache($model);
		}


		elseif($controller == 'csdm'){// Chính sách áp dụng cho danh mục
			$a = explode('-',$bk_id);
			$model = Danhmucchinhsach::find()
								->andWhere(['dmcs_id_chinhsach' => $a[0]])
								->andWhere(['dmcs_id_danhmuc' => $a[1]])
								->one();
			if($model) $return = Danhmucchinhsach::cache($model);
		}

		elseif($controller == 'tssp'){// Thông số sản phẩm	
			$tssp = explode('-',$bk_id);
			$spdm = Sanphamdanhmuc::find()
								->andWhere(['spdm_id_sp' => $tssp[0]])
								->andWhere(['spdm_id_danhmuc' => $tssp[1]])
								->one();
			if($spdm) $return = Sanphamdanhmuc::cache($spdm);
		}
		elseif($controller == 'spnn'){	 //Sản phẩm ngôn ngữ		
			$spnn = Sanphamngonngu::find()
								->andWhere(['spnn_idsanpham' => $id])
								->andWhere(['spnn_idngonngu' => 1])
								->one();
			if($spnn) $return = Sanphamngonngu::cache($spnn);
		}
		elseif($controller == 'image'){
			$image = Image::find()->where(['image_id' => $id])->one();
			if($image) $return = Image::cache($image);
		}
		elseif($controller == 'danhmuc'){
			$danhmuc = Danhmuc::find()->where(['dm_id' => $id])->one();			
			if($danhmuc) $return = Danhmuc::cache($danhmuc);
		}
		elseif($controller == 'sanpham'){
			$sanpham = (Sanpham::M)::find()->where(['sp_id' => $id])->one();
			if($sanpham) $return = Sanpham::cache($sanpham);
		}
		elseif($controller == 'module'){
			$module = Danhmuc::find()                
               ->andWhere(['dm_recycle' => '2'])
               ->andWhere(['dm_type' => 4]) 
               ->andWhere(['dm_groupmenu' => $id])                
               ->orderBy(['dm_sothutu' => SORT_ASC])
               ->asArray()
               ->all();
            if($module){
                $tree = MyComponent::getChildren($module,0);
                $tree = json_encode($tree);
                $cache->set('module'.$id,$tree);
                $return = $tree;
            }
		}
		
		if(empty($return)){
			if(($type == 'array') && !is_array($return)) $return = [];
			if($type == 'string') $return = '';
		}else{
			if(!is_array($return)){
				$return = json_decode($return,true);
			}
		}		
	}else{
		if(!is_array($return)){
			$return = json_decode($return,true);
		}
	}

	if($controller == 'image'){
		if(!empty($return)){
			$size = $type;
			$size = str_replace('x','/',$size);
			if($size == 'array' || empty($size)){
				$return = Url::to($return['fulllink'],true); //Trả về link full		
			}else{
				$return = Url::to('/thumb/'.$size.'/'.$return['filename'],true); //Trả về filename
			}	

		}else{
			$return = '';
		}
	}


	if($bk_controller == 'sanpham'){
		$ab = new SanphamFront();
		$ab->attributes = $return;
		$ab->update();
		return $ab;
	}elseif($bk_controller == 'baiviet'){
		$ab = new SanphamFront();
		$ab->attributes = $return;
		$ab->update();
		return $ab;
	}

	return $return;
}


public static function _show_phone($a)
{
	if(empty($a)) return '';	
	$arr = preg_split( '/[*;\n]+/', $a );
	$html = '';
	foreach ($arr as $k_a => $v_a) {
		$v_a = trim($v_a);
        $html .= ($k_a == 0?'':' - ') .'<span><a href="tel:'.$v_a.'">'.$v_a.'</a></span>';
    }
    return $html;
}

public static function _show_text($a)
{
	if(empty($a)) return '';	
	$arr = preg_split( '/[*;\n]+/', $a );
	$html = '';
	foreach ($arr as $k_a => $v_a) {
		$v_a = trim($v_a);
        $html .= ($k_a == 0?'':' - ') .'<span><a href="tel:'.$v_a.'">'.$v_a.'</a></span>';
    }
    return $html;
}




public static function _get_link($link = '', $id = '', $type = '')
{
	$config_main = array_merge(
	    require(ROOT_PATH . '/frontend/modules/'.temp.'/config/main.php')
	);
	return (isset($config_main['link'][$type]))?($link .'-'. $config_main['link'][$type].$id.'.html'):'';	
}


public static function _show_link($a)// Hiện thị link từ các module
{
	$url = json_decode($a,true);
	$c = '';
	if($url['s'] == 1){
		$c = '/';
	}
	elseif($url['s'] == 2){

	}
	elseif($url['s'] == 3){

	}
	elseif($url['s'] == 4){
		
	}
	elseif($url['s'] == 5){
		
	}
	elseif($url['s'] == 6){
		
	}
	elseif($url['s'] == 7){
		$c = $url['c'];
	}
	elseif($url['s'] == 8){
		$c = $url['c'];
		$dm = Tuyen::_dulieu('danhmuc',$c);
		if(isset($dm['dm_link'])){
			if(empty($dm['dm_link'])) $dm['dm_link'] = 'thong-so';
			$c = $dm['dm_link'].'-t1'.$c.'.html';		
		}
	}
	elseif($url['s'] == 9){
		
	}
	elseif($url['s'] == 10){
		
	}

	return $c;
	// if(empty($a)) return '';	
	// $arr = preg_split( '/[*;\n]+/', $a );
	// $html = '';
	// foreach ($arr as $k_a => $v_a) {
	// 	$v_a = trim($v_a);
 //        $html .= ($k_a == 0?'':' - ') .'<span><a href="'.$v_a.'">'.$v_a.'</a></span>';
 //    }
 //    return $html;
}



public static function _show_title($a)// Hiện thị title
{	
	if(isset($a['dm_ten_ob'])) $url = $a['dm_ten_ob'];
	if(isset($a['ten_ob'])) $url = $a['ten_ob'];

	if(isset($url)){
		if(!is_array($url)) $url = json_decode($url,true);		

		$c = '';
		if($url['s'] == 1){
			$c = $url['c'];
		}	
		elseif($url['s'] == 3){
			$c = $url['c'];
			$dm = Tuyen::_dulieu('danhmuc',$c);
			$c = $dm['dm_ten'];
		}
		elseif($url['s'] == 4){
			$c = $url['c'];
			$sp = Tuyen::_dulieu('sanpham',$c);
			$c = $sp['sp_tensp'];
		}
		elseif($url['s'] == 5){
			$c = $url['c'];
			$dm = Tuyen::_dulieu('danhmuc',$c);
			$c = $dm['dm_ten'];
		}
		elseif($url['s'] == 6){
			$c = $url['c'];
			$sp = Tuyen::_dulieu('sanpham',$c);
			$c = $sp['sp_tensp'];
		}
		elseif($url['s'] == 8){
			$c = $url['c'];
			$dm = Tuyen::_dulieu('danhmuc',$c);
			$c = $dm['dm_ten'];
		}
		elseif($url['s'] == 9){					
			$list = Cauhinh::Thongtincauhinh();
			$c = $list[$url['c']];
		}
	}

	if(empty($c)){
		if(isset($a['dm_ten'])) $c = $a['dm_ten'];
		if(isset($a['label'])) $c = $a['label'];
	}

	return $c;
	
}

public static function _show_gia($a) //
{
	if(is_numeric($a)){
		return number_format($a) . self::_show_donvitiente() ;
	}
	return $a;
}

public static function _show_gia_discount($a, $amduong = '', $phantram = 1) //
/*
	$a: gia trị truyền vào
	$amduong: dấu + hay -
	$phantram: 1 là số cụ thể, 2 là phần trăm 
*/
{
	if(is_numeric($a)){
		$a = (int)($amduong.$a);

		if($a > 0){
			$tiento = '<div class="up-km">+';
		}
		elseif($a < 0){
			$tiento = '<div class="down-km">';
		}
		else{
			return '';
		}
		return  $tiento . number_format($a) . (($phantram == 2)?'%':self::_show_donvitiente()) .'</div>';
	}
	return $a;
}


public static function _show_donvitiente() //
{
	$donvitiente = self::_dulieu('cauhinh', Cauhinh::tientetinhgia);
	return $donvitiente['child'][$donvitiente['default']];
}


public static function _show_conhang($a = '') //
{
	return Sanpham::getConhangLabel($a);
}


public function template()
{	
	//Trả về tên template
	return temp;
}

public function _dir()
{
	return __DIR__;
}

public function dir_template()
{
	//Trả về thư mục template
	return ROOT_PATH.'/frontend/views/template/'.self::template().'/';
}
public function _include($s = '')
{
	//Trả link file template include
	return ROOT_PATH.'/frontend/views/template/'.self::template().'/_include/'.$s.'.php';
}


const url_dm = '1';
const url_sp = '2';
const url_cm = '3';
const url_bv = '4';

const i = 'd-i';  //data controller
const u = 'd-u';//data url
const s = 'd-s';  //data  reload
const r = 'd-r';
const c = 'd-c';
const t = 'd-t'; // = sea: Tìm kiếm // = show: radio 
const ty = 'd-ty';
const m = 'd-m';  //data modal
const ct = 'd-ct';  //data modal


const tp = 'd-tp';
const lt = 'd-lt';
const wh = 'd-wh';
const ht = 'd-ht';

const st = 'd-st';
const gr = 'd-gr';
// const lk = 'd-lk';

const add = 'd-add'; //data add 

const lk = 'd-lk'; //data link

const postimg = 'ImagePost'; //image post

const type = 'd-type'; //Type danh muc


const lcs = 'd-lcs'; //ListChinhSach, list chinh sach ap dung cho cac danh muc cu ther (Form San pham)
const cc = 'd-cc'; //Count click, đếm 1 cs có bao nhiêu danh mục áp dụng cs đó.

} 