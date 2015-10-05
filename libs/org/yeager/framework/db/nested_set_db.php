<?php

/// @cond DEV

/**
 * @name        classDB
 * @deprecated  Datenbank anbindung
 *
 * @since       18.10.2003
 * @version     0.5
 *
 * @link        www.thundernail.de
 * @author      Martin Rosekeit <martin.rosekeit@thundernail.de>
 * @copyright   (c) 2003 Thundernail
 * @GNU         This library is free software; you can redistribute it and/or
 *              modify it under the terms of the GNU Lesser General Public
 *              License as published by the Free Software Foundation; either
 *              version 2.1 of the License, or (at your option) any later
 *              version.
 *              This library is distributed in the hope that it will be
 *              useful, but WITHOUT ANY WARRANTY; without even the implied
 *              warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *              PURPOSE. See the GNU Lesser General Public License for more
 *              details.
 *              You should have received a copy of the GNU Lesser General
 *              Public License along with this library; if not, write to the
 *              Free Software Foundation, Inc., 59 Temple Place, Suite 330,
 *              Boston, MA 02111-1307 USA
 **/

class NestedSetDb {
/**
 * @access      public
 **/
    /**
     * @param       int     ID          ID der MySQL-Verbindung
     * @param       string  adminmail   Mailadresses des Admins fuer Fehlermeldung
     * @param       bool    show_error  Fehlermeldungen anzeigen
     * @return      void
     * @deprecated  Konstruktor
     **/
    function NestedSetDb ($ID, $tablename, $show_error=TRUE) {
        $this->linkId    = $ID;
        $this->showError = true;
        $this->adminmail = $adminmail;
		$this->db = &sYDB();
		$this->tablename = $tablename;
	}


    /**
     * @param       string  query_string    MySQL-Abfragestring
     * @return      int     ID des Abfrageergebnisses
     * @deprecated  Sendet eine Abfrage an die MySQL-DB und gibt die ID der Antwort zurueck
     **/
    function query($query_string) {
		$rs= $this->db->Execute($query_string);
		if(!$rs) {
           $this->error("Invalid SQL: ".$query_string);
		} else {
			$this->query_id = $rs;
			return $rs;
		}
    }


    /**
     * @param       string  query_string    MySQL-Abfragestring
     * @return      int     ID des Abfrageergebnisses
     * @deprecated  Sendet eine Abfrage an die MySQL-DB und gibt die 1. Ergebniszeile zurueck
     *              Kein Parameter uebergeben => Bearbeitung des zuletzt bearbeiteten Ergebnisses
     **/
    function queryFirst($query_string) {
        $this->query($query_string);
        $returnarray=$this->query_id->GetArray();
        $returnarray = $returnarray[0];
        return $returnarray;
    }


    /**
     * @param       string  query_string    MySQL-Abfragestring
     * @return      array   2D-Feld des Abfrageergebnisses
     * @error       bool    FALSE   wenn keine Daten im Feld
     * @deprecated  Sendet eine Abfrage an die MySQL-DB und gibt das Ergebnis zurueck
     **/
    function queryArray($query_string) {
        $this->queryId = $this->query($query_string);
        unset($this->record);
        while ($result = $this->query_id->GetArray()){
            $this->record[] = $result;
        }
        if(isset($this->record))  return $this->record[0];
        else                      return FALSE;
    }


    /**
     * @param       void
     * @return      int     ID des letzte eingetrages
     * @error       int     -1
     * @deprecated  Sendet eine Abfrage an die MySQL-DB und gibt das Ergebnis zurueck
     **/
    function insertId() {
		if($this->db->Insert_ID()) return $this->db->Insert_ID(); else return -1;
    }


/**
 * @access      privat
 **/

    /**
     * @var         int
     **/
    var $linkId = 0;    // @deprecated ID der MySQL-Verbindung
    var $queryId = 0;   // @deprecated ID des Abfrageergebnisses
    /**
     * @var         array
     **/
    var $record = array();  // @deprecated aus einer Abfrage erzeugtes Array
    /**
     * @var         string
     **/
    var $adminmail = "";    // @deprecated Mailadresse des Admins
    /**
     * @var         bool    TRUE
     **/
    var $showError = TRUE;  // @deprecated Sollen fehler als HTML-Seite sichtbar ausgegeben werden


    /**
     * @param       int     query_id    ID des Abfrageergebnisses
     * @return      array   Daten des Abfrageergebnisses
     * @deprecated  Gibt die erste Zeile des Abfrageergebnisses zur?ck
     *              L?scht diese Zeile aus dem Ergebnis
     **/
    function fetchArray($queryId=-1) {
        if ($queryId!=-1) {
            $this->queryId=$queryId;
        }
        $this->record = $this->query_id->GetArray($this->query_id);
        return $this->record;
    }
    /**
     * @param       int     query_id    ID des Abfrageergebnisses
     * @return      bool    erfolgreich?
     * @deprecated  Loescht das Abfrageergebnis aus dem Speicher
     *              Kein Parameter uebergeben => Bearbeitung des zuletzt bearbeiteten Ergebnisses
     **/
    function freeResult($query_id=-1) {
        if ($query_id!=-1) {
            $this->query_id=$query_id;
        }
    }


    /**
     * @param       string  Hinweis zum Fehler
     * @deprecated  Gibt eine Fehlermeldung aus
     *              Beendet danach das Script
     **/
    function error($msg="") {
        $message ="Error in MySQL-DB: $msg\n<br>";
        $message.="error: ".mysql_error()."\n<br>";
        $message.="error number: ".mysql_errno()."\n<br>";
        $message.="Date: ".date("d.m.Y @ H:i")."\n<br>";
        $message.="Script: ".getenv("REQUEST_URI")."\n<br>";
        $message.="Referer: ".getenv("HTTP_REFERER")."\n<br><br>";
        $message.="Admin: <a href=\"mailto:$this->adminmail\">$this->adminmail</a>\n<br><br>";

        if($this->showError)
            die($message);
        exit();
    }
}

/// @endcond

?>