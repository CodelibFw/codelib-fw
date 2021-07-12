<html>

<head>

    <script language="javascript">
        $(document).ready(function () {
            $("#username").click(function (event) {
                $('#reg-username').html('Check');
            });
            $('#country').change(function () {
                provinces();
            });
            $("#reg-username").click(function (event) {
                checkavail('reg-username','username');
            });
        });

        function checkavail(bid, uid) {
            var uname = $("#" + uid).val();
            if (uname == null || uname.length == 0) {
                showTooltip(uname, "Please enter an username first");
                return;
            }
            $("#" + bid).html('<img src="/images/loading.gif" alt="Loading...please wait">');
            $("#" + bid).load("/user/chkuname/" + uname);
        }

        function submitProfile(uform) {
            emregex = /\S+@\S+\.\S{2,4}/;
            if (uform.username.value.indexOf(" ") != -1) {
                showTooltip('username',"please remove spaces from your username");
                return false;
            }
            if (uform.username.value.indexOf("@") != -1) {
                showTooltip('username',"your username is public, so do not use your email address as your username. Please remove @ from your username");
                return false;
            }
            if (uform.username.value.indexOf(" at ") != -1 || uform.username.value.indexOf("_at_") != -1) {
                return false;
            }
            if (uform.username.value.length < 6) {
                showTooltip('username','username must be at least 6 characters long');
                return false;
            }
            if (uform.password.value.length < 6) {
                showTooltip('password', "password must be at least 6 characters long");
                return false;
            }
            if (!emregex.exec(uform.email.value)) {
                showTooltip('email', "email address is not valid");
                return false;
            }
            if (uform.birthdate.value.length > 0) {
                var parts = uform.birthdate.value.split("/");
                if (parts.length < 3) {
                    showTooltip('datepickerbirth',"birthday format is not valid, please dd/mm/ccyy");
                    uform.birthdate.focus();
                    uform.birthdate.select();
                    return false;
                }
                if (parts[2].length < 4) {
                    showTooltip('datepickerbirth',"in the birthdate field, please enter the year last");
                    uform.birthdate.focus();
                    uform.birthdate.select();
                    return false;
                }
                var y = parseInt(parts[2]);
                if (isNaN(y)) {
                    showTooltip('datepickerbirth',"in the birthdate field, year is incorrect");
                    uform.birthdate.focus();
                    uform.birthdate.select();
                    return false;
                }
                var m = parseInt(parts[1]);
                if (isNaN(m) || m.length > 2 || m > 12) {
                    showTooltip('datepickerbirth',"in the birthdate field, month is incorrect");
                    uform.birthdate.focus();
                    uform.birthdate.select();
                    return false;
                }
                var d = parseInt(parts[0]);
                if (isNaN(d) || d.length > 2 || d > 31) {
                    showTooltip('datepickerbirth',"in the birthdate field, day is incorrect");
                    uform.birthdate.focus();
                    uform.birthdate.select();
                    return false;
                }
            }

            if (uform.password.value != uform.rpwd.value) {
                showTooltip('password',"Both passwords must match ");
                uform.rpwd.focus();
                uform.rpwd.select();
                return false;
            }

            return true;
        }
    </script>
</head>

<body>
<header class="regformhdr d-flex">
	<div class="container text-center my-auto">
		<h4 class="mb-1">Complete the form below</h4>
	</div>
	<div class="overlay"></div>
</header>
    <section id="registerFormSection" style="margin-top: 5px;">
        <div class="container">
            <div class="row">
                <div class="col-md-9 col-xs-12">
					<?= isset($feedback) ? $feedback : '' ?>
                    <form method="post" action="index.php" onsubmit="return submitProfile(this)">
                        <h5>Login Information</h5>
                        <div class="input-group mb-3">
                            <input type="text" name="username" value="<?= isset($username) ? $username : '' ?>" class="form-control" placeholder="username is public, DO NOT use your email address" aria-label="username"
                                aria-describedby="reg-username" required="" id="username">
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" id="reg-username">Check</button>
                            </div>
                        </div>
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6">
                                <input type="password" name="password" value="" class="form-control" id="password" placeholder="Password" required="">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="password" name="rpwd" value="" class="form-control" id="inputPassword4" placeholder="Confirm password" required="">
                            </div>
                        </div>
                        <h5>Personal Information</h5>
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6">
                                <input type="email" name="email" value="<?= isset($email) ? $email : '' ?>" class="form-control" id="email" placeholder="Email" required="">
                            </div>
							<div class="form-group col-md-6">
								<select id="gender" name="gender" class="form-control" required="">
									<option value="" selected>Choose gender</option>
									<option>Male</option>
									<option>Female</option>
									<option>Other</option>
								</select>
							</div>
                        </div>
                        <h5>Legal Information</h5>
                        <div class="form-row">
                            <div class="col-md-6">
								<div class="form-check">
									<input class="form-check-input" name="ageok" type="checkbox" id="ageCheck" required="">
									<label class="form-check-label" for="ageCheck">
										I confirm I am 18 years old or older
									</label>
								</div>

                            </div>
							<div class="col-md-6">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="gridCheck" required="">
									<label class="form-check-label" for="gridCheck">
										I have read and agree to the <a href="#" onclick="showTerms('/disclaimer');">Terms and disclaimer</a>
									</label>
								</div>
							</div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12" replace="fragments/countrylist :: countrylist">
                                <label for="country">Country</label>

                            </div>
                        </div>
                        <input type="hidden" name="csrf" value="<?= isset($csrf) ? $csrf : '' ?>">
                        <input type="hidden" name="clkey" value="user.register">
                        <button type="submit" class="btn btn-primary">Sign up</button>
                    </form>
                </div>
                <div class="col-md-3 col-xs-12"></div>
            </div>
        </div>
    </section>
</body>

</html>
