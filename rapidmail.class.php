<?php

    /**
     * rapidmail api client
     *
     * Copyright 2008-2015 by rapidmail GmbH
     *
     * This library is free software; you can redistribute it and/or
     * modify it under the terms of the GNU Lesser General Public
     * License as published by the Free Software Foundation; either
     * version 2.1 of the License, or (at your option) any later version.
     *
     * This library is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
     * Lesser General Public License for more details.
     *
     * You should have received a copy of the GNU Lesser General Public
     * License along with this library; if not, write to the Free Software
     * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
     */


    /**
     * rapidmail api client
     *
     * PHP client class for rapidmail api
     *
     * @author rapidmail GmbH
     * @version 1.7
     * @license LGPL (http://www.gnu.org/licenses/lgpl.html)
     */
    class rapidmail {

        const VERSION = '1.7';

        /**
         * Node id
         *
         * @var integer
         */
        private $node_id = NULL;

        /**
         * Recipient list id
         *
         * @var integer
         */
        private $recipientlist_id = NULL;

        /**
         * Api key for authentication (API-Schlüssel)
         *
         * @var string
         */
        private $apikey = '';

        /**
         * Use secure connection (SSL)
         *
         * @var boolean
         */
        private $use_ssl = true;

        /**
         * Use debug mode
         *
         * @var boolean
         */
        private $debug_mode = false;

        /**
         * Response type ok
         *
         * @var const
         */
        const RESPONSE_TYPE_OK = 'ok';

        /**
         * Response type error
         *
         * @var const
         */
        const RESPONSE_TYPE_ERROR = 'error';

        /**
         * Transfer method get
         *
         * @var const
         */
        const TRANSFER_METHOD_GET = 'GET';

        /**
         * Transfer method post
         *
         * @var const
         */
        const TRANSFER_METHOD_POST = 'POST';

        /**
         * Recipient status active
         *
         * @var const
         */
        const RECIPIENT_STATUS_ACTIVE = 'active';

        /**
         * Recipient status bounced
         *
         * @var const
         */
        const RECIPIENT_STATUS_BOUNCED = 'bounced';

        /**
         * Recipient status deleted
         *
         * @var const
         */
        const RECIPIENT_STATUS_DELETED = 'deleted';

        /**
         * Recipient status new
         *
         * @var const
         */
        const RECIPIENT_STATUS_NEW = 'new';

        /**
         * Constructor
         *
         * @see http://my.rapidmail.de/api/documentation.html?area=library_php5
         * @param integer $node_id Node id
         * @param integer $recipientlist_id Recipient list id
         * @param string $apikey Api key for authentication (API-Schlüssel)
         * @return void
         */
        public function __construct($node_id, $recipientlist_id, $apikey, $use_ssl = true, $debug_mode = false) {

            rapidmailutils::check_int($node_id, 'node_id');
            rapidmailutils::check_int($recipientlist_id, 'recipientlist_id');
            rapidmailutils::check_string($apikey, 'apikey');
            rapidmailutils::check_bool($use_ssl, 'use_ssl');
            rapidmailutils::check_bool($debug_mode, 'debug_mode');

            $this->node_id = $node_id;
            $this->recipientlist_id = $recipientlist_id;
            $this->apikey = $apikey;
            $this->use_ssl = $use_ssl;
            $this->debug_mode = $debug_mode;

        }

        /**
         * Get one recipient from current recipient list
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipient_get
         * @param string $email Email
         * @return array
         */
        public function get_recipient($email) {

            rapidmailutils::check_string($email, 'email');

            return $this->api_call('recipient_get', array('email' => $email));

        }

        /**
         * Get all recipients from current recipient list
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipient_get_multi
         * @param string $status Status
         * @return array
         */
        public function get_recipients($status = self::RECIPIENT_STATUS_ACTIVE, $fields = array()) {

            rapidmailutils::check_string($status, 'status');
            rapidmailutils::check_array($fields, 'fields', true);

            return $this->api_call('recipient_get_multi', array('status' => $status, 'fields' => $fields));

        }

        /**
         * Add recipients to current recipient list from csv file
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipient_new_multi
         * @param string $file_path Path to csv file
         * @param string $enclosure Csv enclosure, for example "
         * @param string $delimiter Csv delimiter, for example ;
         * @param string $recipient_exist What to do if recipient exists
         * @param string $recipient_missing What to do if recipient is missing
         * @param string $recipient_deleted What to do if recipient is deleted
         * @return array
         */
        public function add_recipients($file_path, $enclosure = '"', $delimiter = ';', $recipient_exist = 'rapidmail', $recipient_missing = '', $recipient_deleted = '') {

            rapidmailutils::check_string($file_path, 'file_path');
            rapidmailutils::check_string($enclosure, 'enclosure');
            rapidmailutils::check_string($delimiter, 'delimiter');
            rapidmailutils::check_string($recipient_exist, 'recipient_exist', false, array('rapidmail', 'importfile'));
            rapidmailutils::check_string($recipient_missing, 'recipient_missing', true, array('delete', ''));
            rapidmailutils::check_string($recipient_deleted, 'recipient_deleted', true, array('import', ''));

            $parameters = array(
                'file' => '@FILE@' . $file_path,
                'enclosure' => $enclosure,
                'delimiter' => $delimiter,
                'recipient_exist' => $recipient_exist,
                'recipient_missing' => $recipient_missing,
                'recipient_deleted' => $recipient_deleted
            );

            return $this->api_call('recipient_new_multi', $parameters, self::TRANSFER_METHOD_POST);

        }

        /**
         * Add recipient to current recipient list
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipient_new
         * @param array $recipient_data Recipient data, see documentation for details
         * @return array
         */
        public function add_recipient($email, $recipient_data = array()) {

            rapidmailutils::check_string($email, 'email');
            rapidmailutils::check_array($recipient_data, 'recipient_data', true);

            $recipient_data['email'] = $email;

            if (empty($recipient_data['status']) || $recipient_data['status'] == 'active') {
                $recipient_data['status'] = 'active';
                $recipient_data['activationmail'] = 'no';
            }

            return $this->api_call('recipient_new', $recipient_data);

        }

        /**
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipient_edit
         * @param string $email E-Mail-Address of recipient to edit
         * @param array $recipient_data Recipient data, see documentation for details
         * @return array
         */
        public function edit_recipient($email, $recipient_data) {

            rapidmailutils::check_string($email, 'email');
            rapidmailutils::check_array($recipient_data, 'recipient_data');

            $recipient_data['email'] = $email;

            return $this->api_call('recipient_edit', $recipient_data);

        }

        /**
         * Delete recipient
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipient_delete
         * @param string $email E-Mail-Address of recipient to delete
         * @param boolean $send_goodbye
         * @return array
         */
        public function delete_recipient($email, $send_goodbye = 'no', $track_stats = 'no') {

            rapidmailutils::check_string($email, 'email');
            rapidmailutils::check_string($send_goodbye, 'send_goodbye', false, array('yes', 'no'));
            rapidmailutils::check_string($track_stats, 'track_stats', false, array('yes', 'no'));

            $parameters = array(
                'email' => $email,
                'sendgoodbye' => $send_goodbye,
                'track_stats' => $track_stats
            );

            return $this->api_call('recipient_delete', $parameters);

        }

        /**
         * Delete recipients
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipient_delete_multi
         * @return array
         */
        public function delete_recipients() {
            return $this->api_call('recipient_delete_multi', array());
        }

        /**
         * Send new mailing to current recipient list
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=mailing_new
         * @param string $sender_name Sender name
         * @param string $sender_email Sender email
         * @param string $subject Subject
         * @param string $send_at Send at (ISO datetime, yyyy-mm-dd hh:mm)
         * @param string $zip_file Path to zipfile containing email
         * @param string $charset Charset
         * @param string $draft Add mailing as draft (yes/no)
         * @return array
         */
        public function add_mailing($sender_name, $sender_email, $subject, $send_at = NULL, $zip_file, $charset = NULL, $draft = 'no') {

            rapidmailutils::check_string($sender_name, 'sender_name');
            rapidmailutils::check_string($sender_email, 'sender_email');
            rapidmailutils::check_string($subject, 'subject');

            if ($send_at !== NULL) {
                rapidmailutils::check_string($send_at, 'send_at');
            }

            rapidmailutils::check_string($zip_file, 'zip_file');

            if ($charset !== NULL) {
                rapidmailutils::check_string($charset, 'charset');
            }

            rapidmailutils::check_string($draft, 'draft', false, array('yes', 'no'));

            $parameters = array(
                'sender_name' => $sender_name,
                'sender_email' => $sender_email,
                'subject' => $subject,
                'send_at' => $send_at,
                'file' => '@FILE@' . $zip_file,
                'charset' => $charset,
                'draft' => $draft
            );

            $data = $this->api_call('mailing_new', $parameters, self::TRANSFER_METHOD_POST);

            return $data['api_data']['mailing_id'];

        }

        /**
         * Return statistics to one mailing
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=statistics_mailing_get
         * @param integer $mailing_id Mailing id
         * @param integer $publiclink_validity Validity in days of public link
         * @return array
         */
        public function get_mailing_statistics($mailing_id, $publiclink_validity = 3) {

            rapidmailutils::check_int($mailing_id, 'mailing_id');
            rapidmailutils::check_int($publiclink_validity, 'publiclink_validity', false, 1, 30);

            $parameters = array(
                'mailing_id' => $mailing_id,
                'publiclink_validity' => $publiclink_validity
            );

            return $this->api_call('statistics_mailing_get', $parameters);

        }

        /**
         * Returns mailings
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=mailings_get
         * @return array
         */
        public function get_mailings() {
            return $this->api_call('mailings_get', array());
        }

        /**
         * Returns recipientlist informations
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=recipientlist_get
         * @param integer $recipientlist_id Recipientlist id
         * @return array
         */
        public function get_metadata() {
            return $this->api_call('metadata_get', array());
        }

        /**
         * Changes recipientlist informations
         *
         * @see http://my.rapidmail.de/api/documentation.html?module=metadata_set
         * @param array $data Recipientlists data, "name", "description"
         * @return array
         */
        public function set_metadata($data) {
            return $this->api_call('metadata_set', $data, self::TRANSFER_METHOD_POST);
        }

        /**
         * Api call, used by all methods
         *
         * @param string $module Module
         * @param string $parameters Parameters
         * @param string $method Method
         * @return array
         */
        private function api_call($module, $parameters, $method = self::TRANSFER_METHOD_GET) {

            rapidmailutils::check_string($module, 'module');
            rapidmailutils::check_array($parameters, 'parameters', true);
            rapidmailutils::check_string($method, 'method', false, array(self::TRANSFER_METHOD_GET, self::TRANSFER_METHOD_POST));

            $host = 'http' . ($this->use_ssl ? 's' : '') . '://api.rapidmail.de';
            $url = '/rest/' . $this->apikey . '/' . $this->node_id . '/' . $module . '/?recipientlist_id=' . $this->recipientlist_id . '&version=' . self::VERSION;

            $data = '';

            if ($method == self::TRANSFER_METHOD_GET) {

                foreach ($parameters AS $k => $v) {

                    if (is_array($v)) {

                        if (count($v) > 0) {

                            foreach ($v AS $v_sub) {
                                $url .= '&' . $k . '[]=' . urlencode($v_sub);
                            }

                        }

                    } else {
                        $url .= '&' . $k . '=' . urlencode($v);
                    }

                }

                $header = 'GET ' . $url . ' HTTP/1.0' . "\r\n" .
                          'Host: api.rapidmail.de' . "\r\n\r\n";

            } else {

                $header = 'POST ' . $url . ' HTTP/1.0' . "\r\n" .
                          'Host: api.rapidmail.de' . "\r\n";

                $boundary = md5(microtime() + (rand(0, 1) * 100));

                $data = '';

                foreach ($parameters AS $k => $v) {

                    $data .= '--' . $boundary . "\r\n";

                    if (substr($v, 0, 6) == '@FILE@') {

                        $path_name = substr($v, 6);

                        if (!is_file($path_name)) {
                            throw new rapidmail_io_exception('File "' . $path_name . '" not found');
                        }

                        $filename = basename($path_name);

                        $data .= 'Content-Disposition: form-data; name="' . $k . '"; filename="' . $filename . '"' . "\r\n";
                        $data .= 'Content-Type: application/octet-stream' . "\r\n";
                        $data .= 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
                        $data .= file_get_contents($path_name) . "\r\n";

                    } else {

                        if (is_array($v)) {

                            if (count($v) > 0) {

                                foreach ($v AS $v_sub) {

                                    $data .= 'Content-Disposition: form-data; name="' . $k . '[]"' . "\r\n\r\n";
                                    $data .= $v_sub . "\n";

                                }

                            }

                        } else {

                            $data .= 'Content-Disposition: form-data; name="' . $k . '"' . "\r\n\r\n";
                            $data .= $v . "\n";

                        }

                    }

                }

                $data .= '--' . $boundary . '--' . "\r\n";

                $header .= 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n" .
                           'Content-Length: ' . strlen($data) . "\r\n\r\n";

            }

			if ($this->debug_mode) {

				echo "*** DEBUG MODE ACTIVE ***\n";
				echo 'Node ID: ' . $this->node_id . "\n";
				echo 'Recipientlist ID: ' . $this->recipientlist_id . "\n";
				echo 'API Key: ' . $this->apikey. "\n";
				echo 'Host: ' . $host . "\n";
				echo 'URL: ' . $url . "\n";

				if ($this->use_ssl) {
					echo "Socket: ssl://api.rapidmail.de:443\n";
				} else {
					echo "WARNING: SSL mode disabled. Your data will be transfered UNSECURE\n";
					echo "Socket: tcp://api.rapidmail.de:80\n";
				}

				if ($method == self::TRANSFER_METHOD_POST) {

					echo "*** POST DATA ***\n";

					foreach ($parameters AS $k => $v) {
                        $k . ' => ' . $v . "\n";
					}

				}

			}

			if ($this->use_ssl) {
				$fh = fsockopen('ssl://api.rapidmail.de', 443, $errno, $errstr);
			} else {
	            $fh = fsockopen('tcp://api.rapidmail.de', 80, $errno, $errstr);
   			}

            if (!$fh) {
                throw new rapidmail_io_exception('Error while connecting to api.rapidmail.de (' . $errno . ', ' . $errstr . ')');
            }

            $res = fwrite($fh, $header . $data);

            if (!$res) {
                throw new rapidmail_io_exception('Error writing on stream');
            }

            $xml = '';

            while (!feof($fh)) {
                $xml .= fgets($fh, 1024);
            }

            $xml = trim(substr($xml, strpos($xml, '<rsp')));

            if ($xml == '') {
                throw new rapidmail_io_exception('No response received');
            }

            $result = $this->build_result($xml);

            if ($result['@attributes']['status'] != self::RESPONSE_TYPE_OK) {
                throw new rapidmail_response_exception('(' . $result['@attributes']['status_code'] . ') ' . $result['@attributes']['status_description']);
            }

            return $result;

        }

        /**
         * Build array from xml
         *
         * @param string $xml Xml
         * @return array
         */
        private function build_result($xml) {

            rapidmailutils::check_string($xml, 'xml');

            $xml = @simplexml_load_string($xml, NULL, LIBXML_NOCDATA);

            if (!$xml) {
                throw new rapidmail_io_exception('Error while parsing XML response');
            }

            return self::xml_to_array($xml);

        }

        /**
         * Xml to array
         *
         * @param object $xml Simple xml object
         * @return array
         */
        protected static function xml_to_array($xml) {

            $index = array();

            if ($xml instanceof SimpleXMLElement) {
                $xml = (array)$xml;
            }

            foreach ($xml AS $element => $value) {

                if (is_array($value) || is_object($value)) {

                    $vars = (array)$value;

                    if (count($vars) == 0) {
                        $index[$element] = NULL;
                    } else {
                        $index[$element] = self::xml_to_array($value);
                    }

                } else {
                    $index[$element] = (string)$value;
                }

            }

            return $index;

        }

    }

    /**
     * Base exception
     */
    class rapidmail_base_exception extends Exception {
    }

    /**
     * Exception for wrong parameter
     */
    class rapidmail_parameter_exception extends rapidmail_base_exception {
    }

    /**
     * IO exception
     */
    class rapidmail_io_exception extends rapidmail_base_exception {
    }

    /**
     * Response exception
     */
    class rapidmail_response_exception extends rapidmail_base_exception {
    }

    /**
     * rapidmailutils
     *
     * Helper
     *
     * @author rapidmail GmbH
     * @version 1.0
     * @license LGPL (http://www.gnu.org/licenses/lgpl.html)
     */
    class rapidmailutils {

        /**
         * Checks if a variable is an integer
         *
         * @param integer $value Variable
         * @param string $name Name of variable, for error output
         * @param boolean $zero_allowed True: 0 is allowed, false: 0 is not allowed
         * @param integer $min Minimum value allowed
         * @param integer $max Maximum value allowed
         * @return void
         */
        public static function check_int(&$value, $name, $zero_allowed = false, $min = NULL, $max = NULL) {

            if (!is_scalar($value) || preg_match('/[^0-9-]/i', $value)) {
                throw new rapidmail_parameter_exception('$' . $name . ' must be a integer [0-9-]* ($value = ' . $value . ')');
            }

            $value = (int)$value;

            if ($zero_allowed != true && $value == 0) {
                throw new rapidmail_parameter_exception('$' . $name . ' must be integer and is not allowed to be zero ($value = ' . $value . ')');
            }

            if ($min !== NULL && $value < $min) {
                throw new rapidmail_parameter_exception('$' . $name . ' is below the allowed minimum of ' . $min);
            }

            if ($max !== NULL && $value > $max) {
                throw new rapidmail_parameter_exception('$' . $name . ' is above the allowed maximum of ' . $max);
            }

        }

        /**
         * Checks if a variable is a string
         *
         * @param string $value Variable
         * @param string $name Name of variable, for error output
         * @param boolean $empty_allowed True: string can be empty (""), false: must not be empty
         * @param array $allowed_values Array with allowed values
         * @param array $disallowed_values Array with disallowed values
         * @return void
         */
        public static function check_string($value, $name, $empty_allowed = false, $allowed_values = NULL, $disallowed_values = NULL) {

            if (!is_string($value)) {
                throw new rapidmail_parameter_exception('$' . $name . ' must be a string');
            }

            if ($empty_allowed != true && $value == '') {
                throw new rapidmail_parameter_exception('$' . $name . ' must not be empty');
            }

            if ($allowed_values !== NULL && is_array($allowed_values) && in_array($value, $allowed_values) == false) {
                throw new rapidmail_parameter_exception('$' . $name . ' value is "' . $value .'" which is not among the allowed values (' . implode(',', $allowed_values)  . ')');
            }

            if ($disallowed_values !== NULL && is_array($disallowed_values) && in_array($value, $disallowed_values) == true) {
                throw new rapidmail_parameter_exception('$' . $name . ' value is "' . $value .'" which is among the disallowed values (' . implode(',', $disallowed_values)  . ')');
            }

        }

        /**
         * Checks if a variable is a array
         *
         * @param array $value Variable
         * @param string $name Name of variable, for error output
         * @param boolean $empty_allowed True: array can be empty, false: must not be empty
         * @return void
         */
        public static function check_array($value, $name, $empty_allowed = false) {

            if (!is_array($value)) {
                throw new rapidmail_parameter_exception('$' . $name . ' must be an array');
            }

            if ($empty_allowed != true && count($value) == 0) {
                throw new rapidmail_parameter_exception('$' . $name . ' must not be empty');
            }

        }


        /**
         * Checks if a variable is a boolean
         *
         * @param boolean $value Variable
         * @param string $name Name of variable, for error output
         * @return void
         */
        public static function check_bool($value, $name) {

            if (!is_bool($value)) {
                throw new rapidmail_parameter_exception('$' . $name . ' must be boolean');
            }

        }

    }

?>