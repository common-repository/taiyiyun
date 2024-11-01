<?php
/*
Plugin Name: 区块链版权登记
Plugin URI: http://www.taiyiyun.com/
Description: 区块链具有不可篡改、公开透明等特点，用户可一键登记到太一区块链，对著作权进行快速登记和确权，固定享有著作权的权利证据，减少著作权维权过程中的举证负担。
Version:  1.0.5
Author: 太一云科技
Email: xujunjie@taiyiyun.com
Author URI: http://www.taiyiyun.com/
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once('taiyiyun.class.php');

if(class_exists("Taiyiyun")){
	$taiyiyun_plugin = new Taiyiyun();
	$taiyiyun_plugin->start_plugin();
}
?>