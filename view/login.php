<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>登入</title>
	<link rel="stylesheet" href="css/my_css.css" type="text/css"><!-- 匯入my_css -->
   <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
</head>
<body>
	<div class="container" style="margin-top: 300px">
      <div class="row">
        <div class="col-lg-3 text-center"></div>

        <div class="col-lg-6 text-center asas">
          <form method="POST" action="?action=login_data">
      			編號：<input name="uid" type="text" value="輸入編號" /><br/> <br/>
      			密碼：<input name="passwd" type="password" maxlength="20" placeholder="(限制輸入20個字)"/><br /> <br />
      			<button class="btn btn-primary">登入</button>
      		</form>
        </div>
      </div>
    </div>
	
</body>
</html>