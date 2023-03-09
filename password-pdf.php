<?php
/**
 * Plugin Name: Password PDF
 * Plugin URI: ''
 * Description: Password PDF
 * Version: 1.0
 * Author: Bhavin
 * Author URI: ''
 * Text Domain: password-pdf
 */
/*
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Define variables
 */
define('PASSPDF_FILE', __FILE__);
define('PASSPDF_DIR', plugin_dir_path(PASSPDF_FILE));
define('PASSPDF_URL', plugins_url('/', PASSPDF_FILE));
define('PASSPDF_TEXTDOMAIN', 'password-pdf');

/*
 * Register admin menu for password PDF
 */

function register_custom_menu_page() {
    add_menu_page(__('Password PDF', PASSPDF_TEXTDOMAIN), __('Password PDF', PASSPDF_TEXTDOMAIN), 'manage_options', 'password_pdf', 'password_pdf_callback', '', 6);
}

add_action('admin_menu', 'register_custom_menu_page');

/*
 * Admin menu callback for password PDF
 * Generating HTML table for PDF
 */

function password_pdf_callback() {
    ?>
    <div class="wrap">
        <h1><?php _e('Get PDF', PASSPDF_TEXTDOMAIN); ?></h1>
        <form method="post">
            <?php
            if (!empty($_POST['mail_to'])) {
                $to = sanitize_email($_POST['mail_to']);
                $test_email = createPasswordPDF($to);
                // Email response
                if ($test_email) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e('Email has been sent!', PASSPDF_TEXTDOMAIN); ?></p>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e('Email not sent!', PASSPDF_TEXTDOMAIN); ?></p>
                    </div>
                    <?php
                }
            }
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('To', PASSPDF_TEXTDOMAIN); ?></th>
                    <td>
                        <input type="email" name="mail_to" value="">
                        <p class="description"><i><?php _e('Enter "To address" here.', PASSPDF_TEXTDOMAIN); ?></i></p>
                    </td>
                </tr>
                <tr valign="top">
                    <td><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Send Mail', PASSPDF_TEXTDOMAIN); ?>"></td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}

/*
 * Create password protected PDF
 * Input: Entered email
 * Output: Sending email response
 */

function createPasswordPDF($mail_to) {
    // Include MPDF lib
    require_once(PASSPDF_DIR . 'vendor/autoload.php');

    $receiptID = 1;
    $pdfHTML = '';
    // Initiate PDF HTML
    $pdfHTML = '<div style="border:5px solid #403E4B;padding:20px;">
            <table width="100%" cellspacing="0">
                <tbody>
                    <tr>
                        <td style="text-align:left;"><img src="' . PASSPDF_DIR . 'images/Techesta-logo.png" width="300" height="70"></td>
                        <td style="text-align:right;">103, Angle Square, Near VIP Circle, <br>Utran, <br>Surat - 394105.</td>
                    </tr>
                </tbody>
            </table>
            <hr><br><br>

            <table width="100%" cellspacing="0" cellpadding="10">
                <tbody>
                    <tr>
                        <td style="text-align:left;background-color:#dcdcdc;"><strong>Slip No.:</strong> ' . $receiptID . '</td>
                        <td style="text-align:right;background-color:#dcdcdc;"><strong>Date:</strong> ' . date('d/m/Y') . '</td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table width="100%" cellspacing="0" cellpadding="10" style="background-color:#dcdcdc;">
                <tr>
                    <td style="border-bottom:1px solid #000;"><strong>Name:</strong></td>
                    <td style="border-bottom:1px solid #000;">Bhavin Patel</td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #000;"><strong>Email Address:</strong></td>
                    <td style="border-bottom:1px solid #000;">dev13.techeshta@gmail.com</td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #000;"><strong>Mobile Number:</strong></td>
                    <td style="border-bottom:1px solid #000;">7894561230</td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #000;"><strong>Address:</strong></td>
                    <td style="border-bottom:1px solid #000;">103, Angle Square, Near VIP Circle, Utran, Surat - 394105.</td>
                </tr>   
                <tr>
                    <td style="border-bottom:1px solid #000;"><strong>Amount:</strong></td>
                    <td style="border-bottom:1px solid #000;">Rs. 100.00</td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #000;"><strong>PAN Card Number:</strong></td>
                    <td style="border-bottom:1px solid #000;">ABCDEF</td>
                </tr>
            </table>
        </div>';

    // Creating dir if not exists
    if (!file_exists(PASSPDF_DIR . 'test_pdfs')) {
        mkdir(PASSPDF_DIR . 'test_pdfs', 0777, true);
    }

    // Set PDF password
    $pdfpass = strtolower(substr('Bhavin Patel', 0, 4));
    // Set PDF orientation
    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'orientation' => 'L']);
    // Set password protection
    $mpdf->SetProtection(array('copy', 'print'), 'UserPassword', $pdfpass);
    // Write HTML to PDF
    $mpdf->WriteHTML($pdfHTML);
    // Save file to directory
    $mpdf->Output(PASSPDF_DIR . 'test_pdfs/test_receipt_' . $receiptID . '.pdf', 'F');

    // Sending mail test pdf receipt
    $to = $mail_to;
    $subject = "Your test receipt";
    $message = "Please find the attached test receipt below.\r\nHere is the attachment password: $pdfpass\r\n\r\nThanks!";
    $headers = 'From: Bhavin Patel <dev13.techeshta@gmail.com>' . "\r\n";
    $attachments = array(PASSPDF_DIR . 'test_pdfs/test_receipt_' . $receiptID . '.pdf');
    $mailResponse = wp_mail($to, $subject, $message, $headers, $attachments);
    // Return mail response
    return $mailResponse;
}
