<?php
require("db_connection.php");
require("model/model.php");
if(isset($_GET['action'])){

	$model = new model($dbh);

	$urlError = "請重新確認網址";

	switch ($_GET['action']) {
		case 'login':
			require("view/login.php");
			break;

		case 'logout': 
			session_destroy();
			echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=home>';
			break;

		case 'login_data':
			$uid = $_POST['uid'];
			$passwd = $_POST['passwd'];
			$hashpasswd=hash("sha256", $passwd);
			$prepareSQL = "SELECT uid FROM user where uid = :uid";
			$executeSQL = array(':uid' => $uid);
			$result = $model->getDataSQL($prepareSQL, $executeSQL);
			if($result){
				$prepareSQL = "SELECT uid,passwd,did,pid FROM user where uid = :uid and passwd = :passwd";
				$executeSQL = array(':uid' => $uid,':passwd' => $hashpasswd);
				$result = $model->getDataSQL($prepareSQL, $executeSQL);
				if($result){
					foreach($result as $e){
						$_SESSION['uid'] = $e['uid'];
						$_SESSION['did'] = $e['did'];
						$_SESSION['pid'] = $e['pid'];
					}
					echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=home>';
				}else{
					echo "try again, passwd error";
					echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=login>';
				}
			}else{
				echo "try again no this uid";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=login>';
			}
			break;

		case 'home':
			if(isset($_SESSION["uid"])){
				$fidfname_result=$model->getfidfname($_SESSION['did'],$_SESSION['pid']);
				require("view/home.php");
			}else{
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=login>';
			}
			break;

		case 'self':
			require("view/self.php");
			break;

		case 'staff':
			if(isset($_SESSION["uid"])){
				$fidfname_result=$model->getfidfname($_SESSION['did'],$_SESSION['pid']);
				$c=0;
				foreach($fidfname_result as $f) {
						if($f['fid']=='f005'){
							$c=1;
						}
				}
				if($c==1){
					$prepareSQL = "SELECT did,dname FROM department";
					$executeSQL = array();
					$result = $model->getDataSQL($prepareSQL, $executeSQL);
				}else{
					$prepareSQL = "SELECT did,dname FROM department where did = :did";
					$executeSQL = array(':did' => $_SESSION['did']);
					$result = $model->getDataSQL($prepareSQL, $executeSQL);
				}

				if(isset($_GET['onclick'])&&isset($_GET['clickid'])&&isset($_GET['updid'])&&isset($_GET['uppid'])){
					$prepareSQL = "SELECT function.fid, function.fname FROM permission, function where permission.did= :did and permission.pid = :pid and permission.fid = function.fid";
					$executeSQL = array(':did' => $_GET['updid'],':pid' => $_GET['uppid']);
					$haveresult = $model->getDataSQL($prepareSQL, $executeSQL);

					$prepareSQL = "SELECT fid,fname from function where fid NOT IN (SELECT function.fid FROM permission, function where permission.did = :did and permission.pid = :pid and permission.fid = function.fid)";
					$executeSQL = array(':did' => $_GET['updid'],':pid' => $_GET['uppid']);
					$nothaveresult = $model->getDataSQL($prepareSQL, $executeSQL);
				}
		
				require("view/staff.php");
			}else{
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=login>';
			}
			break;

		case 'add_department_data': 
			$add_dname = $_POST['add_dname'];

			$prepareSQL = "SELECT did,dname FROM department order by did desc limit 1";
			$executeSQL = array();
			$result = $model->getDataSQL($prepareSQL, $executeSQL);
			foreach($result as $e){
				$last_did = $e['did'];
				$last_dname = $e['dname'];
			}
			echo "<script>javascript: alert(".$last_did.")></script>";
			$enddid=substr($last_did, -3, 3)+1;
			echo "<script>javascript: alert(".$enddid.")></script>";
			if ($enddid < 10) {
				$nowendid='00'.$enddid;
			}elseif (100>$enddid && $enddid>=10) {
				$nowendid='0'.$enddid;
			}elseif (1000>$enddid && $enddid>=100) {
				$nowendid=$enddid;
			}
			$add_did='d'.$nowendid;

			$prepareSQL = "INSERT INTO department (did,dname) VALUES(:did,:dname)";
			$executeSQL = array(':did' => $add_did , ':dname' => $add_dname);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('新增部門成功!')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=staff>';
			}else{
				echo "try again";
			}

			break;

		case 'register':  
			require("view/register.php");
			break;

		case 'register_data': 
			$email = $_POST['email'];
			$name = $_POST['name'];
			$psw = $_POST['psw'];
			$hashp=$hashpasswd=hash("sha256", $psw);
			$prepareSQL = "INSERT INTO user (uname,groups_id,position_id,email, passwd) VALUES(:name,:groups_id,:position_id,:email,:psw)";
			$executeSQL = array(':name' => $name , ':groups_id' => '001',':position_id' => '001',':email' => $email ,':psw' => $hashp);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('註冊成功! 請重新登入!')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=home>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=register>';
			}
			break;

		case 'delete': 
			$uid = $_GET['uid'];
			$prepareSQL = "DELETE FROM user WHERE uid = :uid";
			$executeSQL = array(':uid' => $uid);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('刪除成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=staff>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=staff>';
			}
			break;

		case 'staff_change': 

			break;

		case 'upd_permission_data':
			$str="";
			$text_updid=$_POST['text_updid'];
			$text_uppid=$_POST['text_uppid'];
			echo "<script>alert('text_updid: ".$text_updid."');</script>";
			echo "<script>alert('text_uppid: ".$text_uppid."');</script>";

			$prepareSQL = "DELETE FROM permission WHERE did=:did and pid=:pid";
			$executeSQL = array(':did' => $text_updid , ':pid' => $text_uppid);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			echo "<script>alert('DELETE num: ".$sql."');</script>";

			if(!empty($_POST['right_checkbox'])){
		    foreach($_POST['right_checkbox'] as $r){
					echo "<script>alert('".$r."');</script>";

					$prepareSQL = "SELECT ssid FROM permission order by ssid desc limit 1";
					$executeSQL = array();
					$ssidresult = $model->getDataSQL($prepareSQL, $executeSQL);
					foreach($ssidresult as $s){
						$last_ssid = $s['ssid'];
					}
					echo "<script>alert('last_ssid: ".$last_ssid."');</script>";
					$endssid=substr($last_ssid, -3, 3)+1;
					echo "<script>alert('endssid: ".$endssid."');</script>";
					if ($endssid < 10) {
						$nowendssid='00'.$endssid;
					}elseif (100>$endssid && $endssid>=10) {
						$nowendssid='0'.$endssid;
					}elseif (1000>$endssid && $endssid>=100) {
						$nowendssid=$endssid;
					}
					$add_ssid='s'.$nowendssid;
					echo "<script>alert('add_ssid: ".$add_ssid."');</script>";

					$prepareSQL = "INSERT INTO permission (ssid,did,pid,fid) VALUES (:ssid,:did,:pid,:fid)";
					$executeSQL = array(':ssid' => $add_ssid , ':did' => $text_updid,':pid' => $text_uppid, ':fid' => $r);
					$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
				}
				echo "<script>alert('更改成功!!');</script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=staff>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=staff>';
			}
		    
			break;

		case 'staff_add':
			$udate;
			$todaydate;
			$uidno;
			$prepareSQL = "SELECT uid FROM user WHERE 1=:a order by uid desc limit 1";
			$executeSQL = array(':a' => 1);
			$result = $model->getDataSQL($prepareSQL,$executeSQL);
			if($result) {
				foreach($result as $e){
					$last_uid=$e['uid'];
				}
				$udate=(int)(substr($last_uid, 1, 8));
				$todaydate=(int)(date("Ymd"));
				if ($udate != $todaydate) {//今天還沒新增人員
					$today=(string)(date("Ymd"));
					$uidno='u'. $today.'000001';
				}elseif ($udate == $todaydate) {//今天已經有新增人員了
					$endid=substr($last_uid, -6, 6)+1;
					if ($endid < 10) {
						$nowendid='00000'.$endid;
					}elseif (100>$endid && $endid>=10) {
						$nowendid='0000'.$endid;
					}elseif (1000>$endid && $endid>=100) {
						$nowendid='000'.$endid;
					}elseif (10000>$endid && $endid>=1000) {
						$nowendid='00'.$endid;
					}elseif (100000>$endid && $endid>=10000) {
						$nowendid='0'.$endid;
					}elseif (1000000>$endid && $endid>=100000) {
						$nowendid=$endid;
					}
					$today=(string)(date("Ymd"));
					$uidno='u'. $today.$nowendid;
				}
			}

			$name = $_POST['uname'];
			$passwd=$_POST['passwd'];
			$hashpasswd=hash("sha256", $passwd);
			$tel = $_POST['tel'];
			$mail = $_POST['email'];
			$salary = $_POST['salary'];
			$selectOptionD = $_POST['text_adddid'];
			$selectOptionP = $_POST['text_addpid'];
			$prepareSQL = "INSERT INTO user (uid,passwd,uname,tel,salary,begin,end,email,did,pid,employment) VALUES(:uid,:passwd,:uname,:tel,:salary,:begin,:end,:email,:did,:pid,:employment)";
			$executeSQL = array(':uid' => $uidno ,':passwd' => $hashpasswd, ':uname' => $name ,':tel'=> $tel,':salary'=> $salary,':begin'=> date("Ymd"),':end'=>'0000-00-00',':email'=> $mail, ':did' => $selectOptionD,':pid' => $selectOptionP,':employment'=> '1');
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('新增成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=staff>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=login>';
			}
			
			break;
			
		case 'basic':
			$fidfname_result=$model->getfidfname($_SESSION['did'],$_SESSION['pid']);
			$prepareSQL = "SELECT * FROM company";
			$executeSQL = array();
			$result = $model->getDataSQL($prepareSQL, $executeSQL);

			$prepareSQLCustomer = "SELECT * FROM customer where usingg=0";
			$executeSQLCustomer = array();
			$resultCustomer = $model->getDataSQL($prepareSQLCustomer, $executeSQLCustomer);

			$prepareSQLManufacturer = "SELECT * FROM manufacturer where usingg=0";
			$executeSQLManufacturer = array();
			$resultManufacturer = $model->getDataSQL($prepareSQLManufacturer, $executeSQLManufacturer);

			require("view/basic.php");
			
			break;

		case 'Company_edit': 
			$nam = $_POST['inputName'];
			$tel = $_POST['inputTel'];
			$add = $_POST['inputAddress'];
			$tax = $_POST['inputTaxid'];
			$lea = $_POST['inputLeader'];
			$prepareSQL = "UPDATE company SET name=:name,tel=:tel,address=:address,taxid=:taxid,leader=:leader WHERE 1=1";
			$executeSQL = array(':name' => $nam, ':tel' => $tel, ':address' => $add, ':taxid' => $tax, ':leader' => $lea);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('修改成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}
			break;

		case 'Customer_add':
			$udate;
			$todaydate;
			$uidno;
			$prepareSQL = "SELECT cid FROM customer WHERE 1=:a order by cid desc limit 1";
			$executeSQL = array(':a' => 1);
			$result = $model->getDataSQL($prepareSQL,$executeSQL);
			if($result) {
				foreach($result as $e){
					$last_cid= $e['cid'];
				}
				$udate=(int)(substr($last_cid, 1, 8));
				$todaydate=(int)(date("Ymd"));
				if ($udate != $todaydate) {//今天還沒新增人員
					$today=(string)(date("Ymd"));
					$uidno='c'. $today.'000001';
				}elseif ($udate == $todaydate) {//今天已經有新增人員了
					$endid=substr($last_cid, -6, 6)+1;
					if ($endid < 10) {
						$nowendid='00000'.$endid;
					}elseif (100>$endid && $endid>=10) {
						$nowendid='0000'.$endid;
					}elseif (1000>$endid && $endid>=100) {
						$nowendid='000'.$endid;
					}elseif (10000>$endid && $endid>=1000) {
						$nowendid='00'.$endid;
					}elseif (100000>$endid && $endid>=10000) {
						$nowendid='0'.$endid;
					}elseif (1000000>$endid && $endid>=100000) {
						$nowendid=$endid;
					}
					$today=(string)(date("Ymd"));
					$uidno='c'. $today.$nowendid;
				}
			}else{
				$uidno='c'. $today.'000001';
			}
			
			$nam = $_POST['inputName'];
			$tel = $_POST['inputTel'];
			$add = $_POST['inputAddress'];
			$tax = $_POST['inputTaxid'];
			$lea = $_POST['inputLeader'];
			$prepareSQL = "INSERT INTO customer (cid,cname,tel,address,taxid,leader,tcondition,begin,usingg) VALUES(:cid,:cname,:tel,:address,:taxid,:leader,:tcondition,:begin,:using)";
			$executeSQL = array(':cid' => $uidno ,':cname' => $nam , ':tel' => $tel,':address' => $add,':taxid' => $tax,':leader' => $lea,':tcondition' => '???',':begin' => date("Ymd"),':using' => 0);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alrt('新增成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}else{
				echo "try again";
				
			}
			break;

			case 'Manufacturer_add':
			$udate;
			$todaydate;
			$uidno;
			$today=(string)(date("Ymd"));
			$prepareSQL = "SELECT mid FROM manufacturer WHERE 1=:a order by mid desc limit 1";
			$executeSQL = array(':a' => 1);
			$result = $model->getDataSQL($prepareSQL,$executeSQL);
			if($result) {
				foreach($result as $e){
					$last_mid=$e['mid'];
				}
				$udate=(int)(substr($last_mid, 1, 8));
				$todaydate=(int)(date("Ymd"));
				if ($udate != $todaydate) {//今天還沒新增人員	
					$uidno='m'. $today.'000001';
				}elseif ($udate == $todaydate) {//今天已經有新增人員了
					$endid=substr($last_mid, -6, 6)+1;
					if ($endid < 10) {
						$nowendid='00000'.$endid;
					}elseif (100>$endid && $endid>=10) {
						$nowendid='0000'.$endid;
					}elseif (1000>$endid && $endid>=100) {
						$nowendid='000'.$endid;
					}elseif (10000>$endid && $endid>=1000) {
						$nowendid='00'.$endid;
					}elseif (100000>$endid && $endid>=10000) {
						$nowendid='0'.$endid;
					}elseif (1000000>$endid && $endid>=100000) {
						$nowendid=$endid;
					}
					$uidno='m'. $today.$nowendid;
				}
			}else{
				$uidno='m'. $today.'000001';
			}
			
			$nam = $_POST['inputName'];
			$tel = $_POST['inputTel'];
			$add = $_POST['inputAddress'];
			$tax = $_POST['inputTaxid'];
			$lea = $_POST['inputLeader'];
			$prepareSQL = "INSERT INTO manufacturer (mid,mname,tel,address,taxid,leader,tcondition,begin,usingg) VALUES(:mid,:mname,:tel,:address,:taxid,:leader,:tcondition,:begin,:using)";
			$executeSQL = array(':mid' => $uidno ,':mname' => $nam , ':tel' => $tel,':address' => $add,':taxid' => $tax,':leader' => $lea,':tcondition' => '???',':begin' => date("Ymd"),':using' => 0);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('新增成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}else{
				echo "try again";
				
			}
			break;

		case 'Customer_edit': 
			$id = $_POST['CidInput'];
			$nam = $_POST['inputName'];
			$tel = $_POST['inputTel'];
			$add = $_POST['inputAddress'];
			$tax = $_POST['inputTaxid'];
			$lea = $_POST['inputLeader'];
			$prepareSQL = "UPDATE customer SET cname=:name,tel=:tel,address=:address,taxid=:taxid,leader=:leader WHERE 1=1 and cid=:cid";
			$executeSQL = array(':name' => $nam, ':tel' => $tel, ':address' => $add, ':taxid' => $tax, ':leader' => $lea, ':cid' => $id);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('修改成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}
			break;

		case 'Manufacturer_edit': 
			$id = $_POST['MidInput'];
			$nam = $_POST['inputName'];
			$tel = $_POST['inputTel'];
			$add = $_POST['inputAddress'];
			$tax = $_POST['inputTaxid'];
			$lea = $_POST['inputLeader'];
			$prepareSQL = "UPDATE manufacturer SET mname=:name,tel=:tel,address=:address,taxid=:taxid,leader=:leader WHERE 1=1 and mid=:mid";
			$executeSQL = array(':name' => $nam, ':tel' => $tel, ':address' => $add, ':taxid' => $tax, ':leader' => $lea, ':mid' => $id);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('修改成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}
			break;

		case 'Customer_delete': 
			$id = $_GET['id'];
			$prepareSQL = "UPDATE customer SET usingg=1 WHERE 1=1 and cid=:id";
			$executeSQL = array(':id' => $id);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('修改成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}
			break;

		case 'Manufacturer_delete': 
			$id = $_GET['id'];
			$prepareSQL = "UPDATE manufacturer SET usingg=1 WHERE 1=1 and mid=:id";
			$executeSQL = array(':id' => $id);
			$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
			if($sql == 1) {
				echo "<script>javascript: alert('修改成功')></script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}else{
				echo "try again";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}
			break;

		case 'box_insert':

			if(isset($_POST['add_dname']) && isset($_POST['box_or_pouch']) && isset($_POST['pc_item']) && isset($_POST['pc_num'])) {
			  $arr_box_or_pouch = $_POST['box_or_pouch'];
			  $arr_pc_item = $_POST['pc_item'];
			  $arr_pc_num = $_POST['pc_num'];

			  if((count($arr_box_or_pouch)==count($arr_pc_item)) && (count($arr_pc_item)==count($arr_pc_num))){
			  	$b=0;
			  	$i=0;
			  	$n=0;
			  	foreach ($arr_box_or_pouch as $arr_b) {
			  		if($arr_b=='0'){
			  			$b+=1;
			  			echo "<script>alert('arr_box_or_pouch break);</script>";
			  			break;
			  		}
			  	}
			  	foreach ($arr_pc_item as $arr_i) {
			  		if($arr_i=='0'){
			  			$i+=1;
			  			echo "<script>alert('arr_pc_item break);</script>";
			  			break;
			  		}
			  	}
			  	foreach ($arr_pc_num as $arr_n) {
			  		if($arr_n==''){
			  			$n+=1;
			  			echo "<script>alert('arr_pc_num break);</script>";
			  			break;
			  		}
			  	}
			  	
			  	if($b==0 && $i==0 && $n==0){
			  		echo "<script>alert('it is ok');</script>";
			  		$add_dname = $_POST['add_dname'];

			  		$prepareSQL = "SELECT bid FROM box order by bid desc limit 1";
					$executeSQL = array();
					$bidresult = $model->getDataSQL($prepareSQL, $executeSQL);
					foreach($bidresult as $br){
						$last_bid = $br['bid'];
					}
					echo "<script>alert('last_bid: ".$last_bid."');</script>";
					$endbid=substr($last_bid, -3, 3)+1;
					if ($endbid < 10) {
						$nowendbid='00'.$endbid;
					}elseif (100>$endbid && $endbid>=10) {
						$nowendbid='0'.$endbid;
					}elseif (1000>$endbid && $endbid>=100) {
						$nowendbid=$endbid;
					}
					$add_bid='b'.$nowendbid;
					echo "<script>alert('add_bid: ".$add_bid."');</script>";

			  		$reserve=0;
			  		$prepareSQL = "INSERT INTO box (bid,bname,price,reserve) VALUES(:bid,:bname,:price,:reserve)";
					$executeSQL = array(':bid' => $add_bid ,':bname' => $add_dname , ':price' => '',':reserve' => $reserve);
					$sql = $model->rowCountSQL($prepareSQL, $executeSQL);

					if($sql==1){
						for ($x = 0; $x < count($arr_box_or_pouch); $x++) {
							$prepareSQL = "INSERT INTO box_pouch (bid,poid,quantity) VALUES(:bid,:poid,:quantity)";
							$executeSQL = array(':bid' => $add_bid ,':poid' =>$arr_pc_item[$x]  , ':quantity' => $arr_pc_num[$x] );
							$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
						}
					} 

			  		echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';

			  	}else{
			  		echo "<script>alert('資料輸入不完整');</script>";
			  		echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			  	}
			  }

			}else{
				echo "<script>alert('is not set isset');</script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}
			break;

		case 'pouch_insert':
					
			if(isset($_POST['add_poname']) && isset($_POST['chocolate']) && isset($_POST['c_item']) && isset($_POST['c_num'])) {
			  $arr_chocolate = $_POST['chocolate'];
			  $arr_c_item = $_POST['c_item'];
			  $arr_c_num = $_POST['c_num'];

			  if((count($arr_chocolate)==count($arr_c_item)) && (count($arr_c_item)==count($arr_c_num))){
			  	$ch=0;
			  	$i=0;
			  	$n=0;
			  	foreach ($arr_chocolate as $arr_ch) {
			  		if($arr_ch=='0'){
			  			$ch+=1;
			  			echo "<script>alert('arr_chocolate break);</script>";
			  			break;
			  		}
			  	}
			  	foreach ($arr_c_item as $arr_i) {
			  		if($arr_i=='0'){
			  			$i+=1;
			  			echo "<script>alert('arr_c_item break);</script>";
			  			break;
			  		}
			  	}
			  	foreach ($arr_c_num as $arr_n) {
			  		if($arr_n==''){
			  			$n+=1;
			  			echo "<script>alert('arr_c_num break);</script>";
			  			break;
			  		}
			  	}
			  	
			  	if($ch==0 && $i==0 && $n==0){
			  		echo "<script>alert('it is ok');</script>";
			  		$add_poname = $_POST['add_poname'];

			  		$prepareSQL = "SELECT poid FROM pouch order by poid desc limit 1";
					$executeSQL = array();
					$poidresult = $model->getDataSQL($prepareSQL, $executeSQL);
					foreach($poidresult as $por){
						$last_poid = $por['poid'];
					}
					echo "<script>alert('last_poid: ".$last_poid."');</script>";
					$endpoid=substr($last_poid, -3, 3)+1;
					if ($endpoid < 10) {
						$nowendpoid='00'.$endpoid;
					}elseif (100>$endpoid && $endpoid>=10) {
						$nowendpoid='0'.$endpoid;
					}elseif (1000>$endpoid && $endpoid>=100) {
						$nowendpoid=$endpoid;
					}
					$add_poid='po'.$nowendpoid;
					echo "<script>alert('add_poid: ".$add_poid."');</script>";

			  		$reserve=0;
			  		$prepareSQL = "INSERT INTO pouch (poid,poname,price,reserve) VALUES(:poid,:poname,:price,:reserve)";
					$executeSQL = array(':poid' => $add_poid ,':poname' => $add_poname , ':price' => '',':reserve' => $reserve);
					$sql = $model->rowCountSQL($prepareSQL, $executeSQL);

					if($sql==1){
						for ($x = 0; $x < count($arr_chocolate); $x++) {
							$prepareSQL = "INSERT INTO pouch_chocolate (poid,chid,quantity) VALUES(:poid,:chid,:quantity)";
							$executeSQL = array(':poid' => $add_poid ,':chid' =>$arr_c_item[$x]  , ':quantity' => $arr_c_num[$x] );
							$sql = $model->rowCountSQL($prepareSQL, $executeSQL);
						}
					} 

			  		echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';

			  	}else{
			  		echo "<script>alert('資料輸入不完整');</script>";
			  		echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			  	}
			  }

			}else{
				echo "<script>alert('is not set isset, 資料輸入不完整');</script>";
				echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			}
			break;


		case 'chocolate_insert':
			if(isset($_POST['mid']) && isset($_POST['chname'])) {
				$select_mid = $_POST['mid'];
				$input_chname=$_POST['chname'];

				$prepareSQL = "SELECT chid FROM chocolate order by chid desc limit 1";
				$executeSQL = array();
				$chidresult = $model->getDataSQL($prepareSQL, $executeSQL);
				foreach($chidresult as $cr){
					$last_chid = $cr['chid'];
				}
				echo "<script>alert('last_chid: ".$last_chid."');</script>";
				$endchid=substr($last_chid, -3, 3)+1;
				if ($endchid < 10) {
					$nowendchid='00'.$endchid;
				}elseif (100>$endchid && $endchid>=10) {
					$nowendchid='0'.$endchid;
				}elseif (1000>$endchid && $endchid>=100) {
					$nowendchid=$endchid;
				}
				$add_chid='ch'.$nowendchid;
				echo "<script>alert('add_chid: ".$add_chid."');</script>";

				$reserve=0;
				$prepareSQL = "INSERT INTO chocolate (chid,chname,mid,price,reserve) VALUES(:chid,:chname,:mid,:price,:reserve)";
				$executeSQL = array(':chid' => $add_chid ,':chname' => $input_chname , ':mid' =>$select_mid, ':price' => '', ':reserve' => $reserve);
				$sql = $model->rowCountSQL($prepareSQL, $executeSQL);

				if($sql==1){
					echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
				}else{
			  		echo "<script>alert('ch insert error');</script>";
			  		echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
			  	}
			}else{
		  		echo "<script>alert('資料輸入不完整');</script>";
		  		echo '<meta http-equiv=REFRESH CONTENT=1;url=?action=basic>';
		  	}
			break;

		default:
			require("view/error.php");
			break;
	}
}
else{
	require("view/error.php");
}