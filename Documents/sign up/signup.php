<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'database_project');
define('DB_USER','root');
define('DB_PASSWORD','');

$con=mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("Failed to connect to MySQL1: " . mysql_error());
//$db=mysqli_select_db(DB_NAME,$con) or die("Failed to connect to MySQL: " . mysqli_error());


function NewUser()
{
    echo "Allahu akbar";
	$fullname = $_POST['name'];
	$email = $_POST['email'];
	$password =  $_POST['pass'];
    $DOB = $_POST['dob'];
	$query = "INSERT INTO user (name,password,DOB,email) VALUES ('$fullname',$password','$DOB','$email')";
	$data = mysqli_query ($query)or die(mysqli_error());
	if($data)
	{
	echo "Account Registered.";
	}
}

function SignUp()
{
    echo "Vivek";
if(!empty($_POST['email']))   //checking the 'user' name which is from Sign-Up.html, is it empty or have some text
{
	$query = mysqli_query("SELECT * FROM user WHERE email = '$_POST[email]' AND pass = '$_POST[pass]'") or die(mysqli_error());
    echo "bruh";
	if(!$row = mysqli_fetch_array($query) or die(mysqli_error()))
	{
        echo "Bruh";
		NewUser();
	}
	else
	{
		echo "Account already exists.";
	}
}
}
echo "isset";
if(isset($_POST['submit']))
{
	SignUp();
}
?>
