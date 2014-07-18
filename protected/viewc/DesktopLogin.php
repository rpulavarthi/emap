<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Emap Login</title>
		<script src='global/js/jquery-1.7.2.min.js'></script>
		<script src='global/js/jquery-ui-1.8.20.custom.min.js'></script>
		<link rel="stylesheet" href="global/css/jquery/themes/base/jquery.ui.all.css">
		<style>
		body { font-size: 62.5%; }
		label, input { display:block; }
		input.text { margin-bottom:12px; width:95%; padding: .4em; }
		fieldset { padding:0; border:0; margin-top:25px; }
		h1 { font-size: 1.2em; margin: .6em 0; }
		div#users-contain { width: 350px; margin: 20px 0; }
		div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
		div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
		.ui-dialog .ui-state-error { padding: .3em; }
		.validateTips { border: 1px solid transparent; padding: 0.3em; }
	</style>
		<script>
		$(function() {
			$( "#login" ).dialog({
				open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
				modal: true,
				resizable: false,
				closeOnEscape: false,
				buttons: {


					"Login": function() {
						var form_data = {
							username: $("#login_username").val(),
							password: $("#login_password").val()
						};

						$.ajax({
							type: "POST",
							url: "auth/login",
							data: form_data,
							success: function(response){
								if(response.success){

									//check for pw change
									if(response.message == "Change Password Requested")
										$( "#changepw" ).dialog( "open" );
									else

									//redirect
									window.location  = "/emap";
								}else{
									//show error
									$( "#dialog-message" ).append("<p>"+response.message+"</p>");
									$( "#dialog:ui-dialog" ).dialog( "destroy" );
									$( "#dialog-message" ).dialog({
										modal: true,
										resizable: false,
										buttons: {
											Ok: function() {
												$('#dialog-message').empty();
												$( this ).dialog( "close" );
											}
										}
									});
								}
							}
						});
					},
					"Register": function(){$( "#register" ).dialog( "open" );},
					"Forgot PW": function(){$( "#forgotpw" ).dialog( "open" );}





				}
			});

			$( "#changepw" ).dialog({
				open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
				autoOpen: false,
				modal: true,
				resizable: false,
				closeOnEscape: false,
				buttons: {
					"Submit": function(){
						var form_data = {
							password1: $("#change_pw_1").val(),
							password2: $("#change_pw_2").val()
						};

						$.ajax({
							type: "POST",
							url: "auth/changepassword",
							data: form_data,
							success: function(response){
								if(response.success){
									//redirect back to login
									window.location  = "/emap";
								}else{
									//show error
									$( "#dialog-message" ).append("<p>"+response.message+"</p>");
									$( "#dialog:ui-dialog" ).dialog( "destroy" );
									$( "#dialog-message" ).dialog({
										modal: true,
										resizable: false,
										buttons: {
											Ok: function() {
												$('#dialog-message').empty();
												$( this ).dialog( "close" );
											}
										}
									});
								}
							}
						});
					}
				}

			});



			$( "#forgotpw" ).dialog({
                                open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
                                autoOpen: false,
                                modal: true,
                                resizable: false,
                                closeOnEscape: false,
                                buttons: {
					 "Submit": function(){
                                                var form_data = {
                                                        email: $("#forgotpw_email").val()
                                                };

                                                $.ajax({
                                                        type: "POST",
                                                        url: "auth/forgotpassword",
                                                        data: form_data,
                                                        success: function(response){

                                                                if(response.success){
                                                                        //redirect back to login
                                                                        window.location  = "/emap";
                                                                }else{
                                                                        //show error
                                                                        $( "#dialog-message" ).append("<p>"+response.message+"</p>");
                                                                        $( "#dialog:ui-dialog" ).dialog( "destroy" );
                                                                        $( "#dialog-message" ).dialog({
                                                                                modal: true,
                                                                                resizable: false,
                                                                                buttons: {
                                                                                        Ok: function() {
                                                                                                $('#dialog-message').empty();
                                                                                                $( this ).dialog( "close" );
                                                                                        }
                                                                                }
                                                                        });
                                                                }
                                                        }
                                                });
                                        },
                                        Cancel: function() {
                                                $( this ).dialog( "close" );
                                        }
                                }
                        });



			$( "#register" ).dialog({
				open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
				autoOpen: false,
				modal: true,
				resizable: false,
				closeOnEscape: false,
				buttons: {
					"Submit": function(){
						var form_data = {
							username: $("#register_username").val(),
							email: $("#register_email").val()
						};

						$.ajax({
							type: "POST",
							url: "auth/register",
							data: form_data,
							success: function(response){

								if(response.success){
									//redirect back to login
									window.location  = "/emap";
								}else{
									//show error
									$( "#dialog-message" ).append("<p>"+response.message+"</p>");
									$( "#dialog:ui-dialog" ).dialog( "destroy" );
									$( "#dialog-message" ).dialog({
										modal: true,
										resizable: false,
										buttons: {
											Ok: function() {
												$('#dialog-message').empty();
												$( this ).dialog( "close" );
											}
										}
									});
								}
							}
						});
					},
					Cancel: function() {
						$( this ).dialog( "close" );
					}
				}
			});
		});
		</script>
	</head>
	<body>
	<div>
		<div id="login" title="Login">
			<form>
				<fieldset>
					<label for="login_username">Username</label>
					<input type="text" name="name" id="login_username" class="text ui-widget-content ui-corner-all" />
					<label for="login_password">Password</label>
					<input type="password" name="password" id="login_password" value="" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</form>
		</div>
		<div id="register" title="Register new user">
			<p class="validateTips">All form fields are required</p>
			<form>
			<fieldset>
				<label for="register_name">Username</label>
				<input type="text" name="name" id="register_username" class="text ui-widget-content ui-corner-all" />
				<label for="register_email">Email</label>
				<input type="text" name="email" id="register_email" value="" class="text ui-widget-content ui-corner-all" />
			</fieldset>
			</form>
		</div>
		<div id="forgotpw" title="Forgot Password">
                        <p class="validateTips">All form fields are required</p>
                        <form>
                        <fieldset>
				 <label for="forgotpw_email">Email</label>
                                <input type="text" name="email" id="forgotpw_email" value="" class="text ui-widget-content ui-corner-all" />
                        </fieldset>
                        </form>
                </div>


		<div id="changepw" title="Change password">
			<p class="validateTips">Passwords must match</p>
			<form>
			<fieldset>
				<label for="change_pw_1">New Password</label>
				<input type="password" name="password" id="change_pw_1" class="text ui-widget-content ui-corner-all" />
				<label for="change_pw_2">New Password</label>
				<input type="password" name="password" id="change_pw_2" value="" class="text ui-widget-content ui-corner-all" />
			</fieldset>
			</form>
		</div>
		<div id="dialog-message" title="Warning!">
		</div>
	</div>
	</body>
</html>

