<?php
/*
Plugin Name: Quick Popup
Plugin URI: http://www.pc-tudo.pt/
Description: Quando precisa de mostrar um popup ao seu visitante quando ele faz a primeira visita.
Version: 1.0
Author: Pedro Alfaiate
Author URI: http://www.pc-tudo.pt/
*/

function quipo_admin_register_head() {
  $siteurl = get_option('siteurl');
  $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css';    
  echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}
add_action('admin_head', 'quipo_admin_register_head');

/*function our_plugin_action_links($links, $file) {
    static $this_plugin;
 
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }
 
    // check to make sure we are on the correct plugin
    if ($file == $this_plugin) {
        // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=font-uploader.php">Settings</a>';
        // add the link to the list
        array_unshift($links, $settings_link);
    }
 
    return $links;
}
add_filter('plugin_action_links', 'our_plugin_action_links', 10, 2);*/
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'quipo_filter_plugin_actions');

// Add settings option
function quipo_filter_plugin_actions($links) {
  $new_links = array();
  
  $new_links[] = '<a href="options-general.php?page=quick_popup/quick_popup.php">Settings</a>';
  
  return array_merge($new_links, $links);
}
 
function quipo_menu() {
  add_options_page('Quick Popup', 'Quick Popup', 'manage_options', __FILE__, 'quipo_options');
}
add_action('admin_menu', 'quipo_menu');

