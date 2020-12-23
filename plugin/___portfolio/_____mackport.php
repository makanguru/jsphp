<?php

/*
Plugin Name: ______mackport.php
Description: Show portofolo from carousel and slider
Version: 1.0
Author: Nikolay Vladimirovich
Author URI:
Plugin URI:
*/



add_action('wp_enqueue_scripts', 'mnv_styles_scripts');

function mnv_styles_scripts(){

	/*Style*/
//
	wp_register_style( 'css-bootstrap', plugins_url( 'assets/css/bootstrap.min.css', __FILE__ ) );
	wp_register_style( 'css-custom', plugins_url( 'assets/css/custom.css', __FILE__ ) );
	wp_register_style( 'css-owl-car', plugins_url( 'owl-carousel/owl.carousel.css', __FILE__ ) );
	wp_register_style( 'css-owl-theme', plugins_url( 'owl-carousel/owl.theme.css', __FILE__ ) );
  wp_register_style( 'css-owl-transition', plugins_url( 'owl-carousel/owl.transitions.css', __FILE__ ) );  
//
//
//
//
	wp_enqueue_style( 'css-bootstrap' );
  wp_enqueue_style( 'css-custom' );
  wp_enqueue_style( 'css-owl-car');
	wp_enqueue_style( 'css-owl-theme');
  wp_enqueue_style( 'css-owl-transition');  
//
//
    /* JS-script*/
	wp_register_script( 'js-jquery', plugins_url( 'assets/js/jquery.js', __FILE__ ), array('jquery') );
	wp_register_script( 'js-bootstrap', plugins_url( 'assets/js/bootstrap.js', __FILE__ ), array('jquery') );
	wp_register_script( 'js-owl-car', plugins_url( 'owl-carousel/owl.carousel.js', __FILE__ ), array('jquery') );
	wp_register_script( 'js-mnv-custom', plugins_url( 'js/mnv-custom.js', __FILE__ ), array('jquery') );
  wp_register_script( 'js-mnv-hint', plugins_url( 'js/nicetitle.js', __FILE__ ), array('jquery') );


//
	wp_enqueue_script( 'js-jquery');
	wp_enqueue_script( 'js-bootstrap');
	wp_enqueue_script( 'js-owl-car');
  wp_enqueue_script( 'js-mnv-custom');
  wp_enqueue_script( 'js-mnv-hint');
  /* JS-script*/


}



function register_portfolio() {    // Подключаем пользовательские типы данных
    $labels = array(
      'name' => 'Портфолио', // Основное название типа записи
      'singular_name' => 'Портфолио', // отдельное название записи типа Push-
      'add_new' => 'Добавить новую',
      'add_new_item' => 'Добавить новое портфолио',
      'edit_item' => 'Редактировать портфолио',
      'new_item' => 'Новое портфолио',
      'view_item' => 'Посмотреть портфолио',
      'search_items' => 'Найти портфолио',
      'not_found' =>  'Портфолио не найдено',
      'not_found_in_trash' => 'В корзине портфолио не найдено',
      'parent_item_colon' => '',
      'menu_name' => 'Портфолио'

    );
    
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
      'hierarchical' => true,
      'menu_position' => null,
      'supports' => array('title','editor','author','thumbnail','excerpt','comments', 'custom-fields', 'taxonomy')
    );
    register_post_type('portfolio', $args);
}
add_action( 'init', 'register_portfolio');



add_action('init', 'create_taxonomy');
function create_taxonomy(){
  // заголовки
  $labels = array(
    'name'              => 'typeportfolio',
    'singular_name'     => 'Тип портфолио',
    'search_items'      => 'Поиск типа портфолио',
    'all_items'         => 'Все типы портфолио',
    'parent_item'       => 'Родительский тип',
    'parent_item_colon' => 'Родительский тип:',
    'edit_item'         => 'Редактировать тип портфолио',
    'update_item'       => 'Сохранить тип портфолио',
    'add_new_item'      => 'Добавить новый тип портфолио',
    'new_item_name'     => 'Новый тип портфолио',
    'menu_name'         => 'Тип портфолио',
  ); 
  // параметры
  $args = array(
    'label'                 => '', // определяется параметром $labels->name
    'labels'                => $labels,
    'public'                => true,
    'show_in_nav_menus'     => true, // равен аргументу public
    'show_ui'               => true, // равен аргументу public
    'show_tagcloud'         => true, // равен аргументу show_ui
    'hierarchical'          => true,
    'update_count_callback' => '',
    'rewrite'               => true,
    //'query_var'             => $taxonomy, // название параметра запроса
    'capabilities'          => array(),
    'meta_box_cb'           => null, // callback функция. Отвечает за html код метабокса (с версии 3.8): post_categories_meta_box или post_tags_meta_box. Если указать false, то метабокс будет отключен вообще
    'show_admin_column'     => true, // Позволить или нет авто-создание колонки таксономии в таблице ассоциированного типа записи. (с версии 3.5)
    '_builtin'              => false,
    'show_in_quick_edit'    => null, // по умолчанию значение show_ui
  );
  register_taxonomy('typeportfolio', array('portfolio'), $args );
}




/***************/
/* Вывод HTML  */
/***************/
add_action( 'init', 'mnv_portfolio_show');

function mnv_portfolio_show() {
  add_shortcode( 'mnvportfolio', 'mnv_portfolio');  //Вызов [mnvportfolio]:
}


