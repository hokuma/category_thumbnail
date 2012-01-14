<?php
 /*
  Plugin Name: Category Thumbnail
  Plugin URI: https://github.com/hokuma/category_thumbnail
  Description: カテゴリに画像を追加する
  Version: 0.5.0
  Author: Hidearu Okuma
  Author URI: http://hokuma.net/
  Wordpress Version: 3.3
 */

add_action("edit_category_form_fields", "_add_thumbnail_field_to_edit");
add_action("edit_category", "_update_thumbnail");
add_action("delete_category", "_delete_thumbnail");

function _update_thumbnail($category_id){
    if(!wp_verify_nonce($_POST["_update_category_thumbnail"], "update_category_thumbnail")){
        return;
    }
    $id = media_handle_upload("tag-thumbnail", null);
    if(!is_wp_error($id)){
        update_option("_category_thumbnail_" . $category_id, $id);
    }
}

function _delete_thumbnail($category_id){
    $img_id = get_option("_category_thumbnail_" . $category_id);
    delete_option("_category_thumbnail_" . $category_id);
    if(is_numeric($img_id)){
        wp_delete_attachment($img_id, true);
    }
}

function _add_thumbnail_field_to_edit($tag){
    $html  = "<tr class='form-field'>";
    $html .= "<th scope='row' valign='top'><label for='tag-thumbnail'>カテゴリ画像</label></th>";
    $html .= "<td>" . the_category_thumbnail($tag->term_id, array(), false) . "<input type='file' name='tag-thumbnail' id='tag-thumbnail' /></td>";
    $html .= "</tr>";
    $html .= wp_nonce_field("update_category_thumbnail", "_update_category_thumbnail", true, false);
    $html .= "<script type='text/javascript'>";
    $html .= "var dom = document.getElementById('edittag');";
    $html .= "dom.setAttribute('enctype', 'multipart/form-data');";
    $html .= "</script>";
    echo $html;
}

/**
 * 指定されたカテゴリのサムネイルのimgタグを生成する。
 * 
 * @param id 表示対象のカテゴリのID
 * @param args オプションの連想配列。
 *   size: thumbnail, medium(default),large
 *   alt: cateogryの名前(default)
 */
function the_category_thumbnail($id, $args = array(), $echo = true){

    $img_id = get_option("_category_thumbnail_" . $id);
    if(!is_numeric($img_id)){
        return;
    }
    $size = "medium";
    if(isset($args["size"])){
        $size = $args["size"];
        unset($args["size"]);
    }
    $category = get_category($id, ARRAY_A);
    $default_alt = $category["name"];

    $defaults = array(
                      "class" => "",
                      "alt" => $default_alt
                      );
    
    $attributes = wp_parse_args($args, $defaults);

    list($img_src, $w, $h) = image_downsize($img_id, $size);
    $html = '<img src="' . esc_attr($img_src) . '" ';
    foreach($attributes as $attr => $val){
        $html .= esc_attr($attr) . '="' . esc_attr($val) . '" ';
    }
    $html .= " />";
    if($echo){
        echo $html;
    }else{
        return $html;
    }
        
}


?>