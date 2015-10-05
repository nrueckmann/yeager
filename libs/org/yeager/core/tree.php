<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

\framework\import("org.yeager.framework.db.nested_set");

/// @cond DEV

/**
 * The tree class, which represents an instance of an Object Tree.
 */
class Tree extends \framework\Error {
	var $_object;
	var $_objectprefix;
	var $_objectidentifier;

	/**
	 * Constructor of the Tree class
	 *
	 * @param object $object Reference to object from which the Tree class was instantiated
	 */
	function __construct(&$object = NULL) {
		$this->_object = $object;
		$this->table = $this->_object->getTreeTable();
		$this->db = new NestedSetDb($link_id, $this->table, TRUE);
		$this->baum = new NestedSet ($this->table, "LFT", "RGT", "MOVED", "ID", $mail, $link_id, TRUE);
	}

	/**
	 * Gets subnodes
	 *
	 * @param int $parent Node Id from which the subnodes should be taken
	 * @param int $maxLevel Specifies the maximum level of nodes to get
	 * @param bool includeProps (optional) If TRUE, the result array will also contain Properties
	 * @return array Tree nodes
	 */
	function get($parent, $maxLevel, $includeProps = false) {
		$maxLevel = (int)$maxLevel;
		$parent = (int)$parent;
		if ($parent > 0) {
			$myinfo = $this->getAll($parent);
			$myleft = $myinfo["LFT"];
			$myrgt = $myinfo["RGT"];
			$subnodesql = " AND (group2.LFT >= $myleft AND group2.RGT <= $myrgt) ";
		}
		if ($maxLevel > 0) {
			$maxLevelSQL = " AND (group2.LEVEL <= $maxLevel) AND (group1.LEVEL <= $maxLevel)";
		}
		if ($includeProps) {
			$sql_select .= ", " . $this->_object->getPropertyTable() . ".*";
			$sql_from .= ", " . $this->_object->getPropertyTable();
			$sql_where .= " AND (" . $this->_object->getPropertyTable() . ".OBJECTID = group2.LFT)";
		}
		$sql = "SELECT
			group2.* $sql_select
			FROM
			($this->table AS group1, $this->table AS group2 $sql_from)
			WHERE
			((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) $sql_where $subnodesql $maxLevelSQL
			GROUP BY
			group2.LFT, group2.RGT;";
		$result = sYDB()->Execute($sql);
		if ($result) {
			$resulta = $result->GetArray();
		}
		return $resulta;
	}

	/**
	 * Moves Node one level up in hierarchy
	 *
	 * @param int $oid Node Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function left($oid) {
		$oid = (int)$oid;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RWRITE")) {
			$this->baum->moveUp($oid);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Moves Node one level down in hierarchy
	 *
	 * @param int $oid Node Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function right($oid) {
		$oid = (int)$oid;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RWRITE")) {
			$this->baum->moveDown($oid);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Moves Node up (in the same level)
	 *
	 * @param int $oid Node Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function up($oid) {
		$oid = (int)$oid;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RWRITE")) {
			$this->baum->moveLft($oid);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Moves Node down (in the same level)
	 *
	 * @param int $oid Node Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function down($oid) {
		$oid = (int)$oid;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RWRITE")) {
			$this->baum->moveRgt($oid);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if the specified Node has subnodes
	 *
	 * @param int $oid Node Id
	 * @return bool TRUE if the node has subnodes, otherwise FALSE
	 */
	function hasSubnodes($oid) {
		$oid = (int)$oid;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RREAD")) {
			$myinfo = ($this->baum->getAll($oid));
			if ($myinfo["LFT"] + 1 == $myinfo["RGT"]) {
				return false;
			} else {
				return true;
			}
		}
	}

	/**
	 * Gets parent Node
	 *
	 * @param int $oid Node Id
	 * @return int Node Id of parent Node
	 */
	function getParent($oid) {
		$oid = (int)$oid;
		//  if (($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RREAD")) || ($this->_object->getTreeTable() == "yg_templates_tree")) {
		$sql = "SELECT `PARENT` FROM `" . $this->_object->getTreeTable() . "` WHERE `ID` = $oid";
		$result = sYDB()->Execute($sql);
		if ($result) {
			$resulta = $result->GetArray();
		}
		return $resulta[0]["PARENT"];
		//  }
	}

	/**
	 * Gets the Node Id of the root Node
	 *
	 * @return int Node Id of root Node
	 */
	function getRoot() {
		$sql = "SELECT lft.ID AS ID FROM `" . $this->_object->getTreeTable() . "` AS lft WHERE
			(lft.LFT = (SELECT MIN( rgt.LFT ) FROM `" . $this->_object->getTreeTable() . "` AS rgt WHERE rgt.LFT > 0))
			";
		$result = sYDB()->Execute($sql);
		if ($result) {
			$resulta = $result->GetArray();
		}
		return $resulta[0]["ID"];
	}

