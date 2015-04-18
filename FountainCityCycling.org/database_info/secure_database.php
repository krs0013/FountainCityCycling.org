<!DOCTYPE html>
<html>

<head>

<SCRIPT>
	function passWord() {

		var testV = 1;
		var pass1 = prompt('Please enter your password','');
		while (testV < 3) {
			if (!pass1) {
				history.go(-1);
			}
			if (pass1.toLowerCase() == "qwertyuiop") {
				alert('You Got it Right!');
				window.open('secure_database.php');
				break;
			}
			testV += 1;
			var pass1 = prompt('Access Denied - Password Incorrect, Please try Again','Password');
		}		
		if (pass1.toLowerCase != "password" & testV == 3)
			history.go(-1);
		return "";

	}
</SCRIPT>
<FORM>
	<input type="button" value="Enter Protected Area" onClick="passWord()">
</FORM>
</head>

</html>
