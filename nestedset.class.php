<?php
/**
 * A class to use nested sets easily
 *
 * @author Michael
 * @version 0.1
 *
 * Class NestedSet
 */
class NestedSet
{
	/*Properties*/

	/**
	 * Mysqli object
	 * @var mysqli
	 */
	protected $db;

	/**
	 * Name of the database table
	 * @var string
	 */
	public $table = 'tree';

    /**
     * Primary key of the database table
     * @var string
     */
    public $pk = 'id';

	/**
	 * Namefield in the database table
	 * @var string
	 */
	public $name = 'name';

	/*Methods*/

	/**
	 * Stores a Mysqli object for further use
	 * @param object $mysqli Mysqli object
	 * @return boolean true
	 */
	public function __construct($mysqli) {
		$this->db = $mysqli;
		return true;
	}


	/**
	 * Creates the root node
	 * @param string $name Name of the new node
	 * @return boolean true
	 */
	public function createRootNode($name) {
		$this->db->query("LOCK TABLES " . $this->table . " WRITE");
		$sql = "SELECT rgt FROM " . $this->table . " ORDER BY rgt DESC LIMIT 1";
		$result = $this->db->query($sql);
		if ($this->db->affected_rows == 0) {
			$lft = 1;
			$rgt = 2;
		} else {
			$obj = $result->fetch_object();
			$lft = $obj->rgt + 1;
			$rgt = $lft + 1;
		}
		$sql = "INSERT INTO " . $this->table . " (" . $this->name . ", lft, rgt) VALUES ('" . $name . "', " . $lft . ", " . $rgt . ");";
		$this->db->query($sql);
		$this->db->query("UNLOCK TABLES");
		return true;
	}


	/**
	 * Creates a new node
	 * @param string $name name of the new node
	 * @param integer $lft lft of parent node
	 * @param integer $rgt	rgt of parent node
	 * @return boolean	true
	 */
	protected function insertNode($name, $lft, $rgt) {
		$sql = "UPDATE " . $this->table . " SET rgt = rgt + 2 WHERE rgt >= " . $rgt . ";";
		$this->db->query($sql);
		$sql = "UPDATE " . $this->table . " SET lft = lft + 2 WHERE lft > " . $rgt . ";";
		$this->db->query($sql);
		$sql = "INSERT INTO " . $this->table . " (" . $this->name . ", lft, rgt) VALUES ('" . $name . "', " . $rgt . ", " . $rgt . "+1);";
		$this->db->query($sql);
		return true;
	}


	/**
	 * Gets an object with all data of a node
	 * @param integer $id id of the node
	 * @return object object with node-data (id, lft, rgt)
	 */
	protected function getNode($id) {
		$sql = "SELECT " . $this->pk . ", lft, rgt, " . $this->name . " FROM " . $this->table . " WHERE " . $this->pk . " = " . $id . ";";
		$result = $this->db->query($sql);
		if ($this->db->affected_rows == 0) {
			return $this->error(0);
		}
		$node = $result->fetch_object();
		return $node;
	}


	/**
	 * Creates a new child node of the node with the given id
	 * @param string $name name of the new node
	 * @param integer $parent id of the parent node
	 * @return boolean true
	 */
	public function insertChildNode($name, $parent) {
		$this->db->query("LOCK tables " . $this->table . " WRITE;");
		$p_node = $this->getNode($parent);
		$this->insertNode($name, $p_node->lft, $p_node->rgt);
		$this->db->query("UNLOCK TABLES;");
		return true;
	}


	/**
	 * Creates a multi-dimensional array of the whole tree
	 * @return array multi-dimenssional array of the whole tree
	 */
	public function getTree() {
		$sql = "SELECT n." . $this->pk . ", n." . $this->name . ", COUNT(*)-1 AS level FROM " . $this->table . " AS n, " . $this->table . " AS p WHERE n.lft BETWEEN p.lft AND p.rgt GROUP BY n.lft ORDER BY n.lft;";
		$result = $this->db->query($sql);
		if ($this->db->affected_rows == 0) {
			return $this->error(1, true);
		}
		$tree = array();
		$i = 0;
		while ($row = $result->fetch_assoc()) {
			$tree[$i] = $row;
			$i++;
		}
		return $tree;
	}


