<?php
class model{

	function __construct(PDO $dbh) {
		$this->dbh = $dbh;
	}

	function getDataSQL($prepareSQL, $executeSQL) { //顯示資料
		$dbh = $this->dbh;
		$sql = $dbh->prepare($prepareSQL);
		$sql->execute($executeSQL);
		return $sql->fetchAll();
	}

	function rowCountSQL($prepareSQL, $executeSQL) { //執行資料並回傳結果的Boolean
		$dbh = $this->dbh;	
		$sql = $dbh->prepare($prepareSQL);
		$sql->execute($executeSQL);
		return $sql->rowCount();
		// return $dbh->lastInsertId(); //顯示成功筆數
	}

	function getfidfname($did, $pid) { //回傳fid
		$prepareSQL = "SELECT function.fid, function.fname FROM permission, function where permission.did= :did and permission.pid = :pid and permission.fid = function.fid";
		$executeSQL = array(':did' => $did,':pid' => $pid);
		$dbh = $this->dbh;	
		$sql = $dbh->prepare($prepareSQL);
		$sql->execute($executeSQL);
		return $sql->fetchAll();
	}

	function readbox(){
		$prepareSQL = "SELECT bid, bname, price, reserve FROM box";
		$executeSQL = array();
		$dbh = $this->dbh;	
		$sql = $dbh->prepare($prepareSQL);
		$sql->execute($executeSQL);
		return $sql->fetchAll();
	}

	function readpouch(){
		$prepareSQL = "SELECT poid, poname, price, reserve FROM pouch";
		$executeSQL = array();
		$dbh = $this->dbh;
		$sql = $dbh->prepare($prepareSQL);
		$sql->execute($executeSQL);
		return $sql->fetchAll();
	}

	function readchocolate(){
		$prepareSQL = "SELECT chocolate.chid, chocolate.chname, manufacturer.mname, chocolate.price, chocolate.reserve FROM chocolate, manufacturer where chocolate.mid = manufacturer.mid";
		$executeSQL = array();
		$dbh = $this->dbh;	
		$sql = $dbh->prepare($prepareSQL);
		$sql->execute($executeSQL);
		return $sql->fetchAll();
	}

	function readmid(){
		$prepareSQL = "SELECT mid, mname FROM manufacturer";
		$executeSQL = array();
		$dbh = $this->dbh;
		$sql = $dbh->prepare($prepareSQL);
		$sql->execute($executeSQL);
		return $sql->fetchAll();
	}

	
	
}