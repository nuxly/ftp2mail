<?php

namespace nuxly\ftp2mail;

/**
 * © Nuxly SAS
 * 
 * Lionel Vinceslas <lvinceslas@nuxly.com>
 * 
 * This software is a computer program whose purpose is to automatically 
 * catch files on a FTP server and send them by email.
 * 
 * This software is governed by the CeCILL license under French law and
 * abiding by the rules of distribution of free software.  You can  use, 
 * modify and/ or redistribute the software under the terms of the CeCILL
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info". 

 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability. 
 * 
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or 
 * data to be ensured and,  more generally, to use and operate it in the 
 * same conditions as regards security. 
 * 
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 * */

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use nuxly\ftp2mail\DataHistory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class FTP2mail extends CLI
{
    
    protected function setup(Options $options)
    {
        $options->setHelp('Automatically send FTP files by e-mail');
        //$options->registerOption('version', 'print version', 'v');
        $options->registerOption('config', 'Set configuration file (JSON)', 'c', 'filename');
    }

    protected function main(Options $options)
    {
        $downloadedFiles = array();


        if (!($configFile=$options->getOpt('config'))) {
            $configFile = "conf.json";
        }

        // Parsing config file
        $conf = new FTP2mailConfig($configFile);

        $history = new DataHistory('data.json');

        $files_path = $conf->get("files.path") . (substr($conf->get("files.path"), -1) !== '/'?'/':null);
        $log_path   = $conf->get("log.path") . (substr($conf->get("log.path"), -1) !== '/'?'/':null);

        $logger = new Logger('FTP2mail');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../' . $log_path . $conf->get("log.filename") , Logger::DEBUG));
        
        $logger->info("FTP2mail started with '$configFile'");
        
        // Connecting to the FTP server
        $ftp = new \FtpClient\FtpClient();
        $logger->info("Conecting to host " . $conf->get("ftp.host"));
        $ftp->connect($conf->get("ftp.host"), false, $conf->get("ftp.port"));
        $ftp->login($conf->get("ftp.login"), $conf->get("ftp.password"));
        
        // Checking the FTP server
        foreach ($ftp->scandir('.', false) as $key => $value) {
            if (explode('#', $key)[0] === "file") {

                if (file_exists($files_path . $value["name"])) {

                    $suffix = " (" . date("Y-m-d His") . ")";
                    $files_path_parts = pathinfo($files_path . $value["name"]);
                    $renamed = $files_path_parts["filename"] . $suffix . '.' .  $files_path_parts["extension"];

                    $logger->info("File '" . $files_path . $value["name"] . "' already exists. Renamed in '$files_path$renamed'");
                } else {
                    $renamed = $value["name"];
                }

                // Downloading file from FTP server
                $logger->info("Downloading file '$renamed'..");
                $ftp->get($files_path . $renamed, $value["name"], FTP_BINARY);
                
                $file_date = date('Y-m-d');
                $file_time = date('H:i:s');
                $file_id = $history->id($renamed, $file_time);
                $history->add($file_id, $file_date, $file_time, $renamed, $files_path . $renamed);
                $history->save();

                array_push($downloadedFiles, $file_id);
                
                // Deleting file from FTP server
                $logger->info("Deleting file '" . $value["name"] . "' from FTP server..");
                $ftp->remove($value["name"]);
            }
        }

        // Closing the FTP connection
        $ftp->close();
        $logger->info("Closing connection to host " . $conf->get("ftp.host"));

        // Sending Mail
        if (!empty($downloadedFiles)) {
            $mail = new PHPMailer();

            try { 
                /*
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                  // Enable verbose debug output
                $mail->isSMTP();                                        // Send using SMTP
                $mail->Host       = $conf->get("mail.smtp.host");
                $mail->SMTPAuth   = true;
                $mail->Username   = $conf->get("mail.smtp.username");
                $mail->Password   = $conf->get("mail.smtp.password");
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                $mail->Port       = intval($conf->get("mail.smtp.port"));
                */

                $mail->setFrom($conf->get("mail.from"));
                $mail->addAddress($conf->get("mail.to"));

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $conf->get("mail.subject");
                $message    = 'Hi,<br><br>The files below are available for ' . $conf->get("files.timeout") . ' days :<br><br><table><thead style="background: silver"><tr><th>#</th><th>Filename</th></tr></thead><tbody>';
                $i=0;
                foreach ($downloadedFiles as $key => $id) {
                    $i++;
                    $file = $history->get($id);
                    $message .= "<tr><td style='text-align: right'>$i</td><td><a href=\"" . $conf->get("url") ."/?f=$id\" >{$file['name']}</a><td></tr>";
                }
                $message .="</tbody></table><br><br>Cheers,<br><br>FTP2mail.";
                $mail->Body = $message;

                $mail->send();
                $logger->info("Mail successfully sent to '" . $conf->get("mail.to") . "'");
            } catch (Exception $e) {
                $logger->info("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }

        }

        // Erasing obsolete files
        $files = scandir($files_path);
        
        foreach ($files as $key => $value) {
            if (substr($value,0,1) !== "." && !is_dir($files_path . $value)) {
                $file_timestamp = filectime($files_path . $value);
                $current_timestamp = time();
                $seconds = ($current_timestamp - $file_timestamp);

                if (floor($seconds / 86400) >= intval($conf->get("files.timeout"))) {
                    $logger->info("Removing file '" . $files_path . $value . "'", array($file_timestamp));
                    unlink($files_path . $value);
                }
            }
        }

        // End
        $logger->info("FTP2mail successfully completed");
    }
}