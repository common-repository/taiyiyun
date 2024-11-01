<div class="wrap">
   <a name="taiyiyun"></a>
   <h2>区块链版权登记设置</h2>   
   
   <form id="setting_form" method="post" action="options.php">
      <?php settings_fields('taiyiyun_options_group'); ?>
      <p>请访问<a href="http://www.taiyiyun.com" title="注册">区块链版权登记验证设置</a>点击注册申请自己的key.<h3>如果是在登陆页面安装验证，请确保填入正确的APP_ID SECRET KEY，避免造成无法正常提交。</h3></p>
      
      <table class="form-table">
	  	<tr valign="top">
            <th scope="row">您的APP ID</th>
            <td>
               <input id="input_app_id" type="text" name="taiyiyun_options[app_id]" size="40" value="<?php echo $this->options['app_id']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row">您的APP Secret</th>
            <td>
               <input id="input_app_secret"  type="text" name="taiyiyun_options[app_secret]" size="40" value="<?php echo $this->options['secret']; ?>" />               
            </td>
         </tr>
         <tr valign="top">
            <th scope="row">您的APP Key</th>
            <td>
               <input id="input_app_key"  type="text" name="taiyiyun_options[app_key]" size="40" value="<?php echo $this->options['key']; ?>" />               
            </td>
         </tr>
      </table>
      <p class="submit"><input type="submit" class="button-primary" title="保存更改" value="保存更改 &raquo;" /></p>
   </form>
   <?php do_settings_sections('taiyiyun_options_page'); ?>
</div>
<script type="text/javascript">
    var input_app_id = document.getElementById('input_app_id');
    var input_app_secret = document.getElementById('input_app_secret');
    var input_app_key = document.getElementById('input_app_key');
    setting_form.onsubmit=function(){
            var a =  window.confirm("请确保输入正确的信息。\n以免造成无法提交的麻烦。");
            if(a){
                 if(input_app_id.value=="" || input_app_secret.value=="" || input_app_key.value=="" ){
                       alert("您的信息不完整。");
                       return false;
                }
              return true;
            }else{
              return false;
            } 
    }
</script>