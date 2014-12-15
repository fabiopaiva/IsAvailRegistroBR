<?php

/**
 * AvailResponseParser
 * Adaptado do Registro BR
 * @link ftp://ftp.registro.br/pub/isavail/isavail-0.5.tar.gz
 * @file AvailResponseParser.php
 * @date 15/12/2014
 * @author Fábio Paiva <paiva.fabiofelipe@gmail.com>
 * @project registro
 */

namespace IsAvailRegistroBR\Helper;

class AvailResponseParser {

    var $status = -1;
    var $query_id = '';
    var $fqdn = '';
    var $fqdn_ace = '';
    var $expiration_date = '';
    var $publication_status = '';
    var $nameservers = '';
    var $tickets = '';
    var $release_process_dates = array();
    var $msg = '';
    var $cookie = '';
    var $response = '';
    var $suggestions = array();

    function __toString() {
        return $this->str();
    }

    function str() {
        $message = '';
        $message .= "ID da pesquisa: $this->query_id\n";
        $message .= "Domínio: $this->fqdn\n";
        $message .= "Status: <!--$this->status--> (";

        if ($this->status == 0) {
            $message .= "Disponível)\n";
        } else if ($this->status == 1) {
            $message .= "Disponível com tickets ativos)\n";
            $message .= "Tickets: \n";
            $message .= "  " . $this->tickets . "\n";
        } else if ($this->status == 2) {
            $message .= "Registrado)\n";
            $message .= 'Data de expiração: ';
            if ($this->expiration_date == '0') {
                $message .= "Isento de pagamento\n";
            } else {
                $message .= $this->expiration_date . "\n";
            }

            $message .= "Status de publicação: " . $this->publication_status . "\n";
            $message .= "Nameservers: \n";
            $message .= $this->nameservers;

            if (sizeof($this->suggestions) > 0) {
                $message .= "Sugestões:";
                foreach ($this->suggestions as $suggestion) {
                    $message .= " " . $suggestion;
                }
                $message .= "\n";
            }
        } else if ($this->status == 3) {
            $message .= "Indisponível)\n";
            $message .= "Informação adicional: " . $this->msg . "\n";

            if (sizeof($this->suggestions) > 0) {
                $message .= "Sugestões:";
                foreach ($this->suggestions as $suggestion) {
                    $message .= " " . $suggestion;
                }
                $message .= "\n";
            }
        } else if ($this->status == 4) {
            $message .= "Consulta inválida)\n";
            $message .= "Informação adicional: " . $this->msg . "\n";
        } else if ($this->status == 5) {
            $message .= "Aguardando processo de liberação)\n";
        } else if ($this->status == 6) {
            $message .= "Processo de liberação em progresso)\n";
            $message .= "Processo de liberação:\n";
            $message .= "  Data de início: " . $this->release_process_dates[0] . "\n";
            $message .= "  Dado do fim:   " . $this->release_process_dates[1] . "\n";
        } else if ($this->status == 7) {
            $message .= "Processo de liberação em progresso com tickets ativos)\n";
            $message .= "Processo de liberação:\n";
            $message .= "  Data de início: " . $this->release_process_dates[0] . "\n";
            $message .= "  Data do fim:   " . $this->release_process_dates[1] . "\n";
            $message .= "Tickets: \n";
            $message .= $this->tickets;
        } else if ($this->status == 8) {
            $message .= "Erro)\n";
            $message .= "Informação adicional: " . $this->msg . "\n";
        } else if ($this->response != '') {
            $message = $this->response;
        } else {
            $message = 'Sem resposta';
        }

        return $message;
    }

    # Parse a string response

    function parse_response($response) {
        $this->response = $response;
        $buffer = split("\n", $this->response);

        while (42) {
            if (count($buffer) == 0) {
                break;
            }
            $line = trim(array_shift($buffer));

            # Ignore blank lines at the beginning
            if (strlen($line) == 0) {
                continue;
            }

            # Ignore comments
            if (substr($line, 0, 1) == "%") {
                continue;
            }

            # Get the status of the response, or cookie
            if ((substr($line, 0, 3) == "CK ") ||
                    (substr($line, 0, 3) == "ST ")) {
                $items = split(" ", $line);

                # New cookie
                if ($items[0] == "CK") {
                    $this->cookie = substr($items[1], 0, 20);
                    $this->query_id = $items[2];
                    return 0;
                }

                if (count('items') == 0) {
                    return -1;
                }

                # Get the response status
                $this->status = $items[1];

                # Status 8: Error
                if ($this->status == 8) {
                    $this->msg = trim(array_shift($buffer));
                    return 0;
                }

                $this->query_id = $items[2];
            }

            # Get the fqdn and fqdn_ace
            $line = trim(array_shift($buffer));
            $words = split('\|', $line);
            $this->fqdn = $words[0];
            if (count($words) > 1) {
                $this->fqdn_ace = $words[1];
            }

            # Domain available or waiting release process
            if (($this->status == 0) || ($this->status == 5)) {
                return 0;
            }

            # Read a new line from the buffer
            $line = trim(array_shift($buffer));

            # Domain available with ticket: Get the list of active tickets
            if ($this->status == 1) {
                $tickets = split('\|', $line);
                foreach ($tickets as $t) {
                    $this->tickets .= " $t\n";
                }
                return 0;

                # Domain already registered
            } else if ($this->status == 2) {
                $words = split('\|', $line);
                if (count($words) < 2) {
                    return -1;
                }

                $this->expiration_date = $words[0];
                $this->publication_status = $words[1];
                for ($i = 2; $i < count($words); $i++) {
                    $this->nameservers .= "  " . $words[$i] . "\n";
                }

                # Check if there's any suggestion
                $line = trim(array_shift($buffer));
                if ($line == "") {
                    return 0;
                }

                $this->suggestions = split('\|', $line);
                for ($i = 0; $i < sizeof($this->suggestions); $i++) {
                    $this->suggestions[$i] = $this->suggestions[$i] . ".br";
                }

                return 0;

                # Domain unavailable or invalid or release process
            } else if ($this->status == 3 || $this->status == 4) {
                # Just get the message
                $this->msg = $line;

                if ($this->status == 3) {
                    # Check if there's any suggestion
                    $line = trim(array_shift($buffer));
                    if ($line == "") {
                        return 0;
                    }

                    $this->suggestions = split('\|', $line);
                    for ($i = 0; $i < sizeof($this->suggestions); $i++) {
                        $this->suggestions[$i] = $this->suggestions[$i] . ".br";
                    }
                }

                return 0;

                # Release process
            } else if ($this->status == 6 || $this->status == 7) {
                # Get the release process dates
                $this->release_process_dates = split('\|', $line);
                if (count($this->release_process_dates) < 2) {
                    return -1;
                }

                # Get the tickets (status 7)
                if ($this->status == 7) {
                    $line = trim(array_shift($buffer));
                    $tickets = split('\|', $line);
                    foreach ($tickets as $t) {
                        $this->tickets .= "  " . $t . "\n";
                    }
                }
                return 0;
            }

            # Error
            return -1;
        }
    }

}