function quipo_options(){
  $erros=array();
  $file_html = plugin_dir_path( __FILE__ )."html.txt";
  
  if (isset($_POST['guardar_quipo'])){
    setcookie("mostrou_quipo", 'sim', time()+0,'/');
    
     update_option( 'quipo_padding', $_POST['padding'] );
     update_option( 'quipo_activo', $_POST['activo'] );
     update_option( 'quipo_cookie', $_POST['tempo_cookie'] );
     update_option( 'quipo_tipo_popup', $_POST['tipo_popup'] );
     update_option( 'quipo_width', $_POST['width'] );
     update_option( 'quipo_height', $_POST['height'] );
     
     update_option( 'quipo_link_imagem', $_POST['link_imagem'] );
     update_option( 'quipo_target', $_POST['target'] );
     file_put_contents($file_html, stripslashes($_POST['quipo_html']));
     
     if ($_FILES['imagem']['size']>0){
       if (($_FILES["imagem"]["type"] == "image/gif") || ($_FILES["imagem"]["type"] == "image/jpeg") || ($_FILES["imagem"]["type"] == "image/pjpeg")|| ($_FILES["imagem"]["type"] == "image/png")|| ($_FILES["imagem"]["type"] == "image/bmp")){
         if ($_FILES["imagem"]["error"] == 0){
           // para apagar imagem antiga
           $imagem_antiga=get_option('quipo_imagem');
           if (trim($imagem_antiga!='')){
             if (file_exists(plugin_dir_path( __FILE__ ).'imagens/'.$imagem_antiga))
                unlink(plugin_dir_path( __FILE__ ).'imagens/'.$imagem_antiga);
           }
           move_uploaded_file($_FILES["imagem"]["tmp_name"],plugin_dir_path( __FILE__ ).'imagens/'. $_FILES["imagem"]["name"]);
           update_option('quipo_imagem',$_FILES["imagem"]["name"]);
         }else{
           $erros[]='Error code:'.$_FILES["imagem"]["error"];
         }
       }else{
         $erros[]='Ficheiro inválido ao fazer upload';
       }
     }
  }
  
  if (!file_exists($file_html)){
    $ourFileHandle = fopen($file_html, 'w') or die("can't open file");
    fclose($ourFileHandle);
  }
  ?>

  <?php
  $width=get_option('quipo_width');
  $height=get_option('quipo_height');
  if (!(is_numeric ($width)&& ($width>0)))
    $width="'auto'";
  if (!(is_numeric ($height)&& ($height>0)))
    $height="'auto'";
  ?>
  <script language="javascript" type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ );?>editor/tiny_mce.js"></script>
  <script language="javascript" type="text/javascript">
  tinyMCE.init({
    mode : "textareas",
    theme : "advanced",
    plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
    theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect,|,preview,|,code",
    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,|,forecolor,backcolor",
    theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,advhr,|,fullscreen",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resizing : true,
    theme_advanced_resize_horizontal : false,
    nonbreaking_force_tab : true,
    apply_source_formatting : true,
    relat2ive_urls : false,
    rem2ove_script_host : false,
    extended_valid_elements  : "form[name|id|action|method|enctype|accept-charset|onsubmit|onreset|target],input[*]",
    width : "<?php echo $width;?>"
  });
  </script>
  <h2>Quick Popup Configuration</h2>
  
  <?php
  if (count($erros)>0){
    echo '<div class="erros_quipo">';
    foreach ($erros as $key => $value) {
          echo '<div>'.$key.': '.$value.'</div>';
    }
    echo '</div>';
  }
  ?>
  
  <div class="quipo_left">
  <form name="form_popup" method="post" enctype="multipart/form-data">
    <table class="form-table border-quipo">  
      <tr>
      <th scope="row">
        <h2>Main settings</h2> 
      </th>
      <td>
        <span class="description"></span>
      </td>
      </tr>

      <tr>
      <th scope="row">
        <label >Active</label>
      </th>
      <td>
        <?php $activo= get_option('quipo_activo');?>
        <input <?php if ($activo=='1') echo 'checked'; ?> type="radio" name="activo" value="1" /> Yes
        <input <?php if ($activo=='0') echo 'checked'; ?> type="radio" name="activo" value="0" /> No
        <span class="description">Choose wheter to activate or not the popup</span>
      </td>
      </tr>
      
      <tr>
      <th scope="row">
        <label >Cookie Time</label>
      </th>
      <td>
        <input class="regular-text" type="text" name="tempo_cookie" value="<?php echo get_option('quipo_cookie');?>" />
        <span class="description">In seconds when will the popup show again to the user. Ex: 3600 equals 1 hour</span>
      </td>
      </tr>

      <tr>
      <th scope="row">
        <label >Border</label>
      </th>
      <td>
        <input style="width:50px" class="regular-text" type="text" name="padding" value="<?php echo get_option('quipo_padding');?>" />
        <span class="description">Border in px for your popup</span>
      </td>
      </tr>


      <tr>
      <th scope="row">
        <label >Type of popup</label>
      </th>
      <td>
       <?php $tipo_popup= get_option('quipo_tipo_popup');?>
        <input <?php if ($tipo_popup=='html') echo 'checked'; ?> type="radio" name="tipo_popup" value="html" /> HTML
        <input <?php if ($tipo_popup=='imagem') echo 'checked'; ?> type="radio" name="tipo_popup" value="imagem" /> Imagem
        <span class="description">Choose if you want a popup with an image or html</span>
      </td>
      </tr>
  </table>
  
  <table class="form-table border-quipo">  
      <tr>
      <th scope="row">
        <h2>For image popup</h2> 
      </th>
      <td>
        <span class="description">If you don't use image popup you don't need to fill this options'</span>
      </td>
      </tr>
      
      <tr>
      <th scope="row">
        <label >Upload Image</label>
      </th>
      <td>
        <input class="regular-text" type="file" name="imagem" value="" />
        <span class="description">Choose an image to upload to your popup</span>
      </td>
      </tr>

    <?php
      $imagem=get_option('quipo_imagem');
      if ($imagem!=''){
    ?>    
      <tr>
      <th scope="row">
        <label >Current image</label>
      </th>
      <td>
        <?php echo '<a target="_blank" href="'.plugin_dir_url( __FILE__ ).'imagens/'.$imagem.'"><img width="100" src="'.plugin_dir_url( __FILE__ ).'imagens/'.$imagem.'" /></a>';?>
      </td>
      </tr>
    <?php
      }
    ?>

      <tr>
      <th scope="row">
        <label >Image Link</label>
      </th>
      <td>
        <input class="regular-text" type="text" name="link_imagem" value="<?php echo get_option('quipo_link_imagem');?>" />
        <span class="description">Link where the user goes after click your popup image</span>
      </td>
      </tr>
      <tr>
      <th scope="row">
        <label >Link Target</label>
      </th>
      <td>
        <select class="regular-text" name="target">
          <?php $target= get_option('quipo_target');?>
          <option <?php if ($target=='_blank') echo 'selected'; ?> value="_blank">Blank</option>
          <option <?php if ($target=='_target') echo 'selected'; ?> value="_target">Self</option>
        </select>
        <span class="description">Choose where link open. Blank page or self.</span>
      </td>
      </tr>

  </table>
  
  <table class="form-table border-quipo">  
      <tr>
      <th scope="row">
        <h2>For Html popup</h2> 
      </th>
      <td>
        <span class="description">If you don't use html popup you don't need to fill this options'</span>
      </td>
      </tr>
      
      <tr>
      <th scope="row">
        <label >Width</label>
      </th>
      <td>
        <input class="regular-text" type="text" name="width" value="<?php echo get_option('quipo_width');?>" />
        <span class="description">Choose width to your html popup</span>
      </td>
      </tr>

      <tr>
      <th scope="row">
        <label >Height</label>
      </th>
      <td>
        <input class="regular-text" type="text" name="height" value="<?php echo get_option('quipo_height');?>" />
        <span class="description">Choose height to your html popup</span>
      </td>
      </tr>

      <tr>
      <th scope="row">
        <label >Html</label>
      </th>
      <td>
        <textarea class="quipo_html" id="quipo_html" cols="100" rows="5"  name="quipo_html"><?php echo file_get_contents($file_html);?></textarea>
        <span class="description">Create your Html Popup</span>
      </td>
      </tr>
  </table>
  <div style="clear:both"></div>
    <div><input type="submit" name="guardar_quipo" value=" Save " /></div>
  </form>
  </div>
  <div class="quipo_right">
  
    <div class="donate">
    <h2>Donate</h2>
    If you like this plugin and find it useful, help keep this plugin free and actively developed by clicking the donate button.
    <br><br> 
      <div align="center">
      <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="TRSU5ATSNSCMN">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
      </form>
      </div>
    </div>
  
  </div>
  <div class="clearfix"></div>

