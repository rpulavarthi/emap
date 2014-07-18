<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title>OpenLayers with jQuery Mobile</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="apple-mobile-web-app-capable" content="yes">
		 <link rel="stylesheet" href="../global/css/jquery-mobile/themes/default/jquery.mobile-1.1.0.min.css">
		<script src='../global/js/jquery-1.7.2.min.js'></script>
        <script src="../global/js/jquery.mobile-1.1.0.min.js"></script>
    </head>
	<body>

		<!-- start page -->
		<div data-role="page">

			<!-- start header -->
			<div data-role="header">
				<h1>Project Name Login</h1>
				
			</div>
			<!-- end header -->

			<!-- start content -->
			<div data-role="content" data-inset="true">	
				<form action="../auth/login" method="POST">
					<fieldset>

					<label for="email">Username:</label>
					<input name="username" id="email" value=""  />

					<label for="password">Password:</label>
					<input type="password" name="password" id="password" value="" />
					<input id="Submit1" type="submit" value="Login" data-role="button" data-inline="true" data-theme="b" />

				   <hr />
				   Don't have a login? <a href="register.aspx">Sign Up</a>
				   </fieldset>
				</form>   
			</div>
			<!-- end content -->

			<!-- start footer -->
			<div data-role="footer">
			   <h4></h4>
			</div>
			<!-- end footer -->
			
		</div>
		<!-- end page -->
	</body>
</html>