<?php
namespace common\components;

use Aabc;
use aabc\base\Component;
use backend\models\Nhomsanpham3;
use backend\models\Nhomsanpham6;
use backend\models\Danhmuc;
use common\cont\D;

use backend\models\Sanphamngonngu;
use backend\models\Ngonngu;
// $models = Book::find()->where('id != :id and type != :type', ['id'=>1, 'type'=>1])->all();

class MyComponent extends Component {
     
    
    public function dangonngu($data, $form,$ids,$thuoctinh,$id1,$new,$idcss,$type = ''){  
            // $data : Danh sách nhiều nhiều(Sanpham-Ngonngu)
            $ngonngus = Ngonngu::getAll();
            // $label = $new;
            $new = new $new;
            // print_r($data);
            // die;

            if($id1 == NULL ){ $id1 = '0';}            
            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                echo '<div class="dnn"><fieldset>';
                echo '<ul class="nav nav-tabs">';
                foreach ($ngonngus as $key => $ngonngu) {
                    echo '<li class="'. ( ($key == 0 ? 'active' : '') ) .'"><a data-toggle="tab" href="#'.$idcss.$key.'"><img src="'.$ngonngu[Ngonngu::ngonngu_flag].'" /> '.$ngonngu[Ngonngu::ngonngu_ten].'</a></li>';
                }                
                echo '</ul>';
                echo '<div class="tab-content">';
            }
            // die;
            foreach ($ngonngus as $keyngonngu => $ngonngu) {                
                $dacobanghi = 0;
                if($data != NULL){
                    foreach ($data as $keydata => $value2) {
                        if(($value2[$ids[1]]) == $ngonngu[Ngonngu::ngonngu_id]) {
                            $dacobanghi = 1;
                            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                                echo '<div id="'.$idcss.$keyngonngu.'" class="tab-pane fade in '. ( ($keyngonngu == 0 ? 'active' : '') ) .'"> ';
                            }                
                            echo  $form->field($value2, '['.$keyngonngu.']'.$ids[0],['options' => ['class' => 'hide']])->hiddenInput()->label(false);
                            echo  $form->field($value2, '['.$keyngonngu.']'.$ids[1],['options' => ['class' => 'hide']])->hiddenInput()->label(false);                              
                            foreach ($thuoctinh as $valuethuoctinh) {
                                $__valuethuoctinh = '__'.$valuethuoctinh.$type;
                                // $tt__valuethuoctinh = 'tt__'.$valuethuoctinh.$type;
                                echo  $form->field($value2, '['.$keyngonngu.']'.$valuethuoctinh, ['options' => ['class' => 'pt120']])->textarea([
                                    'class' => 'form-control '.$idcss,
                                    'rows' => 3,
                                    'maxlength' => true,
                                    // 'placeholder' => Aabc::$app->_sanphamngonngu->$__valuethuoctinh,
                                    // 'data-html' => 'true',
                                    // 'data-placement' => 'top',
                                    // 'data-trigger' => 'focus',
                                    // 'data-toggle' => 'tooltip',
                                    // 'title' => Aabc::$app->_sanphamngonngu->$tt__valuethuoctinh 

                                  ]);
                            }    
                            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                                echo '</div>';                      
                            } 
                        }
                    }
                }
                
                if ($dacobanghi == 0) {  
                //Nếu chưa có bản ghi với ngôn ngữ này thì tạo form mới                    
                    if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '<div id="'.$idcss.$keyngonngu.'" class="tab-pane fade in '. ( ($keyngonngu == 0 ? 'active' : '') ) .'"> '; 
                    }                          
                        echo  $form->field($new, '['.$keyngonngu.']'.$ids[0],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $id1])->label(false);
                        echo  $form->field($new, '['.$keyngonngu.']'.$ids[1],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $ngonngu[Ngonngu::ngonngu_id]])->label(false);   
                        foreach ($thuoctinh as $valuethuoctinh) {
                            $__valuethuoctinh = '__'.$valuethuoctinh.$type;
                            $tt__valuethuoctinh = 'tt__'.$valuethuoctinh.$type;
                            echo  $form->field($new, '['.$keyngonngu.']'.$valuethuoctinh, ['options' => ['class' => 'pt120']])->textarea([
                              'class' => 'form-control '.$idcss,
                              'rows' => 3,
                              'maxlength' => true,
                              // 'placeholder' => Aabc::$app->_sanphamngonngu->$__valuethuoctinh ,
                              // 'data-html' => 'true',
                              // 'data-placement' => 'top',
                              // 'data-trigger' => 'focus',
                              // 'data-toggle' => 'tooltip',
                              // 'title' => Aabc::$app->_sanphamngonngu->$tt__valuethuoctinh
                            ]);
                        }                         
                    if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '</div>';                      
                    } 
                }   
            }            

            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                echo '<div class="xd"></div>';
                echo '</div>';
                echo '</fieldset></div>';
            }
        }


         public function dangonngutext($data, $form,$ids,$thuoctinh,$id1,$new,$idcss){     
            $ngonngus = Ngonngu::getAll();
            // $label = $new;
            $new = new $new;

            if($id1 == NULL ){ $id1 = '0';}            
            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                // echo '<div class="dnn"><fieldset><legend>Đa ngôn ngữ</legend>';
                echo '<div class="dnn"><fieldset>';
                echo '<ul class="nav nav-tabs">';
                foreach ($ngonngus as $key => $value) {
                    echo '<li class="'. ( ($key == 0 ? 'active' : '') ) .'"><a data-toggle="tab" href="#'.$idcss.$key.'"><img src="'.$value[Ngonngu::ngonngu_flag].'" /> '.$value[Ngonngu::ngonngu_ten].'</a></li>';
                }                
                echo '</ul>';
                echo '<div class="tab-content">';
            }
            
            foreach ($ngonngus as $keyngonngu => $value) {                
                $dacobanghi = 0;
                if($data != NULL){
                    foreach ($data as $keydata => $value2) {
                        if(($value2[$ids[1]]) == $value[Ngonngu::ngonngu_id]) {                            
                            $dacobanghi = 1;
                            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                                echo '<div id="'.$idcss.$keyngonngu.'" class="tab-pane fade in '. ( ($keyngonngu == 0 ? 'active' : '') ) .'"> ';
                            }                
                            echo  $form->field($value2, '['.$keyngonngu.']'.$ids[0])->hiddenInput()->label(false);
                            echo  $form->field($value2, '['.$keyngonngu.']'.$ids[1])->hiddenInput()->label(false);                           
                            foreach ($thuoctinh as $valuethuoctinh) {




                                echo  $form->field($value2, '['.$keyngonngu.']'.$valuethuoctinh)->textarea(['rows' => '20']);

                                echo '<script>';
                                echo   'CKEDITOR.replace("'.$ids[2].'-'.$keyngonngu.'-'.$valuethuoctinh.'");';
                                echo '</script>';
                            }    
                            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                                echo '</div>';                      
                            } 
                        }
                    }
                }
                
                if ($dacobanghi == 0) {  
                //Nếu chưa có bản ghi với ngôn ngữ này thì tạo form mới                       
                    if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '<div id="'.$idcss.$keyngonngu.'" class="tab-pane fade in '. ( ($keyngonngu == 0 ? 'active' : '') ) .'"> '; 
                    }                          
                        echo  $form->field($new, '['.$keyngonngu.']'.$ids[0],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $id1])->label(false);
                        echo  $form->field($new, '['.$keyngonngu.']'.$ids[1],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $value->ngonngu_id])->label(false);   
                        foreach ($thuoctinh as $valuethuoctinh) {
                            
                            if($valuethuoctinh == 'o7'){
                              $new[$valuethuoctinh] = '';
                            }

                            if($valuethuoctinh == 'l2'){
                              $new[$valuethuoctinh] = '';
                            }

                            echo  $form->field($new, '['.$keyngonngu.']'.$valuethuoctinh)->textarea(['rows' => '20']);
                            echo '<script>';
                            echo   'CKEDITOR.replace("'.$ids[2].'-'.$keyngonngu.'-'.$valuethuoctinh.'");';
                            echo '</script>';
                        }                         
                    if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '</div>';                      
                    } 
                }   
            }            

            if(count($ngonngus) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                echo '<div class="xd"></div>';
                echo '</div>';
                echo '</fieldset></div>';
            }
        }

        

        public function homnay($date)
        {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $songay = (strtotime(date("Y-m-d")) - strtotime($date)) /  86400;
              if($songay == -1){
                  return 'Ngày mai';
              }elseif ($songay == 0) {
                  return 'Hôm nay';
              }elseif ($songay == 1) {
                  return 'Hôm qua';
              }else{
                  return date("Y-m-d", strtotime($date)); 
              }
        }








      // public function dangonngu($model,$ngonngu,$form,$ids,$thuoctinh,$id1,$new,$idcss){
      //       if($id1 == NULL ){ $id1 = '0';}
            
      //       if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
      //           echo '<div class="dnn"><fieldset><legend>Đa ngôn ngữ</legend>';
      //           echo '<ul class="nav nav-tabs">';
      //           foreach ($ngonngu as $key => $value) {
      //               echo '<li class="'. ( ($key == 0 ? 'active' : '') ) .'"><a data-toggle="tab" href="#'.$idcss.$key.'">'.$value->ngonngu_ten.'</a></li>';
      //           }                
      //           echo '</ul>';
      //           echo '<div class="tab-content">';
      //       }
            
      //       foreach ($ngonngu as $key => $value) {
      //           $truyvan = $new::find()
      //                       ->andwhere([$ids[0] => $id1 ])
      //                       ->andwhere([$ids[1] => $value->ngonngu_id])
      //                       ->all();
      //           if ($truyvan == NULL) {  
      //           //Nếu chưa có bản ghi với ngôn ngữ này thì tạo form mới
      //               // $value2 = new Sanphamngonngu();
      //               if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
      //                   echo '<div id="'.$idcss.$key.'" class="tab-pane fade in '. ( ($key == 0 ? 'active' : '') ) .'"> '; 
      //               }                          
      //                   echo  $form->field($new, '['.$key.']'.$ids[0],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $id1])->label(false);
      //                   echo  $form->field($new, '['.$key.']'.$ids[1],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $value->ngonngu_id])->label(false);                    
                        

      //                   foreach ($thuoctinh as $value) {
      //                       echo  $form->field($new, '['.$key.']'.$value)->textInput(['maxlength' => true]);
      //                   }
                         
      //               if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
      //                   echo '</div>';                      
      //               } 

      //           } else {
      //           //Nếu đã có thì tạo form update
      //               if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
      //                   echo '<div id="'.$idcss.$key.'" class="tab-pane fade in '. ( ($key == 0 ? 'active' : '') ) .'"> ';                        
      //               }
      //               foreach ($truyvan as $key2 => $value2) {     
      //                   // if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ                   
      //                       echo  $form->field($value2, '['.$key.']'.$ids[0])->hiddenInput()->label(false);
      //                       echo  $form->field($value2, '['.$key.']'.$ids[1])->hiddenInput()->label(false);
      //                   // } 
      //                  foreach ($thuoctinh as $value) {
      //                       echo  $form->field($value2, '['.$key.']'.$value)->textInput(['maxlength' => true]);
      //                   }                    
      //               }
      //               if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
      //                   echo '</div>';                      
      //               } 
      //           }
      //       }            

      //       if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
      //           echo '<div class="xd"></div>';
      //           echo '</div>';
      //           echo '</fieldset></div>';
      //       }
      //   }    

    // public function dangonngutext($model,$ngonngu,$form,$ids,$thuoctinh,$id1,$new,$idcss){
    //         if($id1 == NULL ){ $id1 = '0';}
            
    //         if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
    //             echo '<div class="dnn"><fieldset><legend>Đa ngôn ngữ</legend>';
    //             echo '<ul class="nav nav-tabs">';
    //             foreach ($ngonngu as $key => $value) {
    //                 echo '<li class="'. ( ($key == 0 ? 'active' : '') ) .'"><a data-toggle="tab" href="#'.$idcss.$key.'">'.$value->ngonngu_ten.'</a></li>';
    //             }                
    //             echo '</ul>';
    //             echo '<div class="tab-content">';
    //         }
            
    //         foreach ($ngonngu as $key => $value) {
    //             $truyvan = $new::find()
    //                         ->andwhere([$ids[0] => $id1 ])
    //                         ->andwhere([$ids[1] => $value->ngonngu_id])
    //                         ->all();
    //             if ($truyvan == NULL) {  
    //             //Nếu chưa có bản ghi với ngôn ngữ này thì tạo form mới
    //                 // $value2 = new Sanphamngonngu();
    //                 if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
    //                     echo '<div id="'.$idcss.$key.'" class="tab-pane fade in '. ( ($key == 0 ? 'active' : '') ) .'"> '; 
    //                 }                          
    //                     echo  $form->field($new, '['.$key.']'.$ids[0],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $id1])->label(false);
    //                     echo  $form->field($new, '['.$key.']'.$ids[1],['options' => ['class' => 'hide']])->hiddenInput(['value'=> $value->ngonngu_id])->label(false);       
    //                     foreach ($thuoctinh as $value) {
    //                         echo  $form->field($new, '['.$key.']'.$value)->textarea(['rows' => '200']);
    //                         echo '<script>';
    //                         echo   'CKEDITOR.replace("'.$ids[2].'-'.$key.'-'.$value.'");';
    //                         echo '</script>';
    //                     }
                         
    //                 if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
    //                     echo '</div>';                      
    //                 } 

    //             } else {
    //             //Nếu đã có thì tạo form update
    //                 if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
    //                     echo '<div id="'.$idcss.$key.'" class="tab-pane fade in '. ( ($key == 0 ? 'active' : '') ) .'"> ';                        
    //                 }
    //                 foreach ($truyvan as $key2 => $value2) {     
    //                     // if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ                   
    //                         echo  $form->field($value2, '['.$key.']'.$ids[0])->hiddenInput()->label(false);
    //                         echo  $form->field($value2, '['.$key.']'.$ids[1])->hiddenInput()->label(false);
    //                     // } 
    //                    foreach ($thuoctinh as $value) {

    //                         $value2[$value] = str_replace('img_3677-291-436','img_3262-288-452', $value2[$value]);

    //                         // echo "<pre>";
    //                         // print_r($value2[$value]);
    //                         echo  $form->field($value2, '['.$key.']'.$value)->textarea(['rows' => '200']);

    //                         echo '<script>';
    //                         echo   'CKEDITOR.replace("'.$ids[2].'-'.$key.'-'.$value.'");';
    //                         echo '</script>';

    //                     }                   
    //                 }
    //                 if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
    //                     echo '</div>';                      
    //                 } 
    //             }
    //         }            

    //         if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
    //             echo '<div class="xd"></div>';
    //             echo '</div>';
    //             echo '</fieldset></div>';
    //         }
    //     }

     public function sothutudanhmuc($categories, $type = '',$groupmenu){
        $_SESSION["menu"] = [];
        $_SESSION["menu_level0"] = 0;
        $_SESSION["menu_level1"] = 0;
        $_SESSION["menu_level2"] = 0;
        $cache = Aabc::$app->dulieu; 

        $this->_sothutudanhmuc($categories, $parent_id = 0, $char = '');

        if($type == 1){          
          $cache->set('menu', $_SESSION["menu"]);
        }

        if($type == 4){
            $array = Danhmuc::find()                
               ->andWhere([Aabc::$app->_danhmuc->dm_recycle => '2'])
               ->andWhere([Aabc::$app->_danhmuc->dm_type => $type]) 
               ->andWhere(['dm_groupmenu' => $groupmenu])                
               ->orderBy([Aabc::$app->_danhmuc->dm_sothutu=>SORT_ASC])
               ->asArray()->all();

            $tree = $this->getChildren($array,0);

            $cache->set('module'.$groupmenu, json_encode($tree) );
 
        }
    }
      
  
    public function getChildren(& $array,$p) {      
      $r = [];
      foreach($array as $key => $row) {
        if ($row['dm_idcha'] == $p) {
          $r[$row['dm_id']]['label'] = $row['dm_ten'];
          $r[$row['dm_id']]['url'] = $row['dm_link'];
          $r[$row['dm_id']]['icon'] = $row['dm_icon'];
          $r[$row['dm_id']]['background'] = $row['dm_background'];
          $r[$row['dm_id']]['email'] = $row['dm_email'];
          $r[$row['dm_id']]['phone'] = $row['dm_phone'];
          $r[$row['dm_id']]['zalo'] = $row['dm_zalo'];
          $r[$row['dm_id']]['skype'] = $row['dm_skype'];
          $r[$row['dm_id']]['child'] = MyComponent::getChildren($array,$row['dm_id']);
        }
      }
      return $r;
    }



     public function _sothutudanhmuc($categories, $parent_id = 0, $char = ''){     
            $_Danhmuc = Aabc::$app->_model->Danhmuc;  
          
            foreach ($categories as $key => $item)
            {                
                if ($item[Aabc::$app->_danhmuc->dm_idcha] == $parent_id)
                {    
                    //Cập nhật số thứ tự hiển thi theo tree 
                    $catsave = $_Danhmuc::find()
                                ->andWhere([Aabc::$app->_danhmuc->dm_id => $item[Aabc::$app->_danhmuc->dm_id]])
                                ->one();
                    $catsave[Aabc::$app->_danhmuc->dm_sothutu] = $_SESSION["dem"];
                    $catsave[Aabc::$app->_danhmuc->dm_char] = $char . $catsave[Aabc::$app->_danhmuc->dm_ten];
                    $catsave[Aabc::$app->_danhmuc->dm_level] = substr_count($char, '|-----');
                    
                    $level = substr_count($char, '|-----');

                    if(is_array($catsave[Aabc::$app->_danhmuc->dm_link])){
                        $link = json_encode($catsave[Aabc::$app->_danhmuc->dm_link]);
                    }else{
                        $link = $catsave[Aabc::$app->_danhmuc->dm_link].'-'.D::url_dm.$catsave[Aabc::$app->_danhmuc->dm_id].'.html';
                        // $link = $catsave[Aabc::$app->_danhmuc->dm_link];
                    }

                    $menu_item = [
                          'label' => $catsave[Aabc::$app->_danhmuc->dm_ten],
                          'link' => $link,
                          'icon' => $catsave[Aabc::$app->_danhmuc->dm_icon],
                          'background' => $catsave[Aabc::$app->_danhmuc->dm_background],
                      ];

                    if($catsave[Aabc::$app->_danhmuc->dm_type] == 1){
                        if($level == 0){
                          $_SESSION["menu_level0"] += 1;
                          $_SESSION["menu_level1"] = 0;
                          $_SESSION["menu"][$_SESSION["menu_level0"]] = $menu_item;
                        }elseif($level == 1){
                          $_SESSION["menu_level1"] += 1;
                          $_SESSION["menu_level2"] = 0;
                          $_SESSION["menu"][$_SESSION["menu_level0"]]['child'][$_SESSION["menu_level1"]] = $menu_item;
                        }elseif($level == 2){
                          $_SESSION["menu_level2"] += 1;
                          $_SESSION["menu"][$_SESSION["menu_level0"]]['child'][$_SESSION["menu_level1"]]['child'][$_SESSION["menu_level2"]] = $menu_item;
                        }
                    }
                    
                    Danhmuc::updateAll([
                        'dm_sothutu' => $catsave[Aabc::$app->_danhmuc->dm_sothutu],
                        'dm_char' => $catsave[Aabc::$app->_danhmuc->dm_char],
                        'dm_level' => $catsave[Aabc::$app->_danhmuc->dm_level],
                    ],['dm_id' => $catsave->dm_id]);

                    // if(!$catsave->save()){
                        // Aabc::error($catsave->errors);
                        // Aabc::error($catsave->dm_link);
                    // }

                    $_SESSION["dem"] += 1;
                                                                     
                    unset($categories[$key]);       
                    $this->_sothutudanhmuc($categories, $item[Aabc::$app->_danhmuc->dm_id], $char.'|----- ');
                }
            }            
        }    
} ?>