<?php 
}

if (get_option('quipo_activo')==1){
  add_action( 'wp_print_scripts', 'quipo_scripts' );
  add_action( 'wp_print_styles', 'quipo_styles' );
  add_action('wp_head','quipo_head');
  add_action('wp_footer','quipo_footer');
}

function quipo_scripts(){
  wp_enqueue_script('jquery'); 
  wp_enqueue_script('fancybox',  plugin_dir_url( __FILE__ ).'fancybox/jquery.fancybox-1.3.1.js' );
}
function quipo_styles(){
  wp_enqueue_style( 'style_fancybox', plugin_dir_url( __FILE__ ).'fancybox/jquery.fancybox-1.3.1.css' );
}

function quipo_head(){
  global $mostrar_quipo;
  if (isset($_COOKIE['mostrou_quipo'])){
    $mostrar_quipo=false;
  }else{
    $tempo=get_option('quipo_cookie');
    if (!((is_numeric ($tempo))&& (($tempo>0))))
      $tempo=1000;
    setcookie("mostrou_quipo", 'sim', time()+$tempo,'/');
    $mostrar_quipo=true;
  }
}

function quipo_NlToBr($inString){
  return str_replace("\r\n","",$inString); 
}
function quipo_devolve_link($link,$com_http=1){
  if ($link=='')
    return '';
  if (substr($link, 0, 7)!='http://' )
    $link='http://'.$link;
  else
    $link=$link;
  
  if ($com_http==1)
    return $link;
  else
    return substr($link, 7);
}

function quipo_footer(){
  global $mostrar_quipo;
  
  if ($mostrar_quipo){ 
    $tipo_popup=get_option('quipo_tipo_popup');
    $width=get_option('quipo_width');
    $height=get_option('quipo_height');
    if (!(is_numeric ($width)&& ($width>0)))
      $width="'auto'";
    if (!(is_numeric ($height)&& ($height>0)))
      $height="'auto'";
    
    $padding=get_option('quipo_padding');
    if (!(is_numeric ($padding)&& ($padding>0)))
      $padding="0";

    $link=trim(get_option('quipo_link_imagem'));
    ?>
    <script>
    jQuery(document).ready(function() {
      jQuery.fancybox(
        '<?php
          if ($tipo_popup=='imagem'){
            $width="'auto'";
            $height="'auto'";
            if ($link!='') echo '<a href="'.quipo_devolve_link($link).'" target="'.get_option('quipo_target').'">';
            
            echo '<img src="'.plugin_dir_url( __FILE__ ).'imagens/'.get_option('quipo_imagem').'"/>';
            
            if ($link!='') echo '</a>';
          }else{
            $file_html = plugin_dir_path( __FILE__ )."html.txt";
            echo addslashes(quipo_NlToBr(file_get_contents($file_html)));
          }
        ?>',
        {
          'autoDimensions'  : false,
          'width'             : <?php echo $width?>,
          'height'            : <?php echo $height?>,
          'transitionIn'    : 'none',
          'transitionOut'   : 'none',
          'padding'        : <?php echo $padding?>,
          'margin'        : 0, 
          'overlayOpacity':0.8
        }
      );
    });
    </script>
  <?php  }
}