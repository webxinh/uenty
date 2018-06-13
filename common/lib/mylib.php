<?php
class MyLib {
    public function dangonngu($model,$ngonngu,$form,$ids,$thuoctinh,$id1){    
        echo "hello!";

        if($id1 == NULL ){ $id1 = '0';}
            
            if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                echo '<div class="dnn"><fieldset><legend>Đa ngôn ngữ</legend>';
                echo '<ul class="nav nav-tabs">';
                foreach ($ngonngu as $key => $value) {
                    echo '<li class="'. ( ($key == 0 ? 'active' : '') ) .'"><a data-toggle="tab" href="#a'.$key.'">'.$value->ngonngu_ten.'</a></li>';
                }                
                echo '</ul>';
                echo '<div class="tab-content">';
            }
            
            foreach ($ngonngu as $key => $value) {
                $truyvan = Sanphamngonngu::find()
                            ->andwhere([$ids[0] => $id1 ])
                            ->andwhere([$ids[1] => $value->ngonngu_id])
                            ->all();
                if ($truyvan == NULL) {  
                //Nếu chưa có bản ghi với ngôn ngữ này thì tạo form mới
                    $value2 = new Sanphamngonngu();
                    if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '<div id="a'.$key.'" class="tab-pane fade in '. ( ($key == 0 ? 'active' : '') ) .'"> '; 
                    }                          
                        echo  $form->field($value2, '['.$key.']'.$ids[0])->hiddenInput(['value'=> '0'])->label(false);
                        echo  $form->field($value2, '['.$key.']'.$ids[1])->hiddenInput(['value'=> $value->ngonngu_id])->label(false);                    
                        

                        foreach ($thuoctinh as $value) {
                            echo  $form->field($value2, '['.$key.']'.$value)->textInput(['maxlength' => true]);
                        }
                         
                    if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '</div>';                      
                    } 

                } else {
                //Nếu đã có thì tạo form update
                    if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '<div id="a'.$key.'" class="tab-pane fade in '. ( ($key == 0 ? 'active' : '') ) .'"> ';                        
                    }
                    foreach ($truyvan as $key2 => $value2) {     
                        // if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ                   
                            echo  $form->field($value2, '['.$key.']spnn_idsanpham')->hiddenInput()->label(false);
                            echo  $form->field($value2, '['.$key.']spnn_idngonngu')->hiddenInput()->label(false);
                        // } 
                       foreach ($thuoctinh as $value) {
                            echo  $form->field($value2, '['.$key.']'.$value)->textInput(['maxlength' => true]);
                        }                    
                    }
                    if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                        echo '</div>';                      
                    } 
                }
            }            

            if(count($ngonngu) > 1){ //Nếu có nhiều hơn 1 ngôn ngữ
                echo '</div>';
                echo '</fieldset></div>';
            }
        }    
}