	/**
	 * Gets direct children of the specified Node
	 *
	 * @param int $oid Node Id
	 * @return array Array of Nodes
	 */
	function getDirectChildren($oid) {
		$oid = (int)$oid;
		if (($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RREAD"))) {
			$sql = "SELECT * FROM `" . $this->_object->getTreeTable() . "` AS lft WHERE
				(lft.PARENT = $oid) ORDER BY LFT ASC;";
			$result = sYDB()->Execute($sql);
			if ($result) {
				$resulta = $result->GetArray();
			}
			return $resulta;
		}
	}

	/**
	 * Gets parent Nodes of a specified Node
	 *
	 * @param int $oid Node Id node
	 * @param int $uptio Node Id of oldest ancestor node
	 * @return Array Node Id of parent nodes
	 */
	function getParents($oid, $upto = 0) {
		$oid = (int)$oid;
		$upto = (int)$upto;
		$i = 0;
		$parents = array();
		while ($oid >= 1 && $oid != $upto) {
			$parents[$i] = $this->getParent($oid);
			$oid = $parents[$i];
			$i++;
			if ($oid < 1 || $oid == $upto) {
				break;
			}
		}
		return $parents;
	}

	/**
	 * Gets left neighbor of specified Node
	 *
	 * @param int $oid Node Id
	 * @return int Node Id of left Node
	 */
	function getLeft($oid) {
		$oid = (int)$oid;
		$lft_border = $this->baum->getLft($oid);
		if ($lft_border < 0) {
			return FALSE;
		}
		$left_id = $this->baum->getIdRgt($lft_border - 1);
		if ($left_id < 0) {
			return FALSE;
		}
		return $left_id;
	}

	/**
	 * Gets right neighbor of specified Node
	 *
	 * @param int $oid Node Id
	 * @return int Node Id of left Node
	 */
	function getRight($oid) {
		$oid = (int)$oid;
		$rgt_border = $this->baum->getRgt($oid);
		if ($rgt_border < 0) {
			return FALSE;
		}
		$right_id = $this->baum->getIdLft($rgt_border + 1);
		if ($right_id < 0) {
			return FALSE;
		}
		return $right_id;
	}

	/**
	 * Adds subnode to specified Node
	 *
	 * @param int $parentNodeId Node Id of parent Node
	 * @return int Node Id of new Node
	 */
	function add($parentNodeId) {
		$parentNodeId = (int)$parentNodeId;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $parentNodeId, "RSUB")) {
			return $this->baum->insertChild($parentNodeId);
		}
	}

	/**
	 * Removes specified Node including all subnodes
	 *
	 * @param int $oid Node Id of parent Node
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function remove($oid) {
		$oid = (int)$oid;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RDELETE")) {
			return $this->baum->deleteAll($oid);
		}
	}

	/**
	 * Gets tree structure including all subnodes
	 *
	 * @param int $oid Node Id of parent Node
	 * @return array Tree Nodes
	 */
	function getAll($oid) {
		$oid = (int)$oid;
		if ($this->_object->permissions->checkInternal($this->_object->_uid, $oid, "RREAD")) {
			return $this->baum->getAll($oid);
		}
	}

	/**
	 * Moves specified Node and all of its subnodes
	 *
	 * @param target $target Target node, will be new parent node
	 * @param source $source Source node id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function moveTo($source, $target) {
		$rTargetSub = $this->_object->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $target, 'RSUB');
		$rSourceDelete = $this->_object->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $source, 'RDELETE');
		$rSourceWrite = $this->_object->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $source, 'RWRITE');

		if ($rTargetSub && $rSourceDelete && $rSourceWrite) {
			$myp = $this->getParents($target);
			for ($i = 0; $i < count($myp); $i++) {
				if ($myp[$i] == $source) {
					return false;
				}
			}
			$sourceParents = $this->getParents($source);
			if ($sourceParents[0] == $target) {
				return true;
			}
			return $this->baum->moveTo($source, $target);
		} else {
			return false;
		}
	}

	/**
	 * Gets first key of Array
	 *
	 * @param array $arr Array
	 * @return mixed Key of first Array
	 */
	function getArrayFirstIndex($arr) {
		foreach ($arr as $key => $value)
			return $key;
	}

	/**
	 * Converts tree->getAll result into Nested Array
	 *
	 * @param array $cats Reference to getAll-Result
	 * @param int $i Iterator
	 * @return array nested Array
	 */
	function nest(&$cats, $i) {
		$new = array();
		if (count($cats) < 1) {
			return;
		}
		$i = (int)$i;
		while (list(, $cat) = each($cats)) {
			$new[$cat['ID']] = $cat;
			$next_id = key($cats);
			if (($cat['LEVEL'] < $cats[$next_id]["LEVEL"])) {
				$ncc = Tree::nest($cats, $i);
				if (($ncc[Tree::getArrayFirstIndex($ncc)]["LFT"]) > 0) {
					$new[$cat['ID']]['CHILDREN'] = $ncc;
				}
			}
			$next_id = key($cats);
			if ($next_id && $cats[$next_id]['PARENT'] != $cat['PARENT']) {
				return $new;
			}
		}
		$i++;
		return $new;
	}

	/**
	 * Gets the level the specified Node
	 *
	 * @param int $id Node Id
	 * @return int Level of Node Id
	 */
	function getLevel($id) {
		$id = (int)$id;
		$level = $this->baum->getLevel($id);
		return $level;
	}

}

/// @endcond

?>