/*$listCat = get_taxonomy('typeportfolio');  // Получим список категорий (таксономий) для портфолиг
var_dump($listCat);
*/


function mnv_portfolio() {  // Показ портфолио
    // Получаем категории=таксономии: тип портфолио
    $myterms = get_terms('typeportfolio');

    $pathIMGleft = plugins_url( 'img/arr_p_left.png', __FILE__ );
    $pathIMGright = plugins_url( 'img/arr_p_right.png', __FILE__ );

    $inHTML = '

              <!-- Зависимый от элементов карусели слайдер=single-карусель -->
              <section id="bindGallery">
                <div class="container-fluid"  style="width: 75%;">
                  <div class="row">
              
                    <div class="col-sm-12">
                      <div class="col-sm-9">
                       <div id="owl-mnv-gallery" class="owl-carousel owl-theme"  
                             data-pathLeft="' . $pathIMGleft . '"  data-pathRight="' . $pathIMGright . '" >    <!-- Верхний слайдер -->
                       </div>
                      </div>

                      <div type="button" class="close"   style="color: #fff;">
                        <span>X</span>
                      </div>

                      <div class="col-sm-3 mnv-gal-txt" >   <!-- Текстовая информация справа от слайдера-->
                      </div>
                    </div> 
              
                  </div>
                </div>
              </section>
              
              <div class="block2" id="mess"></div>   <!-- Для всплывающей подсказки-хинта для элементов карусели -->
              
              <section id="carousel-down">
                <div id="mnv-port">   <!-- Карусель -->
              
                   <div class="mnu-caro">
                      <ul class="nav nav-pills">
                        <li class="active" data-id="m0"><a href="">Все</a></li>
                ';




     // Выведем меню категорий (пользователських таксономий ) по которым для филтрации карусели по категориям
     foreach ($myterms as $key => $value) {
         $inHTML .= '<li data-id="' . $value->slug . '"><a href="">'. $value->name . '</a></li>';
     }

    $inHTML .=  '
                     </ul>
                   </div>
              
                   <div class="container-fluid">
                        <div class="row">
                            <div id="owl-mnv-port" class="owl-carousel">
                 ';

    // Формирование главной карусели и всех категорий
    $bodyCar = array();   // Элементы нижней карусели по категориям

    // Заполнение массива элемнтов карусели по категориям

    $allCatHTML = "";   // Основная карусель со всеми категориями без фильтра
    foreach ($myterms as $key => $value) {
         $bodyCar[$value->slug] = showElements($value->slug);
         $allCatHTML .= $bodyCar[$value->slug];
     }

    // Заполнили тело основной карусели без фильтра 

    $inHTML .=  $allCatHTML .  '
                            </div>
                       </div>
                   </div> 
                 ';
     
     // Создадим скрытые блоки карусели для фильтра
    foreach ($bodyCar as $key => $value) {
        $inHTML .=   '
                   <div class="container-fluid">
                     <div class="row">
                 ';
        $inHTML .=  '
                       <div id="owl-' . $key . '" class="owl-carousel">
                    ';
        $inHTML .=  $value;
        $inHTML .= '
                       </div> 
                     </div>
                    </div>
                   ';
    }  // Конец цикла

    $inHTML .=   '
                </div>  <!-- id="mnv-port" -->
              </section>
              
                ';

    return $inHTML;
}


// Выведем тело карусели для категории $catTax
function showElements($catTax) {

    $args = array(
      'post_type' => 'portfolio',
      'typeportfolio' => $catTax
    );

    $query = new WP_Query( $args );

    $showHTML = "";  // Накапливаемая строка с телом карусели для категории

    // Цикл
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {

        $query->the_post();

        // Tooltip-подсказка  и описание кейса (которе справа)
        $showHTML .= '<div class="item ' .  $catTax . 
        '" onmouseover="tooltip(this,\'' . get_the_title() . '\')" onmouseout="hide_info(this)"  data-descrip="' . get_the_content() . '" ';
        //$showHTML .= '<div class="item ' . $catTax . '" data-toggle="tooltip" data-placement="top" title="' . get_the_title() . '" data-descrip="' . get_the_content() . '" ';
  
        $eeee = get_the_excerpt();   // Контент записи портфолио, в которй указаны коды медиафайлов

        // Получаем коды аттачей
        $pieces = explode('"', $eeee);
        $media = explode(',', $pieces[1]);

        //Вытаскиваем информацию по аттачам (медиа-файлам) и переносим во фронт в $data_lnktlu и $data_linkis
        $data_lnktlu = ' data-lnktlu="';
        $data_linkis = ' data-linkis="';
        $dlArr = count($media);
        for ($i=0; $i < $dlArr; $i++) { 
            $attach = get_post( $media[$i] );
            $data_lnktlu .= $attach->post_content . ";*";
            $data_linkis .= $attach->guid . ";*";
        }
        $data_lnktlu .= '" ';
        $data_linkis .= '" ';

        //Объединяем data c основным HTML
        $showHTML .= $data_lnktlu;
        $showHTML .= $data_linkis.">"; //Также закрыли открывающий див

        //Добавим картинку к нижней карусели
        global $post;
        $showHTML .= get_the_post_thumbnail($post->ID, array(1135, 1900)) .'</div>';
        /*echo "----->".$showHTML;*/

      }  // Конец цикла
    } else {
      // Постов не найдено
    }
    /* Возвращаем оригинальные данные поста. Сбрасываем $post. */
    wp_reset_postdata();
    return $showHTML;
}