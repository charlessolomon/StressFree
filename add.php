<?php
include('../conn/conn.php'); 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

if (isset($_POST['register'])) {
    try {
        // Fetching form data
        $fullName = $_POST['fullname'];
        $contactNumber = $_POST['contact_number'];
        $email = $_POST['email'];
        $streetAddress = $_POST['street_address'];
        $edu = $_POST['edu'];
        $degree = $_POST['degree'];
        $study = $_POST['study'];
        $gradDate = $_POST['grad_date'];
        $identityCard = $_POST['identity'];
        $country = $_POST['country'];
        $zipCode = $_POST['zip_code'];
        
        // Handle image uploads
        $images = ['cv_image' => '', 'user_image' => ''];
        $uploadDir = '../uploads/';  

        foreach ($images as $key => &$imagePath) {
            if (!empty($_FILES[$key]['name']) && $_FILES[$key]['error'] == UPLOAD_ERR_OK) {
                $imageName = time() . '_' . basename($_FILES[$key]['name']);
                $imagePath = $uploadDir . $imageName;
                if (!move_uploaded_file($_FILES[$key]['tmp_name'], $imagePath)) {
                    throw new Exception("Failed to upload $key.");
                }
            } else {
                $imagePath = '';  
            }
        }

        // Insert data into the database
        $stmt = $conn->prepare("INSERT INTO `tbl_user` (`fullname`, `email`, `contact_number`, `street_address`, `study`, `grad_date`, `cv_image`, `user_image`, `identity_card`, `zip_code`, `edu`, `degree`, `country`) 
                                VALUES (:fullname, :email, :contact_number, :street_address, :study, :grad_date, :cv_image, :user_image, :identity_card, :zip_code, :edu, :degree, :country)");

        $stmt->bindParam(':fullname', $fullName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':contact_number', $contactNumber, PDO::PARAM_STR);
        $stmt->bindParam(':street_address', $streetAddress, PDO::PARAM_STR);
        $stmt->bindParam(':study', $study, PDO::PARAM_STR);
        $stmt->bindParam(':grad_date', $gradDate, PDO::PARAM_STR);
        $stmt->bindParam(':cv_image', $images['cv_image'], PDO::PARAM_STR);
        $stmt->bindParam(':user_image', $images['user_image'], PDO::PARAM_STR);
        $stmt->bindParam(':identity_card', $identityCard, PDO::PARAM_STR);
        $stmt->bindParam(':zip_code', $zipCode, PDO::PARAM_STR);
        $stmt->bindParam(':edu', $edu, PDO::PARAM_STR);
        $stmt->bindParam(':degree', $degree, PDO::PARAM_STR);
        $stmt->bindParam(':country', $country, PDO::PARAM_STR);

        $stmt->execute();

        // Prepare email
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your.email@gmail.com'; // Replace with your email address
        $mail->Password = 'your_password'; // Replace with your email password or app password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('your.email@gmail.com', 'Your Name');
        $mail->addAddress('Info@angellistusa.com');
        $mail->addReplyTo('your.email@gmail.com', 'Your Name');

        // Attach images
        foreach ($images as $imagePath) {
            if ($imagePath) {
                $mail->addAttachment($imagePath);
            }
        }

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'New User Registration';
        $mail->Body = "New user registered:<br>
                       Full Name: $fullName<br>
                       Contact Number: $contactNumber<br>
                       Email: $email<br>
                       Street Address: $streetAddress<br>
                       Education: $edu<br>
                       Degree: $degree<br>
                       Field of Study: $study<br>
                       Country: $country<br>
                       Zip Code: $zipCode<br>
                       Graduation Date: $gradDate<br>
                       Identity Card: $identityCard<br>";

        $mail->send();

        echo "<script>
            alert('Application successful!');
        </script>";
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "General Error: " . $e->getMessage();
    }
}

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$mail->SMTPDebug = 2;
?>
