<?php
namespace common\cont;
use Aabc;
use aabc\helpers\ArrayHelper;


class _SANPHAM { 

public $tuyen = 'backend\models\Sanpham';
const M = 'backend\models\Sanpham';
const S = 'backend\models\SanphamSearch';
const t = 'sanpham';
const T = 'Sanpham';

const table = 'db_sanpham';



 public static function one($id='')
	{      
	  $t = Aabc::$app->dulieu;
	  $data = $t->get('get_sp_'.$id);
	    if ($data === false){
	        $data = (self::M)::find()->andWhere([self::sp_id => $id])->asArray()->one();
	        $t->set('get_sp_'.$id, $data);
	    }
	    return $data;
	}


 //const  = 'sp_id';
 //const  = 'sp_ma';
 //const  = 'sp_type';
 //const  = 'sp_tensp';
 //const  = 'sp_masp';
 //const  = 'sp_linkseo';
 //const  = 'sp_motaseo';
 //const  = 'sp_images';
 //const  = 'sp_status';
 //const  = 'sp_recycle';
 //const  = 'sp_conhang';
 //const  = 'sp_view';
 //const  = 'sp_ngaytao';
 //const  = 'sp_ngayupdate';
 //const  = 'sp_idnguoitao';
 //const  = 'sp_idnguoiupdate';
 //const  = 'sp_id_ncc';
 //const  = 'sp_id_thuonghieu';
 //const  = 'sp_gia';
 //const  = 'sp_giakhuyenmai';
 //const  = 'sp_soluong';
 //const  = 'sp_soluongfake';
 //const  = 'sp_soluotmua';
 //const  = 'sp_id_danhmuc';
 //const  = 'sp_id_chinhsach';

 const sp_id = 'sp_id';
 const sp_ma = 'sp_ma';
 const sp_type = 'sp_type';
 const sp_tensp = 'sp_tensp';
 const sp_masp = 'sp_masp';
 const sp_linkseo = 'sp_linkseo';
 const sp_motaseo = 'sp_motaseo';
 const sp_linkanhdaidien = 'sp_linkanhdaidien';
 const sp_images = 'sp_images';
 const sp_status = 'sp_status';
 const sp_recycle = 'sp_recycle';
 const sp_conhang = 'sp_conhang';
 const sp_view = 'sp_view';
 const sp_ngaytao = 'sp_ngaytao';
 const sp_ngayupdate = 'sp_ngayupdate';
 const sp_idnguoitao = 'sp_idnguoitao';
 const sp_idnguoiupdate = 'sp_idnguoiupdate';
 const sp_id_ncc = 'sp_id_ncc';
 const sp_id_thuonghieu = 'sp_id_thuonghieu';
 const sp_gia = 'sp_gia';
 const sp_giakhuyenmai = 'sp_giakhuyenmai';
 const sp_soluong = 'sp_soluong';
 const sp_soluongfake = 'sp_soluongfake';
 const sp_soluotmua = 'sp_soluotmua';

//Thêm
 const sp_id_danhmuc = 'sp_id_danhmuc';
 const sp_id_chinhsach = 'sp_id_chinhsach';





 const __sp_id = 'ID';
 const __sp_ma = 'ID';
 const __sp_type = 'sp_type';
 const __sp_tensp = 'Tên sản phẩm';
 const __sp_masp = 'Mã sản phẩm';
 const __sp_linkseo = 'Link seo';
 const __sp_motaseo = 'Mô tả seo';
 const __sp_linkanhdaidien = 'sp_linkanhdaidien';
 const __sp_images = 'sp_images';
 const __sp_status = 'Trạng thái';
 const __sp_recycle = 'sp_recycle';
 const __sp_conhang = 'Tình trạng';
 const __sp_view = 'Lượt xem';
 const __sp_ngaytao = 'Ngày đăng';
 const __sp_ngayupdate = 'Ngày cập nhật';
 const __sp_idnguoitao = 'Người đăng';
 const __sp_idnguoiupdate = 'Người cập nhật';
 const __sp_id_ncc = 'NCC';
 const __sp_id_thuonghieu = 'Thương hiệu';
 const __sp_gia = 'Giá';
 const __sp_giakhuyenmai = 'sp_giakhuyenmai';
 const __sp_soluong = 'Số lượng';
 const __sp_soluongfake = 'sp_soluongfake';
 const __sp_soluotmua = 'sp_soluotmua';
//Thêm
 const __sp_id_danhmuc = 'Danh mục';
 const __sp_id_chinhsach = 'Chính sách';





 const __sp_id_2 = 'ID';
 const __sp_ma_2 = 'ID';
 const __sp_type_2 = 'sp_type';
 const __sp_tensp_2 = 'Tên bài viết';
 const __sp_masp_2 = 'Mã sản phẩm';
 const __sp_linkseo_2 = 'Link seo';
 const __sp_motaseo_2 = 'Mô tả seo';
 const __sp_linkanhdaidien_2 = 'sp_linkanhdaidien';
 const __sp_images_2 = 'sp_images';
 const __sp_status_2 = 'Hiển thị';
 const __sp_recycle_2 = 'sp_recycle';
 const __sp_conhang_2 = 'Tình trạng';
 const __sp_view_2 = 'sp_view';
 const __sp_ngaytao_2 = 'sp_ngaytao';
 const __sp_ngayupdate_2 = 'sp_ngayupdate';
 const __sp_idnguoitao_2 = 'sp_idnguoitao';
 const __sp_idnguoiupdate_2 = 'sp_idnguoiupdate';
 const __sp_id_ncc_2 = 'NCC';
 const __sp_id_thuonghieu_2 = 'Thương hiệu';
 const __sp_gia_2 = 'Giá';
 const __sp_giakhuyenmai_2 = 'sp_giakhuyenmai';
 const __sp_soluong_2 = 'Số lượng';
 const __sp_soluongfake_2 = 'sp_soluongfake';
 const __sp_soluotmua_2 = 'sp_soluotmua';
//Thêm
 const __sp_id_danhmuc_2 = 'Chuyên mục';

} 