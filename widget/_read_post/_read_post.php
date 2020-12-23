<?php 
/*
Plugin Name: _____test_plugin
Plugin URI: http://страница_с_описанием_плагина_и_его_обновлений
Description: Краткое описание плагина. Для вывода записи 
Version: Номер версии плагина, например: 1.0
Author: Имя автора плагина
Author URI: http://страница_автора_плагина

Для


*/

// подключаем функцию активации мета блока (my_complet_fields)




/**********************/
/*Подключение виджетов*/

define( 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require( PLUGIN_DIR .'/lib-php/class_all.php' ); 

function mnv_widget_refer() {
  register_widget('MNVtest_Widget');
}

add_action('widgets_init', 'mnv_widget_refer' );