	/**
	 * Get the HTML code for an unordered list of the tree
	 * @return string HTML code for an unordered list of the whole tree
	 */
	public function treeAsHtml() {
		$tree = $this->getTree();
		$html = "<ul>\n";
		for ($i=0; $i<count($tree); $i++) {
			$html .= "<li>" . $tree[$i][$this->name];
			if ($tree[$i]['level'] < $tree[$i+1]['level']) {
				$html .= "\n<ul>\n";
			} elseif ($tree[$i]['level'] == $tree[$i+1]['level']) {
				$html .= "</li>\n";
			} else {
				$diff = $tree[$i]['level'] - $tree[$i+1]['level'];
				$html .= str_repeat("</li>\n</ul>\n", $diff) . "</li>\n";
			}
		}
		$html .= "</ul>\n";
		return $html;
	}


	/**
	 * Deletes a node an all it's children
	 * @param integer $id id of the node to delete
	 * @return boolean true
	 */
	public function deleteNode($id) {
		$this->db->query("LOCK tables " . $this->table . " WRITE;");
		$node = $this->getNode($id);
		$sql = "DELETE FROM " . $this->table . " WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ";";
		$this->db->query($sql);
		$sql = "UPDATE " . $this->table . " SET lft = lft - ROUND((" . $node->rgt . " - " . $node->lft . " + 1)) WHERE lft > " . $node->rgt . ";";
		$this->db->query($sql);
		$sql = "UPDATE " . $this->table . " SET rgt = rgt - ROUND((" . $node->rgt . " - " . $node->lft . " + 1)) WHERE rgt > " . $node->rgt . ";";
		$this->db->query($sql);
		$this->db->query("UNLOCK TABLES;");
		return true;
	}


	/**
	 * Deletes a node and increases the level of all children by one
	 * @param integer $id id of the node to delete
	 * @return boolean true
	 */
	public function deleteSingleNode($id) {
		$this->db->query("LOCK tables " . $this->table . " WRITE;");
		$node = $this->getNode($id);
		$sql = "DELETE FROM " . $this->table . " WHERE lft = " . $node->lft . ";";
		$this->db->query($sql);
		$sql = "UPDATE " . $this->table . " SET lft = lft - 1, rgt = rgt - 1 WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ";";
		$this->db->query($sql);
		$sql = "UPDATE " . $this->table . " SET lft = lft - 2 WHERE lft > " . $node->rgt . ";";
		$this->db->query($sql);
		$sql = "UPDATE " . $this->table . " SET rgt = rgt - 2 WHERE rgt > " . $node->rgt . ";";
		$this->db->query($sql);
		$this->db->query("UNLOCK TABLES;");
		return true;
	}


	/**
	 * Gets a multidimensional array containing the path to defined node
	 * @param integer $id id of the node to which the path should point
	 * @return array multidimensional array with the data of the nodes in the tree
	 */
	public function getPath($id) {
		$sql = "SELECT p." . $this->pk . ", p." . $this->name . " FROM " . $this->table . " n, " . $this->table . " p WHERE n.lft BETWEEN p.lft AND p.rgt AND n." . $this->pk ." = " . $id . " ORDER BY p.lft;";
		$result = $this->db->query($sql);
		if ($this->db->affected_rows == 0) {
			return $this->error(0);
		}
		$path = array();
		$i = 0;
		while ($row = $result->fetch_assoc()) {
			$path[$i] = $row;
			$i++;
		}
		return $path;
	}


	/**
	 * Gets the id of a node depending on it's rgt value
	 * @param integer $rgt rgt value of the node
	 * @return integer id of the node
	 */
	protected function getIdRgt($rgt) {
		$sql = "SELECT " . $this->pk . " FROM " . $this->table . " WHERE rgt = " . $rgt . ";";
		$result = $this->db->query($sql);
		if ($this->db->affected_rows == 0) {
			return false;
		}
		$obj = $result->fetch_object();
		return $obj->{$this->pk};
	}


