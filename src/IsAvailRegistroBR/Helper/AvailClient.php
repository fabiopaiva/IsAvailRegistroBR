<?php

/**
 * AvailClient
 * Adaptado do Registro BR
 * @link ftp://ftp.registro.br/pub/isavail/isavail-0.5.tar.gz
 * @file AvailClient.php
 * @date 15/12/2014
 * @author Fábio Paiva <paiva.fabiofelipe@gmail.com>
 * @project sig-registro
 */

namespace IsAvailRegistroBR\Helper;
use IsAvailRegistroBR\Helper\AvailResponseParser;

class AvailClient {
    var $lang = 0;
    var $ip = '';
    var $cookie = '00000000000000000000';
    var $cookie_file = '/tmp/isavail-cookie.txt';
    var $version = 1;
    var $server = '200.160.2.3';
    var $port = 43;
    var $suggest = 1;
    
    const MAX_UDP_SIZE = 512;
    const MAX_RETRIES = 3;
    const RETRY_TIMEOUT = 5;
    
    public function __construct() {
        $this->cookie_file = sys_get_temp_dir() . '/isavail-cookie.txt';
    }

    function setParam($arg) {
        $this->lang        = $arg["lang"];
        $this->ip          = $arg["ip"];
        $this->cookie_file = $arg["cookie_file"];
        $this->server      = $arg["server"];
        $this->port        = $arg["port"];
        $this->suggest     = $arg["suggest"];

        if (!file_exists($this->cookie_file) || !is_readable($this->cookie_file)) {
            # Send a query with an invalid cookie
            $this->send_query('registro.br');
        } else {
            $COOKIE = fopen($this->cookie_file, "r");
            $this->cookie = fread($COOKIE, filesize($this->cookie_file));
            fclose($COOKIE);
        }
    }
    
    function send_query($fqdn) {
        $query = '';
        if ($this->ip != '') {
            $query .= "[" . $this->ip . "] ";
        }
    
        # Create a random 10 digit query ID (2^32)
        $query_id = rand(1000000000, 4294967296);
    
        # Form the query
        $query .= $this->version . " " . $this->cookie . " " .
                  $this->lang . " " . $query_id . " " . trim($fqdn);
    
        if ($this->version > 0) {
            $query .= " " . $this->suggest;
        }

        # Create a new socket
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if (!(@socket_connect($sock, $this->server, $this->port))) {
            print "\nConnection Failed!\n";
            exit(1);
        }

        # Send the query and wait for a response
        $timeout = 0;
        $retries = 0;
        $resend = true;
    
        # Response parser
        $parser = new AvailResponseParser();
        while (42) {
            # Check the need to (re)send the query
            if ($resend == true) {
                $resend = false;
                $retries++;
                if ($retries > self::MAX_RETRIES) {
                    break;
                }
               
                # Send the query
                socket_write($sock, $query, strlen($query));
            }
           
            # Set the timeout
            $timeout += self::RETRY_TIMEOUT;
            socket_set_option($sock, 
                          SOL_SOCKET,  // socket level
                          SO_RCVTIMEO, // timeout option
                          array(
                                "sec"  => $timeout, // Timeout in seconds
                                "usec" => 0  // I assume timeout in microseconds
                               ) );

            $response = @socket_read($sock, self::MAX_UDP_SIZE);
            if (empty($response)) {
                $resend = true;
                continue;
            }
    
            # Response received. Call the parser
            $parser->parse_response($response);
    
            # Check the query ID
            if (($parser->query_id != $query_id) &&
                ($parser->status != 8)) {
                # Wrong query ID. Just wait for another response
                $resend = false;
                continue;
            }            
            
            # Check if the cookie was invalid
            if ($parser->cookie != "") {
                # Save the new cookie
                $cookie = $this->cookie;
                $this->cookie = $parser->cookie;
    
                if ($COOKIE = fopen($this->cookie_file, "w")) {
                    fwrite($COOKIE, $this->cookie);
                    fclose($COOKIE);
                }
    
                if ($cookie == DEFAULT_COOKIE) {
                    # Nothing else to do
                    break;
                } else {
                    # Resend query. Now we should have the right cookie
                    $parser = $this->send_query($fqdn);
                    break;
                }
    
            }
            break;
        }        
        
        # Return the filled ResponseParser object
        return $parser;
    }
    
    public function getLang() {
        return $this->lang;
    }

    public function getIp() {
        return $this->ip;
    }

    public function getCookie() {
        return $this->cookie;
    }

    public function getCookie_file() {
        return $this->cookie_file;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getServer() {
        return $this->server;
    }

    public function getPort() {
        return $this->port;
    }

    public function getSuggest() {
        return $this->suggest;
    }

    public function setLang($lang) {
        $this->lang = $lang;
        return $this;
    }

    public function setIp($ip) {
        $this->ip = $ip;
        return $this;
    }

    public function setCookie($cookie) {
        $this->cookie = $cookie;
        return $this;
    }

    public function setCookie_file($cookie_file) {
        $this->cookie_file = $cookie_file;
        return $this;
    }

    public function setVersion($version) {
        $this->version = $version;
        return $this;
    }

    public function setServer($server) {
        $this->server = $server;
        return $this;
    }

    public function setPort($port) {
        $this->port = $port;
        return $this;
    }

    public function setSuggest($suggest) {
        $this->suggest = $suggest;
        return $this;
    }


}
