<?php
require_once('taiyiyunlib.php');
if(!class_exists("Taiyiyun")){
    class Taiyiyun{
    	public $plugin_directory;	
    	public $options;
      public $config;
    	function start_plugin(){
    	 		$this->plugin_directory = basename(dirname(__FILE__));
          $this->config = get_option("taiyiyun_token");
				  $this->register_default_options() ;
				  $this->register_actions();    	 	
    	}

    	function register_default_options($rewrite = false) {
        $options = get_option("taiyiyun_options");
        $taiyiyunlib = new taiyiyunlib();
        $this->options = $options;
    	  if((isset($options) && empty($this->config)) || $this->config['validated'] <time()  || $rewrite){
            $result = $taiyiyunlib->getToken($options);
            $this->config = array();
            if($result['success'] ){
              $this->config['success'] = 1;
              $this->config['validated'] = time()+$result['expires_in']-600;
              $this->config['token']  = $result['token'];
            }else{
              $this->config['success'] = 0;
              $this->config['message']  = $result['message'];
            }  
            update_option("taiyiyun_token", $this->config);
        }
    	}

    	//停止插件   回调函数
        function uninstall(){
            //移除管理员设置页面
            unregister_setting("taiyiyun_options_group", 'taiyiyun_options');
           
        }

        function register_actions() {
        	add_action('admin_init', array($this, 'register_settings_group'));   

        	add_action('admin_menu', array($this, 'add_settings_page')); 

          add_action('admin_notices', array($this, 'missing_keys_notice'));

          add_action('publish_post', array($this, 'ty_copyright_publish'));

          register_activation_hook($this->plugin_directory . '/wp-taiyiyun.php', array($this, 'register_default_options'));

          register_activation_hook($this->plugin_directory . '/wp-taiyiyun.php', array($this, 'uninstall')); 

        }


        function register_settings_group() {
            register_setting("taiyiyun_options_group", 'taiyiyun_options', array($this, 'validate_options'));
            if($this->config['success']){
              if (function_exists('add_meta_box')) {
              add_meta_box('ty-copyright-sidebox', '版权登记设置[文章]', array($this, 'ty_copyright_sidebox'), 'post', 'side', 'high');
              add_filter( 'post_row_actions',  array($this, 'ty_post_row_actions'), 10, 2 );//文章
              }   
            }
        }
        function validate_options($input) {
            $validated['app_id'] = trim($input['app_id']);
            $validated['secret'] = trim($input['app_secret']);
            $validated['key']    = trim($input['app_key']);

            return $validated;
        }

        function add_settings_page() {            
            add_options_page('区块链版权登记', '区块链版权登记', 'manage_options', __FILE__, array($this, 'show_settings_page'));
        }
        function show_settings_page() {
            include("settings.php");
        }

        function missing_keys_notice() {
            if ($this->keys_missing()) {
                $this->create_error_notice('请完善区块链版权登记信息.');
            }
            if(!$this->keys_missing() && isset($this->options['message']) ){
                $this->create_error_notice($this->options['message']);
            }
            if($this->config['success'] && $this->config['validated'] <time()){
                $this->create_error_notice('请重新提交验证信息');
            }
        }
        
        function keys_missing() {
            return (empty($this->options['app_id']) || empty($this->options['secret']) || empty($this->options['key']));
        }
        
        function create_error_notice($message, $anchor = '') {
            $options_url = admin_url('options-general.php?page=geetest/geetest.class.php') . $anchor;
            $error_message = sprintf(__($message . ' <a href="%s" title="WP-GeeTest Options">点击修复</a>', 'geetest'), $options_url);
            
            echo '<div class="error"><p><strong>' . $error_message . '</strong></p></div>';
        }

        // 文章发布页面 面板
        function ty_copyright_sidebox($post) {
           if ($post -> post_status != 'publish') {
              echo '<p><label><input type="checkbox" name="publish_sync" value="1" />登记</label></p>';
            } else {
              $this->get_post_station($post);
              $result_json = get_post_meta($post->ID,'_ty_result',true);
              $result = json_decode($result_json);
              if($result->success) { 
                echo '<p><label><input type="checkbox" name="publish_update_sync" value="1" />变更登记</label></p>';
              }else{
                echo '<p><label><input type="checkbox" name="publish_sync" value="1" />登记</label></p>';
              }
            } 
        } 

        function ty_copyright_publish($post_ID) {
          if (isset($_POST['publish_sync']) || isset($_POST['publish_update_sync']) ){
            @ini_set("max_execution_time", 120);
            $taiyiyunlib = new taiyiyunlib();
            $this->check_token();
            $data = array();
            $api_url = 'http://api.taiyiyun.com/copyright/?token='.$this->config['token'];   //copyright api url
            $api_register = '&taiyiyun.api=api.copyright.register';
            $api_change = '&taiyiyun.api=api.copyright.change';
            $post = get_post($post_ID);
            $data['business_num'] = $post->ID;//业务ID
            $data['hash'] = hash('md5',$post->post_content);  //权益元数据64位hash值
            $data['work_from'] = 'w'; // 数据来源 wordpress
            foreach((get_the_category()) as $category)
            {
             $work_type = $category->cat_name;
            }
            if($work_type ==''){
              $work_type = '没有类别';
            }
            $data['custom_type'] = $work_type ;
            $data['work_type'] = 'origin';
            $account = get_userdata($post -> post_author );  
            $data['author'] = $account->data->display_name;  //作者
            $data['work_name'] = $post -> post_title ; //作品名
            $data['copyright_owner'] = $account->data->display_name; //著作权
            $data['first_pubtime']  = date("Y年m月d日",strtotime($post->post_date));

            if(isset($_POST['publish_update_sync'])){
              $data['old_business_num'] = $post->ID ;
              $data['finish_time']  = date("Y年m月d日",strtotime($post->post_modified));
              $data['regist_time']  = date("Y年m月d日",strtotime($post->post_modified));
            }else{
              $data['finish_time']  = date("Y年m月d日",strtotime($post->post_date));//编辑需要变更
              $data['regist_time']  = date("Y年m月d日",strtotime($post->post_date));//编辑需要变更
            }
            $data['copy_abstract'] =  $taiyiyunlib->ty_status($post->post_content,'',100);
            $data['work_link'] = get_permalink($post_ID);
            $data['work_content'] = $post->post_content;


            if(isset($_POST['publish_update_sync'])){
              $result = $taiyiyunlib->send_post($api_url.$api_change,$data);
            }else{
              $result = $taiyiyunlib->send_post($api_url.$api_register,$data);
            }
            update_post_meta($post->ID, "_ty_result",$result);
            //print_r($result);die;
          }
          return;
        }

        function check_token(){
            $taiyiyunlib = new taiyiyunlib();
            $data = array();
            $api_url = 'http://api.taiyiyun.com/copyright/?token='.$this->config['token']; // to check token
            $api_register = '&taiyiyun.api=api.copyright.register';
            $result_json = $taiyiyunlib->send_post($api_url.$api_register,$data);
            $result = json_decode($result_json);
            if(isset($result->code) && $result->code == '100'){
                $this->register_default_options(true);
            }
        }
        function ty_post_row_actions( $actions, $post_object ) {

          $result_json = get_post_meta($post_object->ID,'_ty_result',true);
          $result = json_decode($result_json);
          if($result->success) { 
            $actions['ty'] = '<a href="'.$result->regnum.'" target="_blank">已登记</a>';
          }else{
            $actions['ty'] = '<a href="" target="_blank">未登记</a>';
          }
          return $actions;
        }

        //去获取文章的最新状态，变更数据库
        function get_post_station($data){
          $taiyiyunlib = new taiyiyunlib();
          $this->check_token();
          $api_url = 'http://api.taiyiyun.com/copyright/?token='.$this->config['token'];
          $api_search = '&taiyiyun.api=api.copyright.search';

          $result_json = $taiyiyunlib->send_post($api_url.$api_search, array('business_num' => $data->ID, ));
          update_post_meta($data->ID, "_ty_result",$result_json);
        }

   	}
}
?>