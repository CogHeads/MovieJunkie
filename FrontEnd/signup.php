<?php

function SignUp()
{
    require_once "connection.php";
if(!empty($_POST['email']))   //checking the 'user' name which is from Sign-Up.html, is it empty or have some text
{
	$query = mysqli_query($con,"SELECT * FROM user WHERE email = '$_POST[email]' AND password = '$_POST[pass]'") or die(mysqli_error($con));
	if(!$row = mysqli_fetch_array($query) or die(mysqli_error($con)))
	{
	    $fullname = $_POST['name'];
	    $email = $_POST['email'];
	    $password =  $_POST['pass'];
        $DOB = $_POST['dob'];
	    $query = "INSERT INTO user (name,password,DOB,email) VALUES        ('$fullname','$password','$DOB','$email')";
	    $data = mysqli_query ($con, $query)or die(mysqli_error($con));
	    if($data)
	    {
	        echo "Account Registered";
            header("Location: http://127.0.0.1:58932/FrontEnd/index.html");
	    }
	}
	else
	{
		echo "Account already exists.";
	}
}
}
if(isset($_POST['submit']))
{
	SignUp();
}
?>
