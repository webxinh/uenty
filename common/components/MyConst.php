<?php
namespace common\components;

use Aabc;
use aabc\base\Component;
// use backend\models\Nhomsanpham3;


class MyConst extends Component {

//Model
	


	public $model_table1_item_hienthi = 'Xuất bản';
	public $model_table1_item_an = 'Không hiển thị';
	public $model_table1_item_conhang = 'Còn hàng';
	public $model_table1_item_tamhet = 'Tạm hết';
	public $model_table1_item_ngungkinhdoanh = 'Ngừng kinh doanh';
	public $model_table1_item_6 = '';
	public $model_table1_item_7 = '';
	public $model_table1_item_8 = '';
	public $model_table1_item_9 = '';
	public $model_table1_item_10 = '';

      	


//View _ index	
	public $view_btn_them = 'Thêm';
    public $view_btn_thungrac = 'Thùng rác';
    public $view_btn_reload = 'Reload';
    public $view_btn_themanh = 'Tải thêm ảnh';
    public $view_btn_chonanh = 'Chọn ảnh';

    public $vindex_search_timkiem = 'Tìm kiếm - Bộ lọc';
    public $vindex_search_tenma = 'Tên/mã sản phẩm';
    public $vindex_search_tenma_2 = 'Tên bài viết';
    public $vindex_search_chon = '--Chọn--';
   

//View _ indexrecyvle
	public $vindexrecycle_xoatatca = 'Xóa tất cả';
    public $vindexrecycle_khoiphuc = 'Khôi phục';
    public $vindexrecycle_xoa = 'Xóa';


//View _ gridview
    public $gridview_khongthayketqua = 'Không tìm thấy kết quả nào.!';
    public $gridview_khongthayanh = 'Chưa có ảnh. Vui lòng tải ảnh lên!';
    

//View _ gridview_menu
    public $gridview_menu_suachitiet = 'Chỉnh sửa';
    public $gridview_menu_suatenanh = 'Sửa tên ảnh';
    public $gridview_menu_copyduongdan = 'Copy đường dẫn';
    public $gridview_menu_xemchitiet = 'Xem chi tiết';

    public $gridview_menu_moihon = 'Ảnh mới hơn';
    public $gridview_menu_cuhon = 'Ảnh cũ hơn';
    
    public $gridview_menu_hienthi = 'Xuất bản';
    public $gridview_menu_an = 'Không hiển thị';
    public $gridview_menu_thungrac = 'Xóa tạm';
    public $gridview_menu_up = 'Lên trên';
    public $gridview_menu_down = 'Xuống dưới';


//View _ gridview_selectmultiitem
    public $gridview_selectmultiitem_chonthaotac = '--Chọn thao tác--';
    public $gridview_selectmultiitem_hienthi = 'Xuất bản';
    public $gridview_selectmultiitem_an = 'Không hiển thị';
    public $gridview_selectmultiitem_thungrac = 'Xóa tạm';
    public $gridview_selectmultiitem_suatenanh = 'Sửa tên ảnh';
    public $gridview_selectmultiitem_thuchien = 'Thực hiện';
    
    


//View _ form	
    public $form_tab_thongtinchung = 'Thông tin chung';
    public $form_tab_motasanpham = 'Mô tả sản phẩm';
    public $form_tab_toiuuseo = 'Tối ưu SEO';
    







} ?>