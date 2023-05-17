
<?php

// Include config file
require_once "../database.php";
require_once '../vendor/autoload.php';
 
// Define variables and initialize with empty values
$first_name = $last_name = $username = $password = $confirm_password = $email = $confirm_email = $token= "";

$first_name_err = $last_name_err = $username_err = $password_err = $confirm_password_err = $email_err = $confirm_email_err = $token_err= "";

$role = 'client';
 

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

      // Validate first name
      if(empty(trim($_POST["first_name"]))){
        $first_name_err = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if(empty(trim($_POST["last_name"]))){
        $last_name_err = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }
 
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else{
        // Prepare a select statement
        $sql = "SELECT user_id FROM tbl_users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }

        // Validate email
        if(empty(trim($_POST["email"]))){
            $email_err = "Please enter your email address.";
        } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
            $email_err = "Please enter a valid email address.";
        } else{
            $email = trim($_POST["email"]);
        }


        // Check if email is already taken
$sql = "SELECT user_id FROM tbl_users WHERE email = ?";
      
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $param_email);
    $param_email = trim($_POST["email"]);
        
    if(mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
          
        if(mysqli_stmt_num_rows($stmt) == 1) {
            $email_err = "This email is already registered.";
        } else {
            $email = trim($_POST["email"]);
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }

    mysqli_stmt_close($stmt);
}


        // Validate confirm email
        if (empty(trim($_POST["confirm_email"]))) {
    $confirm_email_err = "Please confirm email address.";
        } else {
    $confirm_email = trim($_POST["confirm_email"]);
        if ($confirm_email !== $email) {
        $confirm_email_err = "Email addresses do not match.";
        }
    }

   
    // Check input errors before inserting in database
    if(empty($first_name_err)&&empty($last_name_err)&&empty($username_err) && empty($email_err)&&empty($token_err)&&empty($password_err) && empty($confirm_password_err)&& empty($role_err)){

         // Generate a verification token
        //  $token = bin2hex(random_bytes(32));

        $token = rand(100000, 999999);

        // Prepare an insert statement
        $sql = "INSERT INTO tbl_users (first_name, last_name, username, email, token, password, user_type, verification_status) VALUES (?, ?, ?, ?, ?, ?,?,?)";
        
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssisss", $param_first_name, $param_last_name, $param_username, $param_email, $param_token, $param_password, $param_role, $param_verification_status);


            
            // Set parameters
            $param_first_name=$first_name;
            $param_last_name=$last_name;
            $param_username = $username;
            $param_email=$email;
            $param_token=$token;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_role = 'client';
            $param_verification_status='pending';

            
        /*$email->addContent(
            "text/plain",
            "To finish creating your account please click the link to verify your email: <a href='http://localhost/Online-Exam-System/verify-email.php?code={$verificationCode}'>Verify Email</a>"
        );*/
        

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){

                $user_id = mysqli_insert_id($conn);

                // Send verification email using SendGrid
                $SendEmail = new \SendGrid\Mail\Mail();
                // Set the verification URL as a substitution value
            // Set the HTML content of the email using your SendGrid template, with the substitution value included
                $SendEmail->setFrom("fit_youwebsite@outlook.com", "Fit You");
                $SendEmail->setTemplateId("d-ba05ed0bbbb545449dc35d43ac06c7b6");
                $SendEmail->addDynamicTemplateData("token", $token);
                $SendEmail->addDynamicTemplateData("user_id", $user_id);
                $SendEmail->addTo($email, $first_name . " " . $last_name);

                $sendgrid = new \SendGrid($SENDGRID_API_KEY);
                try {
                    $response = $sendgrid->send($SendEmail);
                    // Check response status code and headers for errors
                    if ($response->statusCode() >= 400) {
                        throw new Exception('Error sending email: ' . $response->body());
                    }  
                    // Redirect to login page after successful sign up
                } catch (Exception $e) {
                    $errors[] = 'Error sending email: ' . $e->getMessage();
                }
            //        // Send the verification email
            // $to = $email;
            // $subject = 'Verify Your Email Address';
            // $message = '
            //     Hello '.$username.',<br><br>
            //     Thank you for signing up! To activate your account, please click the link below:<br><br>
            //     <a href="http://yourwebsite.com/verify.php?email='.$email.'&token='.$token.'">Verify Email Address</a><br><br>
            //     If you did not create an account on our website, please ignore this email.<br><br>
            //     Best regards,<br>
            //     Your Website Team
            // ';
            // $headers = 'From: yourwebsite@example.com' . "\r\n" .
            //             'Reply-To: yourwebsite@example.com' . "\r\n" .
            //             'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            //             'X-Mailer: PHP/' . phpversion();
            // mail($to, $subject, $message, $headers);
            //      // Redirect to login page
            //      header("location: login.php");
            //      exit();

                // Get the user_id of the newly inserted row
                // $user_id = mysqli_insert_id($conn); qika qeta e kompentova une BLERTA
                  // Insert additional data into the appropriate table

                  if($role == "client"){
                    $sql = "INSERT INTO tbl_client_profiles (user_id, first_name, last_name) VALUES (?,?,?)";
                } else{
                    $sql = "INSERT INTO tbl_staff_profiles (user_id, first_name, last_name) VALUES (?,?,?)"; //Qik
                }

                $stmt2 = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt2, "iss", $user_id, $first_name, $last_name);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                // Redirect the user to the appropriate dashboard page
              //  header("location:../login.php");
              header("Location:http://localhost/UEB2_PROJEKTI/client/register-client.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}
    ?>
