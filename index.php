<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//error_reporting(0);

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

 
require __DIR__ . '/vendor/autoload.php';


if (!isset($_GET['f'])) {

    $cli = new nuxly\ftp2mail\FTP2mail();
    $cli->run();
    die("Done.");
} elseif (!file_exists($_GET['f'])) {
    die("This file does not exist.");
} else {
    $file = $_GET['f'];

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"$file\""); 

}