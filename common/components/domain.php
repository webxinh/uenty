<?php
namespace common\components;
use Aabc;
use aabc\base\Component;
class domain extends Component { 

 public $table = 'db_domain';

 // public $ = 'dm_id';
 // public $ = 'dm_domain';
 // public $ = 'dm_length';
 // public $ = 'dm_status';
 // public $ = 'dm_recycle';
 // public $ = 'dm_tiemnang';
 // public $ = 'dm_chude';

 public $dm_id = 'dm_id';
 public $dm_domain = 'dm_domain';
 public $dm_length = 'dm_length';
 public $dm_status = 'dm_status';
 public $dm_recycle = 'dm_recycle';
 public $dm_tiemnang = 'dm_tiemnang';
 public $dm_chude = 'dm_chude';
 public $dm_email = 'dm_email';
 public $dm_source = 'dm_source';
 public $dm_timedownload = 'dm_timedownload';


 public $__dm_tiemnang = 'Responsive';
 public $__dm_id = 'ID';
 public $__dm_domain = 'Domain';
 public $__dm_length = 'Length';
 public $__dm_status = 'Status';
 public $__dm_recycle = 'Thùng rác';
 public $__dm_chude = 'Chủ đề';
 public $__dm_email = 'Email';
 public $__dm_source = 'Source';
 public $__dm_timedownload = 'Thời gian tải';


 public $dm_status_1 = 'Lỗi 0';
 public $dm_status_2 = 'Lỗi < 10,000';
 public $dm_status_3 = 'Chưa kiểm tra';
 public $dm_status_4 = 'Tạm';
 public $dm_status_5 = 'Đẹp';
 public $dm_status_6 = 'Xấu';
 public $dm_status_7 = 'OK';
 public $dm_status_8 = 'Bỏ qua';
 

} ?>