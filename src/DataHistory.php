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
 * 
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
 */


class DataHistory
{
    private $configuration = array();
    private $configuration_file = null;
    private $files_to_remove = array();

    /**
     * @author Lionel Vinceslas <lvinceslas@nuxly.com>
     * @version 2019-12-16.1
     * @param string $configuration_file
     * @return DataHistory
     */
    public function __construct(string $configuration_file)
    {
        if (!is_array($this->configuration = json_decode(file_get_contents($configuration_file), true))) {
            $this->configuration = array();
        }
        
        $this->configuration_file = $configuration_file;
    }

    /**
     * Return a unique name for a given filename and time
     * @author Lionel Vinceslas <lvinceslas@nuxly.com>
     * @version 2019-12-16.1
     * @param string $filename
     * @param string $time
     * @return string
     */
    public function id(string $filename, string $time)
    {
        return md5($filename . $time);
    }

    /**
     * Save the history
     * @author Lionel Vinceslas <lvinceslas@nuxly.com>
     * @version 2020-01-06.1
     * @return bool
     */
    public function save()
    {
        if (!empty($this->files_to_remove)) {
            foreach ($this->files_to_remove as $key => $file) {
                unlink($file);
            }
            $this->files_to_remove = array();
        }

        file_put_contents($this->configuration_file, json_encode($this->configuration, JSON_PRETTY_PRINT));
        return true;
    }

    /**
     * Return the given item from the history
     * @author Lionel Vinceslas <lvinceslas@nuxly.com>
     * @version 2020-01-06.1
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        if (array_key_exists($id, $this->configuration)) {
            return $this->configuration[$id];
        } else {
            return null;
        }
    }

    /**
     * Add the given item to the history
     * @author Lionel Vinceslas <lvinceslas@nuxly.com>
     * @version 2020-01-06.1
     * @param string $id                FILE ID
     * @param string $date              YYYY-MM-DD
     * @param string $time              00:00:00
     * @param string $name              FILE NAME
     * @param string $file              FILE URL
     * @param string $foreignId         FILE URL
     * @param string $unconformities    ARRAY OF UNCONFORMITIES
     * @return bool
     */
    public function add(string $id, string $date, string $time, string $name, string $file, string $foreignId = null, array $unconformities = array())
    {
        if (array_key_exists($id, $this->configuration)) {
            return false;
        } else {
            $this->configuration[$id]['date'] = $date;
            $this->configuration[$id]['time'] = $time;
            $this->configuration[$id]['name'] = $name;
            $this->configuration[$id]['file'] = $file;
            $this->configuration[$id]['foreignId'] = $foreignId;
            $this->configuration[$id]['unconformities'] = $unconformities;
            
            return true;
        }
    }

    /**
     * Remove the given item from the history
     * @author Lionel Vinceslas <lvinceslas@nuxly.com>
     * @version 2019-12-16.1
     * @param string $id        FILE ID
     * @param bool $deleteFile  TRUE | FALSE
     * @return bool
     */
    public function remove(string $id, bool $deleteFile = true)
    {
        if (array_key_exists($id, $this->configuration)) {
            if ($deleteFile) {
                array_push($this->files_to_remove, $this->configuration[$id]['file']);
            }

            unset($this->configuration[$id]);
            return true;
        } else {
            return false;
        }
    }
}