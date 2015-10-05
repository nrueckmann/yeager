<?php

/// @cond DEV

/**
 * @name        classBaum
 * @deprecated  Mit Hilfe dieser Klasse koennen NestedSetBaeume erstellt werden!
 *              Dabei greift die Klasse nur auf zwei Felder in der Datenbank zu (lft & rgt)
 *              dadurch ist die Klasse unabhaengig von der Struktur der abgespeicherten Daten.
 *              M?gliche Aktionen sind:
 *                  - neues Element an einer bestimmten Stelle einfuegen
 *                  - Element loeschen
 *                  - Element verschieben
 * @class       NestedSet
 * @subpackage  NestedSetDb
 * @methods     NestedSet (str DB_table, str lft, str rgt, str moved, str id [, str adminmail [, int ID [, bool show_error]]])
 *              // Knoten einf?gen
 *                insertRoot()
 *                insertChild (int idVater)
 *                insertBrotherLft (int idKnoten)
 *                insertBrotherRgt (int idKnoten)
 *              // Knoten l?schen
 *                deleteOne (int idKnoten)
 *                deleteAll (int idKnoten)
 *              // Bewegen eines Knotens inc. seiner Kinder
 *                moveLft (int idKnoten)
 *                moveRgt (int idKnoten)
 *                moveUp (int idKnoten)
 *                moveDown (int idKnoten)
 *              // Fehlermeldungen
 *                getErrorNo ()
 *                getErrorStr ()
 *
 * @start       11.08.2003
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

\framework\import("org.yeager.framework.db.nested_set_db");

class NestedSet {
	/**
	 * @access      public
	 **/
	/**
	 * @param       string  DB_table    Name der Table in der DB
	 * @param       string  lft         Name des linken Tabellenfaldes
	 * @param       string  rgt         Name des rechten Tabellenfeldes
	 * @param       string  moved       Name des moved-Tabellenfeldes (wurde ein Element bewegt)
	 * @param       string  id          Name des id-Tabellenfeldes
	 * @param       string  admin_mail  Email-Adresse des Admins
	 * @param       int     ID          ID der MySQL-Verbindung
	 * @param       bool    show_error  Fehlermeldungen anzeigen
	 * @return      void
	 * @deprecated  Konstruktor
	 **/
	function NestedSet($DB_table, $lft, $rgt, $moved, $id, $adminmail = "", $ID = 0, $show_error = TRUE, &$db = NULL) {
		$this->DbTable = $DB_table;
		$this->lftFeld = $lft;
		$this->rgtFeld = $rgt;
		$this->movedFeld = $moved;
		$this->idFeld = $id;

		$this->db = new NestedSetDb($ID, $DB_table, $show_error, $db);
	}


	/**
	 * @return      int     id des Kindelements
	 * @error       -1
	 * @deprecated  F?gt den Root-Knoten ein
	 **/
	function insertRoot() {
		// Tabelle sperren
		$this->DbLocked();
		// Tabelle leer?
		$anzKnoten = $this->getAnzKnoten();
		if ($anzKnoten < 0) {
			$this->DbUnLocked();
			return -1;
		}
		if ($anzKnoten > 0) {
			$this->errorNo = 12;
			$this->errorStr = "Root-Koten existiert schon.";
			$this->DbUnLocked();
			return -1;
		}
		// Knoten einf?gen
		$this->db->query(" INSERT INTO $this->DbTable ($this->lftFeld, $this->rgtFeld)
                VALUES                     (0,              1);");
		$this->DbUnLocked();
		return $this->db->insertId();
	}


	/**
	 * @param       int     idVater     id des Vaterelements
	 * @return      int     id des Kindelements
	 * @error       -1
	 * @deprecated  F?gt ein Kindelement Rechts in Vater ein
	 **/
	function insertChild($idVater) {
		// Tabelle sperren
		$this->DbLocked();

		// Rechte Grenze vom Vater
		$rgtVater = $this->getRgt($idVater);
		if ($rgtVater < 0) {
			$this->DbUnLocked();
			return -1;
		}

		// Linke Grenzen verschieben
		$this->db->query(" UPDATE $this->DbTable
                SET $this->lftFeld = $this->lftFeld + 2
                WHERE $this->lftFeld >= $rgtVater;");
		// Rechte Grenze verschieben
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld + 2
                WHERE $this->rgtFeld >= $rgtVater;");
		$this->db->query(" INSERT INTO $this->DbTable ($this->lftFeld, $this->rgtFeld)
                VALUES                     ($rgtVater,      $rgtVater+1);");
		$mynewid = $this->db->insertId();
		$vaterlevel = $this->getLevel($idVater);
		$mynewlevel = $vaterlevel+1;
		$this->db->query(" UPDATE $this->DbTable
                SET PARENT = $idVater, LEVEL = $mynewlevel
                WHERE $this->idFeld = $mynewid;");

		$this->DbUnLocked();
		return $mynewid;

	}


	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      int     id des neues bruders
	 * @error       -1
	 * @deprecated  F?gt einen Bruder links neben den Knoten ein
	 **/
	function insertBrotherLft($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// Linke Grenze vom Knoten
		$lftKnoten = $this->getLft($idKnoten);
		if ($lftKnoten < 0) {
			$this->DbUnLocked();
			return -1;
		}
		// root-Knoten?
		$levelKnoten = $this->getLevel($idKnoten);
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return -1;
		}
		if ($levelKnoten < 2) {
			$this->errorNo = 8;
			$this->errorStr = "Root-Koten kann keinen Bruder haben.";
			$this->DbUnLocked();
			return -1;
		}
		// Linke Grenzen verschieben
		$this->db->query(" UPDATE $this->DbTable
                SET $this->lftFeld = $this->lftFeld + 2
                WHERE $this->lftFeld >= $lftKnoten;");
		// Rechte Grenze verschieben
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld + 2
                WHERE $this->rgtFeld >= $lftKnoten;");
		$this->db->query(" INSERT INTO $this->DbTable ($this->lftFeld, $this->rgtFeld)
                VALUES                     ($lftKnoten,     $lftKnoten+1);");
		$this->DbUnLocked();
		return $this->db->insertId();
	}


	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      int     id des neues bruders
	 * @error       -1
	 * @deprecated  F?gt einen Bruder rechts neben den Knoten ein
	 **/
	function insertBrotherRgt($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// Rechte Grenze vom Knoten
		$rgtKnoten = $this->getRgt($idKnoten);
		if ($rgtKnoten < 0) {
			$this->DbUnLocked();
			return -1;
		}
		$levelKnoten = $this->getLevel($idKnoten);
		// root-Knoten?
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return -1;
		}
		if ($levelKnoten < 2) {
			$this->errorNo = 8;
			$this->errorStr = "Root-Koten kann keinen Bruder haben.";
			$this->DbUnLocked();
			return -1;
		}
		// Linke Grenzen verschieben
		$this->db->query(" UPDATE $this->DbTable
                SET $this->lftFeld = $this->lftFeld + 2
                WHERE $this->lftFeld >= $rgtKnoten+1;");
		// Rechte Grenze verschieben
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld + 2
                WHERE $this->rgtFeld >= $rgtKnoten+1;");
		$this->db->query(" INSERT INTO $this->DbTable ($this->lftFeld, $this->rgtFeld)
                VALUES                     ($rgtKnoten+1,   $rgtKnoten+2);");
		$this->DbUnLocked();
		return $this->db->insertId();
	}


	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      bool    TRUE erfolgreich
	 * @error       FALSE
	 * @deprecated  L?scht einen Knoten, alle Kinder gelangen 1 Ebene nach oben
	 **/
	function deleteOne($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// Rechte Grenze vom Knoten
		$rgtKnoten = $this->getRgt($idKnoten);
		if ($rgtKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze vom Knoten
		$lftKnoten = $this->getLft($idKnoten);
		if ($lftKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// root-Knoten?
		$levelKnoten = $this->getLevel($idKnoten);
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return FALSE;
		}
		if ($levelKnoten < 1) {
			$this->errorNo = 9;
			$this->errorStr = "Root-Koten kann nicht genl?scht werden.";
			$this->DbUnLocked();
			return FALSE;
		}

		$this->db->query(" DELETE FROM $this->DbTable WHERE $this->idFeld=$idKnoten;");
		$this->db->query(" UPDATE $this->DbTable
                SET $this->lftFeld = $this->lftFeld - 1,
		$this->rgtFeld = $this->rgtFeld - 1
                WHERE $this->lftFeld BETWEEN $lftKnoten AND $rgtKnoten;");
		$this->db->query(" UPDATE $this->DbTable
                SET $this->lftFeld = $this->lftFeld - 2
                WHERE $this->lftFeld > $rgtKnoten;");
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld - 2
                WHERE $this->rgtFeld > $rgtKnoten;");
		$this->DbUnLocked();
		return TRUE;
	}


	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      bool    TRUE erfolgreich
	 * @error       FALSE
	 * @deprecated  L?scht einen Knoten und seine Kinder
	 **/
	function deleteAll($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// Rechte Grenze vom Knoten
		$rgtKnoten = $this->getRgt($idKnoten);
		if ($rgtKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze vom Knoten
		$lftKnoten = $this->getLft($idKnoten);
		if ($lftKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Raum unter dem Knoten bestimmen
		$diff = $rgtKnoten - $lftKnoten + 1;
		// root-Knoten?
		$levelKnoten = $this->getLevel($idKnoten);
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return FALSE;
		}
		if ($levelKnoten < 2) {
			$this->errorNo = 9;
			$this->errorStr = "Root-Koten kann nicht genl?scht werden.";
			$this->DbUnLocked();
			return FALSE;
		}

		$this->db->query(" DELETE FROM $this->DbTable
                WHERE $this->lftFeld BETWEEN $lftKnoten AND $rgtKnoten;");
		$this->db->query(" UPDATE $this->DbTable
                SET $this->lftFeld = $this->lftFeld - $diff
                WHERE $this->lftFeld > $rgtKnoten;");
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld - $diff
                WHERE $this->rgtFeld > $rgtKnoten;");
		$this->DbUnLocked();
		return TRUE;
	}


	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      bool    TRUE erfolgreich
	 * @error       FALSE
	 * @deprecated  Knoten mit linkem Bruder Platz tauschen
	 **/
	function moveLft($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// Rechte Grenze vom Knoten
		$rgtKnoten = $this->getRgt($idKnoten);
		if ($rgtKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze vom Knoten
		$lftKnoten = $this->getLft($idKnoten);
		if ($lftKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// root-Knoten?
		$levelKnoten = $this->getLevel($idKnoten);
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return FALSE;
		}
		if ($levelKnoten < 2) {
			$this->errorNo = 6;
			$this->errorStr = "Root-Koten kann nicht verschoben werden.";
			$this->DbUnLocked();
			return FALSE;
		}
		// ID des linken Bruders
		$idBrother = $this->getIdRgt($lftKnoten-1);
		if ($idBrother < 0) {
			$this->errorNo = 4;
			$this->errorStr = "Keinen linken Bruder gefunden.";
			$this->DbUnLocked();
			return FALSE;
		}
		// Rechte Grenze von linkem Bruder
		$rgtBrother = $this->getRgt($idBrother);
		if ($rgtBrother < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze von linkem Bruder
		$lftBrother = $this->getLft($idBrother);
		if ($lftBrother < 0) {
			$this->DbUnLocked();
			return FALSE;
		}


		// differenz zur alten Position
		$diffRgt = $rgtKnoten-$rgtBrother;
		$diffLft = $lftKnoten-$lftBrother;

		// moved 0 setzen
		$this->db->query(" UPDATE $this->DbTable
                SET $this->movedFeld = 0
                WHERE $this->movedFeld <> 0;");
		// Eintr?ge nach rechts bewegen (Platz machen)
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld   = $this->rgtFeld + $diffRgt,
		$this->lftFeld   = $this->lftFeld + $diffRgt,
		$this->movedFeld = 1
                WHERE $this->lftFeld BETWEEN $lftBrother AND $rgtBrother;");
		// Eintr?ge nach links bewegen
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld - $diffLft,
		$this->lftFeld = $this->lftFeld - $diffLft
                WHERE $this->lftFeld BETWEEN $lftKnoten AND $rgtKnoten
                AND $this->movedFeld = 0;");
		// moved 0 setzen
		$this->db->query(" UPDATE $this->DbTable
                SET $this->movedFeld = 0
                WHERE $this->movedFeld <> 0;");
		$this->DbUnLocked();
		return TRUE;
	}

	function moveToFirstChild ($src, $dst) {
		$lftKnoten = $this->getLft($dst);
		return $this->_moveSubtree ($src, $lftKnoten+1);
	}

	private function shiftRLValues ($first, $delta) {
		$this->db->query("UPDATE $this->DbTable SET ".$this->lftFeld." = ".$this->lftFeld."+$delta WHERE ".$this->lftFeld." >=$first");
		$this->db->query("UPDATE $this->DbTable SET ".$this->rgtFeld." = ".$this->rgtFeld."+$delta WHERE ".$this->rgtFeld." >=$first");
	}
	private function shiftRLRange ($first, $last, $delta, $levelmod) {
		$this->db->query("UPDATE $this->DbTable SET ".$this->lftFeld." = ".$this->lftFeld."+$delta, LEVEL = LEVEL + $levelmod WHERE ".$this->lftFeld.">=$first AND ".$this->lftFeld."<=$last");
		$this->db->query("UPDATE $this->DbTable SET ".$this->rgtFeld." = ".$this->rgtFeld."+$delta WHERE ".$this->rgtFeld.">=$first AND ".$this->rgtFeld."<=$last");
		return array('l' => $first+$delta, 'r' => $last+$delta);
	}


	function moveSubtree ($xsrc, $xto) {
		$to = (int)$this->getLft($xto);
		$src['r'] = (int)$this->getRgt($xsrc);
		$src['l'] = (int)$this->getLft($xsrc);

		$sourcelevel = $this->getLevel($xsrc);
		$targetlevel = $this->getLevel($to);
		if ($targetlevel == $sourcelevel-1) {
			$levelmod = 0;
		} else {
			$levelmod = $targetlevel - $sourcelevel + 1;
		}

		$treesize = (int)$src['r']-(int)$src['l']+1;
		$this->shiftRLValues($to, $treesize);
		if ($src['l'] >= $to) {
			// src was shifted too?
			$src['l'] += (int)$treesize;
			$src['r'] += (int)$treesize;
		}
		/* now there's enough room next to target to move the subtree*/
		$meep = $to-$src['l'];

		$newpos = $this->shiftRLRange($src['l'], $src['r'], (int)$meep, $levelmod);

		/* correct values after source */
		$this->shiftRLValues($src['r']+1, -$treesize);
		if ($src['l'] <= $to) {
			// dst was shifted too?
			$newpos['l'] -= $treesize;
			$newpos['r'] -= $treesize;
		}

		$this->db->query("UPDATE $this->DbTable SET PARENT = $xto WHERE ID = $xsrc;");
		$this->DbUnLocked();
		return $newpos;
	}
	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      bool    TRUE erfolgreich
	 * @error       FALSE
	 * @deprecated  Knoten mit rechtem Bruder Platz tauschen
	 **/
	function moveTo($idKnoten, $idTarget ) {

		$this->DbLocked();

		$sourcelevel = $this->getLevel($idKnoten);
		$targetlevel = $this->getLevel($idTarget);
		$levelmod = $targetlevel - $sourcelevel + 1;

		$cleft = $this->getLft($idKnoten);
		$cright = $this->getRgt($idKnoten);

		$pleft = $this->getLft($idTarget);
		$pright = $this->getRgt($idTarget);

		if ($cleft < 1) return false;
		if ($pleft < 1) return false;
		if (($pleft < $cleft) && ($pleft > $cright)) return false;
		if ($cleft == $pleft+1) return false;

		$treesize = $cright-$cleft+1;
		$to = $pleft+1;

		//shiftRLValues($thandle, $to, $treesize);
		$sql = "UPDATE $this->DbTable SET LFT=LFT+$treesize WHERE LFT >=$to";
		$this->db->query($sql);
		$sql = "UPDATE $this->DbTable SET RGT=RGT+$treesize WHERE RGT >=$to";
		$this->db->query($sql);

		$n_cleft = $cleft;
		$n_cright = $cright;
		if ($cleft >= $to) {
			// src was shifted too?
			$n_cleft += $treesize;
			$n_cright += $treesize;
		}
		/* now there's enough room next to target to move the subtree*/
		//$newpos = shiftRLRange($thandle, $cleft, $cright, $to-$cleft);
		$delta = $to-$n_cleft;
		$sql = "UPDATE $this->DbTable SET LFT=LFT+$delta WHERE LFT>=$n_cleft AND LFT<=$n_cright";
		$this->db->query($sql);
		$sql = "UPDATE $this->DbTable SET RGT=RGT+$delta WHERE RGT>=$n_cleft AND RGT<=$n_cright";
		$newpos = array('l' => $n_cleft+$delta, 'r' => $n_cright+$delta);
		$this->db->query($sql);

		/* correct values after source */
		//shiftRLValues($thandle, $cright+1, -$treesize);
		$ntreesize = -$treesize;
		$sql = "UPDATE $this->DbTable SET LFT=LFT+$ntreesize WHERE LFT >=$n_cright";
		$this->db->query($sql);
		$sql = "UPDATE $this->DbTable SET RGT=RGT+$ntreesize WHERE RGT >=$n_cright";
		$this->db->query($sql);

		if ($n_cleft <= $to) {
			// dst was shifted too?
			$newpos['l'] -= $treesize;
			$newpos['r'] -= $treesize;
		}

		// relevel
		$sourceNewLft = $this->getLft($idKnoten);
		$sourceNewRgt = $this->getRgt($idKnoten);
		$sql = "UPDATE $this->DbTable SET LEVEL = LEVEL + $levelmod WHERE LFT >= $sourceNewLft AND RGT <= $sourceNewRgt;";
		$this->db->query($sql);
		$sql = "UPDATE $this->DbTable SET PARENT = $idTarget WHERE ID = $idKnoten;";
		$this->db->query($sql);

		$this->DbUnLocked();
		return TRUE;
	}

	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      bool    TRUE erfolgreich
	 * @error       FALSE
	 * @deprecated  Knoten mit rechtem Bruder Platz tauschen
	 **/
	function moveRgt($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// Rechte Grenze vom Knoten
		$rgtKnoten = $this->getRgt($idKnoten);
		if ($rgtKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze vom Knoten
		$lftKnoten = $this->getLft($idKnoten);
		if ($lftKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// root-Knoten?
		$levelKnoten = $this->getLevel($idKnoten);
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return FALSE;
		}
		if ($levelKnoten < 2) {
			$this->errorNo = 6;
			$this->errorStr = "Root-Koten kann nicht verschoben werden.";
			$this->DbUnLocked();
			return FALSE;
		}
		// ID des rechten Bruders
		$idBrother = $this->getIdLft($rgtKnoten+1);
		if ($idBrother < 0) {
			$this->errorNo = 5;
			$this->errorStr = "Keinen Rechten Bruder gefunden.";
			$this->DbUnLocked();
			return FALSE;
		}
		// Rechte Grenze von rechtem Bruder
		$rgtBrother = $this->getRgt($idBrother);
		if ($rgtBrother < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze von rechtem Bruder
		$lftBrother = $this->getLft($idBrother);
		if ($lftBrother < 0) {
			$this->DbUnLocked();
			return FALSE;
		}

		// differenz zur alten Position
		$diffRgt = $rgtBrother-$rgtKnoten;
		$diffLft = $lftBrother-$lftKnoten;

		// moved 0 setzen
		$this->db->query(" UPDATE $this->DbTable
                SET $this->movedFeld = 0
                WHERE $this->movedFeld <> 0;");
		// Eintr?ge nach links bewegen (Platz machen)
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld   = $this->rgtFeld - $diffLft,
		$this->lftFeld   = $this->lftFeld - $diffLft,
		$this->movedFeld = 1
                WHERE $this->lftFeld BETWEEN $lftBrother AND $rgtBrother;");

		// Eintr?ge nach rechts bewegen
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld + $diffRgt,
		$this->lftFeld = $this->lftFeld + $diffRgt
                WHERE $this->lftFeld BETWEEN $lftKnoten AND $rgtKnoten
                AND $this->movedFeld = 0;");
		// moved 0 setzen
		$this->db->query(" UPDATE $this->DbTable
                SET $this->movedFeld = 0
                WHERE $this->movedFeld <> 0;");
		$this->DbUnLocked();
		return TRUE;
	}


	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      bool    TRUE erfolgreich
	 * @error       FALSE
	 * @deprecated  Knoten um eine Ebene nach oben.
	 *              Wird als rechter Bruder neben Vater gesetzter.
	 **/
	function moveUp($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// der root-Knoten oder in die root-Ebene kann nicht verschoben werden
		$levelKnoten = $this->getLevel($idKnoten);
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return FALSE;
		}
		if ($levelKnoten < 2) {
			$this->errorNo = 6;
			$this->errorStr = "Root-Koten kann nicht verschoben werden.";
			$this->DbUnLocked();
			return FALSE;
		}
		if ($levelKnoten < 3) {
			$this->errorNo = 7;
			$this->errorStr = "In die Root-Ebene nicht verschoben werden.";
			$this->DbUnLocked();
			return FALSE;
		}

		// Knoten nach rechts Verscheiben, bis er ganz rechts steht
		do {
			$moved = $this->moveRgt($idKnoten);
			if ($moved < 0) {
				if ($this->errorNo == 4) {
					$this->errorNo = 0;
					$this->errorStr = "";
					break;
				} else {
					return FALSE;
				}
			}
		}
		while ($moved == TRUE);


		// Rechte Grenze vom Knoten
		$rgtKnoten = $this->getRgt($idKnoten);
		if ($rgtKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}

		// Linke Grenze vom Knoten
		$lftKnoten = $this->getLft($idKnoten);
		if ($lftKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// ID des Vater Knotens
		$idVather = $this->getIdRgt($rgtKnoten+1);
		if ($idVather < 0) {
			$this->errorNo = 10;
			$this->errorStr = "Keinen Vater gefunden.";
			$this->DbUnLocked();
			return FALSE;
		}

		// Rechte Grenze vom Vater
		$rgtVather = $this->getRgt($idVather);
		if ($rgtVather < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze vom Vater
		$lftVather = $this->getLft($idVather);
		if ($lftVather < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// breite des Knotens
		$widthKnoten = $rgtKnoten-$lftKnoten+1;

		// Knoten um 1 nach rechts bewegen => F?llt aus Vaterknoten raus
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld + 1,
		$this->lftFeld = $this->lftFeld +1,
                LEVEL = LEVEL - 1
                WHERE $this->lftFeld BETWEEN $lftKnoten AND $rgtKnoten");
		// Vaterknoten vorm Knoten schlie?en
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld - $widthKnoten
                WHERE $this->idFeld = $idVather;");
		$this->DbUnLocked();

		$newparent = $this->getParent($idVather);
		if ($newparent < 0) {
			$this->errorNo = 10;
			$this->errorStr = "Keine neues Parent gefunden.";
			$this->DbUnLocked();
			return FALSE;
		}

		$this->db->query(" UPDATE $this->DbTable
                SET PARENT = $newparent
                WHERE $this->idFeld = $idKnoten");

		return TRUE;
	}


	/**
	 * @param       int     idKnoten    id des Knotens
	 * @return      bool    TRUE erfolgreich
	 * @error       FALSE
	 * @deprecated  Knoten um eine Ebene nach unten.
	 *              Wird rechter Sohn seines linken Bruders.
	 *                  K1     K2     K3
	 *                 /  \   /  \   /  \
	 *                K4..K5 K6..K7 K8..K9
	 *              --moveDown(K2)----------
	 *                    K1      K3
	 *                 /     \   /  \
	 *                K4..K5 K2 K8..K9
	 *                      /  \
	 *                     K6..K7
	 **/
	function moveDown($idKnoten) {
		// Tabelle sperren
		$this->DbLocked();
		// der root-Knoten kann nicht verschoben werden
		$levelKnoten = $this->getLevel($idKnoten);
		if ($levelKnoten < 1) {
			$this->DbUnLocked();
			return FALSE;
		}
		if ($levelKnoten < 2) {
			$this->errorNo = 6;
			$this->errorStr = "Root-Koten kann nicht verschoben werden.";
			$this->DbUnLocked();
			return FALSE;
		}

		// Rechte Grenze vom Knoten
		$rgtKnoten = $this->getRgt($idKnoten);
		if ($rgtKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze vom Knoten
		$lftKnoten = $this->getLft($idKnoten);
		if ($lftKnoten < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// ID des neuen Vater Knotens
		$idVather = $this->getIdRgt($lftKnoten-1);
		if ($idVather < 0) {
			$this->errorNo = 04;
			$this->errorStr = "Keinen linken Bruder gefunden.";
			$this->DbUnLocked();
			return FALSE;
		}
		// Rechte Grenze von neuen Vater
		$rgtVather = $this->getRgt($idVather);
		if ($rgtVather < 0) {
			$this->DbUnLocked();
			return FALSE;
		}
		// Linke Grenze von neuen Vater
		$lftVather = $this->getLft($idVather);
		if ($lftVather < 0) {
			$this->DbUnLocked();
			return FALSE;
		}

		// breite des Knotens
		$widthKnoten = $rgtKnoten-$lftKnoten+1;

		// Knoten um 1 nach links bewegen => Member des neuen Vaterknotens
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld - 1,
		$this->lftFeld = $this->lftFeld - 1
                WHERE $this->lftFeld BETWEEN $lftKnoten AND $rgtKnoten");
		// Vaterknoten hinterm Knoten schlie?en
		$this->db->query(" UPDATE $this->DbTable
                SET $this->rgtFeld = $this->rgtFeld + $widthKnoten
                WHERE $this->idFeld = $idVather;");
		$this->DbUnLocked();
		return TRUE;
	}


	/**
	 * @return      int     Fehlernummer
	 * @error       -1
	 * @deprecated  Gibt die Fehlernummer zur?ck.
	 **/
	function getErrorNo() {
		if ($this->errorNo < 1) {
			$this->errorNo = 13;
			$this->errorStr = "Kein Fehler aufgetreten";
			return -1;
		}
		return $this->errorNo;
	}


	/**
	 * @return      string  Fehlermeldung
	 * @error       ""
	 * @deprecated  Gibt die Fehlermeldung zur?ck.
	 **/
	function getErrorMsg() {
		if ($this->errorNo < 1) {
			$this->errorNo = 13;
			$this->errorStr = "Kein Fehler aufgetreten.";
			return "";
		}
		return $this->errorStr;
	}

	/**
	 * @access      privat
	 **/
	/**
	 * @var         string
	 * @deprecated  MySQL-DB Daten
	 **/
	var $DbTable = "";
	// @deprecated  Name der Table in der DB
	var $lftFeld = "";
	// @deprecated  Name des linken Tabellenfeldes
	var $rgtFeld = "";
	// @deprecated  Name des rechten Tabellenfeldes
	var $idFeld = "";
	// @deprecated  Name des id-Tabellenfeldes
	var $movedFeld = "";
	// @deprecated  Name des moved-Tabellenfeldes
	/**
	* @deprecated  Fehlerfall
	**/
	var $errorNo = 0;
	// @var         int
	var $errorStr = "";
	// @var         string
	/**
	* @package     NestedSetDb
	**/
	var $db;
	// @deprecated  MySQL-Datenbankanbindung


	/**
	 * @param       int     id  id des Knoten
	 * @return      int     rechte Grenze
	 * @error       -1
	 * @deprecated  Bestimmt die rechte Grenze eines Knotens
	 **/
	function getRgt($id) {
		$knoten = $this->db->queryFirst(" SELECT $this->rgtFeld as rgt
	FROM $this->DbTable
	WHERE $this->idFeld = $id;");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 1;
			$this->errorStr = "Der Knoten mit der ID=$id konnte nicht gefunden werden.";
			return -1;
		}
		return $knoten["rgt"];
	}


	/**
	 * @param       int     id  id des Knoten
	 * @return      int     linke Grenze
	 * @error       -1
	 * @deprecated  Bestimmt die linke Grenze eines Knotens
	 **/
	function getLft($id) {
		$knoten = $this->db->queryFirst(" SELECT $this->lftFeld as lft
			FROM $this->DbTable
			WHERE $this->idFeld = $id;");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 1;
			$this->errorStr = "Der Knoten mit der ID=$id konnte nicht gefunden werden.";
			return -1;
		}
		return $knoten["lft"];
	}


	/**
	 * @param       int     rgt  Rechte Grenze des Knoten
	 * @return      int     id des Kontens
	 * @error       -1
	 * @deprecated  bestimmt die ID eines Knotens anhand des rechten Grenze
	 **/
	function getIdRgt($rgt) {
		$knoten = $this->db->queryFirst(" SELECT $this->idFeld as id
                FROM $this->DbTable
WHERE $this->rgtFeld = $rgt;");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 2;
			$this->errorStr = "Ein Knoten mit der rechten Grenze $rgt konnte nicht gefunden werden.";
			return -1;
		}
		return $knoten["id"];
	}


	/**
	 * @param       int     lft  Linke Grenze des Knoten
	 * @return      int     id des Kontens
	 * @error       -1
	 * @deprecated  bestimmt die ID eines Knotens anhand des linken Grenze
	 **/
	function getIdLft($lft) {
		$knoten = $this->db->queryFirst(" SELECT $this->idFeld as id
FROM $this->DbTable
WHERE $this->lftFeld = $lft;");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 3;
			$this->errorStr = "Ein Knoten mit der linken Grenze $lft konnte nicht gefunden werden.";
			return -1;
		}
		return $knoten["id"];
	}

	function getAll($nodeid) {
		$knoten = $this->db->queryFirst(" SELECT id, $this->lftFeld, $this->rgtFeld
FROM $this->DbTable
WHERE $this->idFeld = $nodeid;");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 3;
			$this->errorStr = "Ein Knoten mit der linken Grenze $lft konnte nicht gefunden werden.";
			return -1;
		}
		return $knoten;
	}

	function getParent($nodeid) {
		$knoten = $this->db->queryFirst("SELECT * FROM $this->DbTable WHERE $this->idFeld = $nodeid;");
		return $knoten["PARENT"];
	}

	function getLevel_($idKnoten) {
		$knoten = $this->db->queryFirst("SELECT LEVEL
FROM $this->DbTable
                WHERE ID = $idKnoten;");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 3;
			$this->errorStr = "Ein Knoten mit der linken Grenze $lft konnte nicht gefunden werden.";
			return -1;
		}
		return $knoten["LEVEL"];
	}

	/**
	 * @param       int     idKnoten  ID des Knotens
	 * @return      int     Ebene des Knotens (0: rootKnoten)
	 * @error       -1
	 * @deprecated  Bestimmt die Ebene des Knotens
		**/
	function getLevel($idKnoten) {
		$knoten = $this->db->queryFirst("  SELECT baum2.$this->idFeld AS id,
                COUNT(*) AS level
	FROM $this->DbTable AS baum1,
		$this->DbTable AS baum2
	WHERE baum2.lft BETWEEN baum1.lft AND baum1.rgt
	GROUP BY baum2.lft, baum2.$this->idFeld
                ORDER BY ABS(baum2.$this->idFeld - $idKnoten);");

		//  $knoten =  $this->db->queryFirst("SELECT * FROM $this->DbTable WHERE $this->idFeld = $idKnoten");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 1;
			$this->errorStr = "Der Knoten mit der ID=$id konnte nicht gefunden werden.";
			return -1;
		}
		if ($knoten["id"] != $idKnoten) {
			// Knoten nicht gefunden
			$this->errorNo = 1;
			$this->errorStr = "Der Knoten mit der ID=$id konnte nicht gefunden werden.";
			return -1;
		}
		return $knoten["level"];
	}


	/**
	 * @return      int     Anzahl der Knoten
	 * @error       -1
	 * @deprecated  Bestimmt die Anzahl der Knoten in der Tabelle
	 **/
	function getAnzKnoten() {
		$knoten = $this->db->queryFirst(" SELECT COUNT(*) AS anz
	FROM $this->DbTable;");
		if (!$knoten) {
			// Knoten nicht gefunden
			$this->errorNo = 11;
			$this->errorStr = "Anzahl der Knoten konnte nicht bestimmt werden.";
			return -1;
		}
		return $knoten["anz"];
	}

	/**
		* @return      FALSE
		* @deprecated  Sperrt die DB-Tabelle
		**/
	function DbLocked() {
		/*        $this->db->query("  LOCK TABLES $this->DbTable WRITE,
		 $this->DbTable AS baum1 WRITE,
		$this->DbTable AS baum2 WRITE;");*/
		$this->db->db->StartTrans();
	}


	/**
		* @return      FALSE
		* @deprecated  Hebt Sperrung der DB-Tabelle auf
		**/
	function DbUnLocked() {
		$this->db->db->CompleteTrans();
		//        $this->db->query("UNLOCK TABLES;");
	}
}


///////////////////////////////////////////////


function nstNewRoot ($thandle, $othercols)
/* creates a new root record and returns the node 'l'=1, 'r'=2. */
{
	$newnode['l'] = 1;
	$newnode['r'] = 2;
	_insertNew ($thandle, $newnode, $othercols);
	return $newnode;
}

function nstNewFirstChild ($thandle, $node, $othercols)
/* creates a new first child of 'node'. */
{
	$newnode['l'] = $node['l']+1;
	$newnode['r'] = $node['l']+2;
	shiftRLValues($thandle, $newnode['l'], 2);
	_insertNew ($thandle, $newnode, $othercols);
	return $newnode;
}

function nstNewLastChild ($thandle, $node, $othercols)
/* creates a new last child of 'node'. */
{
	$newnode['l'] = $node['r'];
	$newnode['r'] = $node['r']+1;
	shiftRLValues($thandle, $newnode['l'], 2);
	_insertNew ($thandle, $newnode, $othercols);
	return $newnode;
}

function nstNewPrevSibling ($thandle, $node, $othercols) {
	$newnode['l'] = $node['l'];
	$newnode['r'] = $node['l']+1;
	shiftRLValues($thandle, $newnode['l'], 2);
	_insertNew ($thandle, $newnode, $othercols);
	return $newnode;
}

function nstNewNextSibling ($thandle, $node, $othercols) {
	$newnode['l'] = $node['r']+1;
	$newnode['r'] = $node['r']+2;
	shiftRLValues($thandle, $newnode['l'], 2);
	_insertNew ($thandle, $newnode, $othercols);
	return $newnode;
}


/* *** internal routines *** */

function shiftRLValues ($thandle, $first, $delta)
/* adds '$delta' to all L and R values that are >= '$first'. '$delta' can also be negative. */
{
	//print("SHIFT: add $delta to gr-eq than $first <br/>");
	mysql_query("UPDATE ".$thandle['table']." SET ".$thandle['lvalname']."=".$thandle['lvalname']."+$delta WHERE ".$thandle['lvalname'].">=$first");
	mysql_query("UPDATE ".$thandle['table']." SET ".$thandle['rvalname']."=".$thandle['rvalname']."+$delta WHERE ".$thandle['rvalname'].">=$first");
}
function shiftRLRange ($thandle, $first, $last, $delta)
/* adds '$delta' to all L and R values that are >= '$first' and <= '$last'. '$delta' can also be negative.
 returns the shifted first/last values as node array.
*/
{
	mysql_query("UPDATE ".$thandle['table']." SET ".$thandle['lvalname']."=".$thandle['lvalname']."+$delta WHERE ".$thandle['lvalname'].">=$first AND ".$thandle['lvalname']."<=$last");
	mysql_query("UPDATE ".$thandle['table']." SET ".$thandle['rvalname']."=".$thandle['rvalname']."+$delta WHERE ".$thandle['rvalname'].">=$first AND ".$thandle['rvalname']."<=$last");
	return array('l' => $first+$delta, 'r' => $last+$delta);
}

function _insertNew ($thandle, $node, $othercols)
/* creates a new root record and returns the node 'l'=1, 'r'=2. */
{
	if (strlen($othercols) > 0) {
		$othercols .= ",";
	}
	$res = mysql_query("INSERT INTO ".$thandle['table']." SET $othercols" .$thandle['lvalname']."=".$node['l'].", ".$thandle['rvalname']."=".$node['r']);
	if (!$res) {
		_prtError();
	}
}


/* ******************************************************************* */
/* Tree Reorganization */
/* ******************************************************************* */

/* all nstMove... functions return the new position of the moved subtree. */
function nstMoveToNextSibling ($thandle, $src, $dst)
/* moves the node '$src' and all its children (subtree) that it is the next sibling of '$dst'. */
{
	return _moveSubtree ($thandle, $src, $dst['r']+1);
}

function nstMoveToPrevSibling ($thandle, $src, $dst)
/* moves the node '$src' and all its children (subtree) that it is the prev sibling of '$dst'. */
{
	return _moveSubtree ($thandle, $src, $dst['l']);
}

function nstMoveToFirstChild ($thandle, $src, $dst)
/* moves the node '$src' and all its children (subtree) that it is the first child of '$dst'. */
{
	return _moveSubtree ($thandle, $src, $dst['l']+1);
}

function nstMoveToLastChild ($thandle, $src, $dst)
/* moves the node '$src' and all its children (subtree) that it is the last child of '$dst'. */
{
	return _moveSubtree ($thandle, $src, $dst['r']);
}

function _moveSubtree ($thandle, $src, $to)
/* '$src' is the node/subtree, '$to' is its destination l-value */
{
	$treesize = $src['r']-$src['l']+1;
	shiftRLValues($thandle, $to, $treesize);
	if ($src['l'] >= $to) {
		// src was shifted too?
		$src['l'] += $treesize;
		$src['r'] += $treesize;
	}
	/* now there's enough room next to target to move the subtree*/
	$newpos = shiftRLRange($thandle, $src['l'], $src['r'], $to-$src['l']);
	/* correct values after source */
	shiftRLValues($thandle, $src['r']+1, -$treesize);
	if ($src['l'] <= $to) {
		// dst was shifted too?
		$newpos['l'] -= $treesize;
		$newpos['r'] -= $treesize;
	}
	return $newpos;
}

/* ******************************************************************* */
/* Tree Destructors */
/* ******************************************************************* */

function nstDeleteTree ($thandle)
/* deletes the entire tree structure including all records. */
{
	$res = mysql_query("DELETE FROM ".$thandle['table']);
	if (!$res) {
		_prtError();
	}
}

function nstDelete ($thandle, $node)
/* deletes the node '$node' and all its children (subtree). */
{
	$leftanchor = $node['l'];
	$res = mysql_query("DELETE FROM ".$thandle['table']." WHERE " .$thandle['lvalname'].">=".$node['l']." AND ".$thandle['rvalname']."<=".$node['r']);
	shiftRLValues($thandle, $node['r']+1, $node['l'] - $node['r'] -1);
	if (!$res) {
		_prtError();
	}
	return nstGetNodeWhere ($thandle,
	$thandle['lvalname']."<".$leftanchor ." ORDER BY ".$thandle['lvalname']." DESC" );
}



/* ******************************************************************* */
/* Tree Queries */
/*
 * the following functions return a valid node (L and R-value),
* or L=0,R=0 if the result doesn't exist.
*/
/* ******************************************************************* */

function nstGetNodeWhere ($thandle, $whereclause)
/* returns the first node that matches the '$whereclause'.
 The WHERE-caluse can optionally contain ORDER BY or LIMIT clauses too.
*/
{
	$noderes['l'] = 0;
	$noderes['r'] = 0;
	$res = mysql_query("SELECT * FROM ".$thandle['table']." WHERE ".$whereclause);
	if (!$res) {
		_prtError();
	} else {
		if ($row = mysql_fetch_array ($res)) {
			$noderes['l'] = $row[$thandle['lvalname']];
			$noderes['r'] = $row[$thandle['rvalname']];
		}
	}
	return $noderes;
}

function nstGetNodeWhereLeft ($thandle, $leftval)
/* returns the node that matches the left value 'leftval'.
 */
{
	return nstGetNodeWhere($thandle, $thandle['lvalname']."=".$leftval);
}
function nstGetNodeWhereRight ($thandle, $rightval)
/* returns the node that matches the right value 'rightval'.
 */
{
	return nstGetNodeWhere($thandle, $thandle['rvalname']."=".$rightval);
}

function nstRoot ($thandle)
/* returns the first node that matches the '$whereclause' */
{
	return nstGetNodeWhere ($thandle, $thandle['lvalname']."=1");
}

function nstFirstChild ($thandle, $node) {
	return nstGetNodeWhere ($thandle, $thandle['lvalname']."=".($node['l']+1));
}
function nstLastChild ($thandle, $node) {
	return nstGetNodeWhere ($thandle, $thandle['rvalname']."=".($node['r']-1));
}
function nstPrevSibling ($thandle, $node) {
	return nstGetNodeWhere ($thandle, $thandle['rvalname']."=".($node['l']-1));
}
function nstNextSibling ($thandle, $node) {
	return nstGetNodeWhere ($thandle, $thandle['lvalname']."=".($node['r']+1));
}
function nstAncestor ($thandle, $node) {
	return nstGetNodeWhere ($thandle,
	$thandle['lvalname']."<".($node['l'])
	." AND ".$thandle['rvalname'].">".($node['r'])
	." ORDER BY ".$thandle['rvalname'] );
}


/* ******************************************************************* */
/* Tree Functions */
/*
 * the following functions return a boolean value
*/
/* ******************************************************************* */

function nstValidNode ($thandle, $node)
/* only checks, if L-value < R-value (does no db-query)*/
{
	return ($node['l'] < $node['r']);
}
function nstHasAncestor ($thandle, $node) {
	return nstValidNode($thandle, nstAncestor($thandle, $node));
}
function nstHasPrevSibling ($thandle, $node) {
	return nstValidNode($thandle, nstPrevSibling($thandle, $node));
}
function nstHasNextSibling ($thandle, $node) {
	return nstValidNode($thandle, nstNextSibling($thandle, $node));
}
function nstHasChildren ($thandle, $node) {
	return (($node['r']-$node['l']) > 1);
}
function nstIsRoot ($thandle, $node) {
	return ($node['l'] == 1);
}
function nstIsLeaf ($thandle, $node) {
	return (($node['r']-$node['l']) == 1);
}
function nstIsChild ($node1, $node2)
/* returns true, if 'node1' is a direct child or in the subtree of 'node2' */
{
	return (($node1['l'] > $node2['l']) and ($node1['r'] < $node2['r']));
}
function nstIsChildOrEqual ($node1, $node2) {
	return (($node1['l'] >= $node2['l']) and ($node1['r'] <= $node2['r']));
}
function nstEqual ($node1, $node2) {
	return (($node1['l'] == $node2['l']) and ($node1['r'] == $node2['r']));
}


/* ******************************************************************* */
/* Tree Functions */
/*
 * the following functions return an integer value
*/
/* ******************************************************************* */

function nstNbChildren ($thandle, $node) {
	return (($node['r']-$node['l']-1)/2);
}

function nstLevel ($thandle, $node)
/* returns node level. (root level = 0)*/
{
	$res = mysql_query("SELECT COUNT(*) AS level FROM ".$thandle['table']." WHERE " .$thandle['lvalname']."<".($node['l'])
	." AND ".$thandle['rvalname'].">".($node['r'])
	);

	if ($row = mysql_fetch_array ($res)) {
		return $row["level"];
	} else {
		return 0;
	}
}

/* ******************************************************************* */
/* Tree Walks  */
/* ******************************************************************* */

function nstWalkPreorder ($thandle, $node)
/* initializes preorder walk and returns a walk handle */
{
	$res = mysql_query("SELECT * FROM ".$thandle['table'] ." WHERE ".$thandle['lvalname'].">=".$node['l'] ."   AND ".$thandle['rvalname']."<=".$node['r'] ." ORDER BY ".$thandle['lvalname']);

	return array('recset' => $res,
							'prevl' => $node['l'], 'prevr' => $node['r'], // needed to efficiently calculate the level
							'level' => -2 );
}

function nstWalkNext($thandle, &$walkhand) {
	if ($row = mysql_fetch_array ($walkhand['recset'], MYSQL_ASSOC)) {
		// calc level
		$walkhand['level'] += $walkhand['prevl'] - $row[$thandle['lvalname']] +2;
		// store current node
		$walkhand['prevl'] = $row[$thandle['lvalname']];
		$walkhand['prevr'] = $row[$thandle['rvalname']];
		$walkhand['row'] = $row;
		return array('l' => $row[$thandle['lvalname']], 'r' => $row[$thandle['rvalname']]);
	} else {
		return FALSE;
	}
}

function nstWalkAttribute($thandle, $walkhand, $attribute) {
	return $walkhand['row'][$attribute];
}

function nstWalkCurrent($thandle, $walkhand) {
	return array('l' => $walkhand['prevl'], 'r' => $walkhand['prevr']);
}
function nstWalkLevel($thandle, $walkhand) {
	return $walkhand['level'];
}



/* ******************************************************************* */
/* Printing Tools */
/* ******************************************************************* */

function nstNodeAttribute ($thandle, $node, $attribute)
/* returns the attribute of the specified node */
{
	$res = mysql_query("SELECT * FROM ".$thandle['table']." WHERE ".$thandle['lvalname']."=".$node['l']);
	if ($row = mysql_fetch_array ($res)) {
		return $row[$attribute];
	} else {
		return "";
	}
}

function nstPrintSubtree ($thandle, $node, $attributes)
/*  */
{
	$wlk = nstWalkPreorder($thandle, $node);
	while ($curr = nstWalkNext($thandle, $wlk)) {
		// print indentation
		print (str_repeat("&nbsp;", nstWalkLevel($thandle, $wlk) * 4));
		// print attributes
		$att = reset($attributes);
		while ($att) {
			// next line is more efficient:  print ($att.":".nstWalkAttribute($thandle, $wlk, $att));
			print ($wlk['row'][$att]);
			$att = next($attributes);
		}
		print ("<br/>");
	}
}

function nstPrintSubtreeOLD ($thandle, $node, $attributes)
/*  */
{
	$res = mysql_query("SELECT * FROM ".$thandle['table']." ORDER BY ".$thandle['lvalname']);
	if (!$res) {
		_prtError();
	} else {
		$level = -1;
		$prevl = 0;
		while ($row = mysql_fetch_array ($res)) {
			// calc level
			if ($row[$thandle['lvalname']] == ($prevl+1)) {
				$level += 1;
			} elseif ($row[$thandle['lvalname']] != ($prevr+1)) {
				$level -= 1;
			}
			// print indentation
			print (str_repeat("&nbsp;", $level * 4));
			// print attributes
			$att = reset($attributes);
			while ($att) {
				print ($att.":".$row[$att]);
				$att = next($attributes);
			}
			print ("<br/>");
			$prevl = $row[$thandle['lvalname']];
			$prevr = $row[$thandle['rvalname']];
		}
	}
}

function nstPrintTree ($thandle, $attributes)
/* Prints attributes of the entire tree. */
{
	nstPrintSubtree ($thandle, nstRoot($thandle), $attributes);
}


function nstBreadcrumbsString ($thandle, $node)
/* returns a string representing the breadcrumbs from $node to $root
 Example: "root > a-node > another-node > current-node"

Contributed by Nick Luethi
*/
{
	// current node
	$ret = nstNodeAttribute ($thandle, $node, "name");
	// treat ancestor nodes
	while (nstAncestor ($thandle, $node) != array("l" => 0, "r" => 0)) {
		$ret = "".nstNodeAttribute($thandle, nstAncestor($thandle, $node), "name")." &gt; ".$ret;
		$node = nstAncestor ($thandle, $node);
	}
	return $ret;
	//return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;breadcrumb: <font size='1'>".$ret."</font>";
}

/* ******************************************************************* */
/* internal functions */
/* ******************************************************************* */

function _prtError() {
	echo "<p>Error: ".mysql_errno().": ".mysql_error()."</p>";
}

/// @endcond

?>