	/**
	 * Moves a node one position to the left staying in the same level
	 * @param $nodeId id of the node to move
	 * @return boolean true
	 */
	public function moveLft($nodeId) {
		$this->db->query("LOCK tables " . $this->table . " WRITE;");
		$node = $this->getNode($nodeId);
		$brotherId = $this->getIdRgt($node->lft-1);
		if ($brotherId == false) {
			return $this->error(3);
		}
		$brother = $this->getNode($brotherId);

		$nodeSize = $node->rgt - $node->lft + 1;
		$brotherSize = $brother->rgt - $brother->lft + 1;

		$sql = "SELECT " . $this->pk . " FROM " . $this->table . " WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ";";
		$result = $this->db->query($sql);
		$idsNotToMove = array();
		while ($obj = $result->fetch_object()) {
			$idsNotToMove[] = $obj->{$this->pk};
		}

		$sql = "UPDATE " . $this->table . " SET lft = lft - " . $brotherSize . ", rgt = rgt - " . $brotherSize . " WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ";";
		$this->db->query($sql);

		$sql = "UPDATE " . $this->table . " SET lft = lft + " . $nodeSize . ", rgt = rgt + " . $nodeSize . " WHERE lft BETWEEN " . $brother->lft . " AND " . $brother->rgt;
		for ($i = 0; $i < count($idsNotToMove); $i++) {
			$sql .= " AND " . $this->pk . " != " . $idsNotToMove[$i];
		}
		$sql .= ";";
		$this->db->query($sql);
		$this->db->query("UNLOCK TABLES;");
		return true;
	}


	/**
	 * Gets the id of a node depending on it's lft value
	 * @param integer $lft lft value of the node
	 * @return integer id of the node
	 */
	protected function getIdLft($lft) {
		$sql = "SELECT " . $this->pk . " FROM " . $this->table . " WHERE lft = " . $lft . ";";
		$result = $this->db->query($sql);
		if ($this->db->affected_rows == 0) {
			return false;
		}
		$obj = $result->fetch_object();
		return $obj->{$this->pk};
	}


	/**
	 * Moves a node one position to the right staying in the same level
	 * @param $nodeId id of the node to move
	 * @return boolean true
	 */
	public function moveRgt($nodeId) {
		$this->db->query("LOCK tables " . $this->table . " WRITE;");
		$node = $this->getNode($nodeId);
		$brotherId = $this->getIdLft($node->rgt+1);
		if ($brotherId == false) {
			return $this->error(2);
		}
		$brother = $this->getNode($brotherId);

		$nodeSize = $node->rgt - $node->lft + 1;
		$brotherSize = $brother->rgt - $brother->lft + 1;

		$sql = "SELECT " . $this->pk . " FROM " . $this->table . " WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ";";
		$result = $this->db->query($sql);
		$idsNotToMove = array();
		while ($obj = $result->fetch_object()) {
			$idsNotToMove[] = $obj->{$this->pk};
		}

		$sql = "UPDATE " . $this->table . " SET lft = lft + " . $brotherSize . ", rgt = rgt + " . $brotherSize . " WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ";";
		$this->db->query($sql);

		$sql = "UPDATE " . $this->table . " SET lft = lft - " . $nodeSize . ", rgt = rgt - " . $nodeSize . " WHERE lft BETWEEN " . $brother->lft . " AND " . $brother->rgt;
		for ($i = 0; $i < count($idsNotToMove); $i++) {
			$sql .= " AND " . $this->pk . " != " . $idsNotToMove[$i];
		}
		$sql .= ";";
		$this->db->query($sql);
		$this->db->query("UNLOCK TABLES;");
		return true;
	}


	/**
	 * Prints a error message
	 * @param int $id array-key of the message
	 * @param boolean $continue continue script or not
	 * @return void
	 */
	public function error($id, $continue = false) {
		$errors = array ();
		$errors[] = 'There is no node with the given id!';
		$errors[] = 'No entries!';
		$errors[] = 'Node can\'t be moved to the right!';
		$errors[] = 'Node can\'t be moved to the left!';
		echo $errors[$id];
		if ($continue == false) {
			exit;
		}
	}

}
