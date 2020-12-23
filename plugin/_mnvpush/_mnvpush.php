<?php

/*
Plugin Name: _mnvpush
Description: Pusshbot
Version: 1.0
Author: Nikolay Vladimirovich
Author URI:
Plugin URI:
*/

// ------------------------------------------------
// Создаем страницу настроек плагина
// ------------------------------------------------
//
function add_plugin_page(){
  add_options_page( 'Настройки push', 'push', 'manage_options', 'push_slug', 'push_options_page_output' );
}
add_action('admin_menu', 'add_plugin_page');

function push_options_page_output(){
  ?>
  <div class="wrap">
    <h2><?php echo get_admin_page_title() ?></h2>

    <form action="options.php" method="POST">
      <?php settings_fields( 'push_group' ); ?>
      <?php do_settings_sections( 'push_page' ); ?>
      <?php submit_button(); ?>
    </form>
  </div>
  <?
}

// ------------------------------------------------
// Регистрируем настройки
// ------------------------------------------------
//
function plugin_settings(){ 
    // параметры: $option_group, $option_name, $sanitize_callback
    register_setting( 'push_group', 'push_option');

    // параметры: $id, $title, $callback, $page
    add_settings_section( 'push_id', 'Основные настройки', '', 'push_page' ); 

    // параметры: $id, $title, $callback, $page, $section, $args
    add_settings_field('opt_push_ID', 'Application ID (Код)', 'opt_push_ID_APP', 'push_page', 'push_id' );
    add_settings_field('opt_push_Secret', 'Application Secret (Секретный  ключ приложения)', 'opt_push_Secret_APP', 'push_page', 'push_id' );
    add_settings_field('opt_push_Token', 'Application token (Token приложения)', 'opt_push_Token_APP', 'push_page', 'push_id' );
}
add_action('admin_init', 'plugin_settings');



// заполняем опцию  - ID приложения
function opt_push_ID_APP(){
  $val = get_option('push_option');
  $val = $val['opt_push_ID'];
  ?>
  <input type="text" name="push_option[opt_push_ID]" size="40" value="<? echo esc_attr( $val ) ?>" />
  <?
}



// заполняем опцию - секретный ключ приложения
function opt_push_Secret_APP(){
    $val = get_option('push_option');
    $val = $val['opt_push_Secret'];
    ?>
    <input type="text" name="push_option[opt_push_Secret]" size="40" value="<? echo esc_attr( $val ) ?>" />
    <?
}





// заполняем опцию - токен приложения
function opt_push_Token_APP(){
    $val = get_option('push_option');
    $val = $val['opt_push_Token'];
    ?>
    <input type="text" name="push_option[opt_push_Token]" size="200" value="<? echo esc_attr( $val ) ?>" />
    <?
}




//*************************



function register_push() {    // Подключаем пользовательские типы данных
    $labels = array(
      'name' => 'Push-отправка', // Основное название типа записи
      'singular_name' => 'push-отправка', // отдельное название записи типа Push-
      'add_new' => 'Добавить новую',
      'add_new_item' => 'Добавить новую push-отправку',
      'edit_item' => 'Редактировать push',
      'new_item' => 'Новвый push',
      'view_item' => 'Посмотреть push',
      'search_items' => 'Найти push-отправку',
      'not_found' =>  'Отправок не найдено',
      'not_found_in_trash' => 'В корзине отправовк не найдено',
      'parent_item_colon' => '',
      'menu_name' => 'Push-отправки'

    );
/*
register_meta_box_cb(строка)
callback функция, которая будет срабатывать при установки мета блоков для страницы создания/редактирования этого типа записи. Используйте remove_meta_box() и add_meta_box() в callback функции.
По умолчанию: нет
*/
    
    $args = array(
      'labels' => $labels,
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'rewrite' => true,
      'capability_type' => 'post',
      'has_archive' => true,
      'menu_icon'   => 'dashicons-megaphone',
      'hierarchical' => false,
      'menu_position' => null,
      'supports' => array('title','editor','author','thumbnail','excerpt','comments', 'custom-fields')
    );
    register_post_type('pushbot', $args);
}
add_action( 'init', 'register_push');




// включаем обновление полей при сохранении
add_action('save_post', 'mnv_pushbots', 0);

/* Сохраняем данные, при сохранении поста */
function mnv_pushbots( $post_id ){

    $post_type = get_post_type($post_id);
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false; // если это автосохранение
    if ( !current_user_can('edit_post', $post_id) ) return false; // если юзер не имеет право редактировать запись
    if ($post_type !== "pushbot") return false;



    $post_txt = get_post( $post_id );
    $txtmain = $post_txt->post_content;


    
    
    pushFromPushBots($txtmain);  ///*Пушим*/
}


function pushFromPushBots($msg) {
   /*
   // Пушим
   */
  require_once(dirname(__FILE__). '/lib/PushBots.class.php');

  $pb = new PushBots();
  $valOpt = get_option('push_option');

  $valID = $valOpt['opt_push_ID'];       // ID
  $valID = $valOpt['opt_push_Secret'];   // Secret
  $valToken = $valOpt['opt_push_Token']; // Token



// Application ID
  $appID = $valID;
// Application Secret
  $appSecret = $valID;

  $pb->App($appID, $appSecret);
 
// Notification Settings
  $pb->Alert($msg);
  $pb->Platform(array("0","1"));
  $pb->Badge("+2");

// Update Alias 
/**
 * set Alias Data
 * @param integer $platform 0=> iOS or 1=> Android.
 * @param String  $token Device Registration ID.
 * @param String  $alias New Alias.
 */
 

  $pb->AliasData(1, $valToken, "test");  
// set Alias on the server
  $pb->setAlias();

// Push it !
  $pb->Push();














/*************    $sound = null;
    $badge = null;
    $platforms = null;
    $tags = array('sd' => 33);


    require_once "lib/PushBots.class.php";
    $pb = new PushBots();

*/    /*Получаем данные из админки*/
/*********    $valOpt = get_option('push_option');
    $valID = $valOpt['opt_push_ID'];
    $valToken = $valOpt['opt_push_Token'];
*/

    // Application ID
/*******    $appID = $valID;*/
    // Application Secret
/*********    $appSecret = $valToken;
    $pb->App($appID, $appSecret);
*/    
    // Notification Settings
/***********    $pb->Alert($msg);
    $pb->Sound($sound);
    $pb->Badge($badge);
    $pb->Platform($platforms);
*/    
    // Tags Array
    /********* $pb->Tags($tags);*/
    
    // Custom fields - payload data
/*************    $customfields= array("author" => "Jeff","nextActivity" => "com.example.sampleapp.Next");
    $pb->Payload($customfields);
*/    
    
    // Country or state
    /*$pb->Geo($country , $gov);*/
    
    // Push it !
/*************    $pb->Push();*/
    
    // Update Alias 
    /**
    * set Alias Data
    * @param    integer $platform 0=> iOS or 1=> Android.
    * @param    String  $token Device Registration ID.
    * @param    String  $alias New Alias.
    */
    
/*****************    $pb->AliasData(1, "APA91bFpQyCCczXC6hz4RTxxxxx", "test");
    // set Alias on the server
    $pb->setAlias();
*/    
    // Push to Single Device
    // Notification Settings
/****************    $pb->AlertOne("test Mesage");
    $pb->PlatformOne("0");
    $pb->TokenOne("3dfc8119fedeb90d1b8a9xxxxxx");
*/    
    //Push to Single Device
/****************    $pb->PushOne();*/

}










