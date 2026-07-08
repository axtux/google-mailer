<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer autoloader
require 'vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function send_email($filename) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use ENCRYPTION_STARTTLS for port 587
        $mail->Port       = (int)$_ENV['SMTP_PORT'];

        // Recipients
        $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']);
        $mail->addAddress($_ENV['TO_EMAIL']);
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        // Content
        $mail->isHTML(false);
        $mail->Subject = 'New bill from OVH';
        //$mail->Body    = '<h1>Hello!</h1><p>Test</p>';
        $mail->Body = "Hello Alex,\n\nPlease find attached a new bill from OVH!\n\nKind regards,\nBill";

        // Attachment(s)
        $mail->addAttachment($filename, basename($filename));
        // You can add more:
        // $mail->addAttachment('/path/to/image.jpg', 'photo.jpg');

        $mail->send();
        echo 'Email sent successfully!';

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function send_all_files_in_folder(string $folderPath) {
    // Validate folder
    if (!is_dir($folderPath)) {
        die("Error: Folder not found " . $folderPath);
    }

    // Get all files (excluding . and ..)
    $files = array_diff(scandir($folderPath), ['.', '..']);

    $sentCount = 0;
    $total = 0;

    echo "Processing folder: " . $folderPath;

    foreach ($files as $file) {
        $fullPath = rtrim($folderPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;

        // Skip directories
        if (is_dir($fullPath)) {
            continue;
        }

        ++$total;
        echo "Found: " . $file . ":";

        if (send_email($fullPath)) {
            $sentCount++;
        }

        // Optional: small delay to avoid rate limits
        sleep(1);
    }

    echo "Sent $sentCount of $total files.";
}

if ($argc < 2) {
    echo "Usage: php ".$argv[0]." <folder_path>\n";
    echo "Example: php ".$argv[0]." /path/to/attachments\n";
    exit(1);
}

send_all_files_in_folder($argv[1]);
