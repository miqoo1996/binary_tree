<?php

class Tree
{
    public $nodes = [];

    public $sortBy;

    public function __construct($nodes = [])
    {
        $this->nodes = $nodes;
    }

    public function createRootNode($name, $id = null)
    {
        $lft = 1;
        $rgt = 2;
        if (count($this->nodes)) {
            $nodes = $this->sortByNodeValue('rgt', 'desc');
            foreach ($nodes as $node) {
                if (isset($node['rgt'])) {
                    $lft = $node['rgt'] + 1;
                    $rgt = $lft + 1;
                    break;
                }
            }
        }

        $this->nodes[!is_null($id) ? $id : uniqid()] = [
            'lft' => $lft,
            'rgt' => $rgt,
            'rand_num' => rand(0, 999999),
            'name' => $name
        ];
    }

    public function insertNode($name, $lft, $rgt, $id = null)
    {
        foreach ($this->nodes as $key => $node) {
            if (!isset($node['rgt'], $node['lft'])) continue;

            if ($node['rgt'] >= $rgt) {
                $this->nodes[$key]['rgt'] += 2;
            }

            if ($node['lft'] > $rgt) {
                $this->nodes[$key]['lft'] += 2;
            }
        }

        $this->nodes[!is_null($id) ? $id : uniqid()] = [
            'lft' => $rgt,
            'rgt' => $rgt + 1,
            'rand_num' => rand(0, 999999),
            'name' => $name
        ];
    }

    protected function getNode($id)
    {
        if (!isset($this->nodes[$id])) {
            $this->error(0);
        }

        return $this->nodes[$id];
    }

    public function insertChildNode($name, $parent, $id = null)
    {
        $p_node = $this->getNode($parent);
        $this->insertNode($name, $p_node['lft'], $p_node['rgt'], $id);
    }

    public function getTree()
    {
        $tree = [];
        $level = 1;
        foreach ($this->nodes as $id => $node) {
            if (empty($node)) continue;

            foreach ($this->nodes as $subnode) {
                if (!($node['lft'] >= $subnode['lft'] && $node['lft'] <= $subnode['rgt'])) {
                    continue;
                }
            }

            $tree[$id] = $node;
            $tree[$id]['level'] = $level;

            $level++;
        }

        $treeObj = new self($tree);

        $tree = $treeObj->sortByNodeValue('lft');

        return $tree;
    }

    public function deleteNode($id)
    {
        $_node = $this->getNode($id);

        foreach ($this->nodes as $_id => $node) {
            if ($node['lft'] >= $_node['lft'] && $node['lft'] <= $_node['lft']) {
                unset($this->nodes[$_id]);
                //$this->nodes[$_id] = null;
            }
        }

        //// checking lft values after delete
        foreach ($this->nodes as $_id => $node) {
            if ($_node['lft'] > $_node['rgt']) {
                $this->nodes[$_id]['lft'] -= round($_node['rgt'] - $_node['lft'] + 1);
            }
        }

        //// checking rgt values after delete
        foreach ($this->nodes as $_id => $node) {
            if ($_node['rgt'] > $_node['rgt']) {
                $this->nodes[$_id]['rgt'] -= round($_node['rgt'] - $_node['lft'] + 1);
            }
        }
    }

    public function getPath($_id)
    {
        // SELECT p.id, p.name FROM tree n, tree p WHERE n.lft BETWEEN p.lft AND p.rgt AND n.id = 11 ORDER BY p.lft;

        if (!isset($this->nodes[$_id])) return null;

        $subnode = $this->nodes[$_id];

        $res_nodes = [];

        foreach ($this->nodes as $id => $node) {
            if (empty($node)) continue;

            if ($node['lft'] >= $subnode['lft'] && $node['lft'] <= $subnode['rgt']) {
                $node['type'] = $id == $_id ? 'parent' : 'child';
                $res_nodes[$id] = $node;
            }
        }

        return $res_nodes;
    }

    public function treeAsHtml() {
        $tree = $this->getTree();
        $html = "<ul>\n";
        for ($i=0; $i<count($tree); $i++) {
            $html .= "<li>" . $tree[$i]['name'];
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

    public function sortBy($a, $b)
    {
        return $a[$this->sortBy] - $b[$this->sortBy];
    }

    public function sortByNodeValue($value, $orderBy = 'asc')
    {
        $this->sortBy = $value;

        $nodes = $this->nodes;

        usort($nodes, [$this, 'sortBy']);

        if ($orderBy == 'desc') {
            $nodes = array_reverse($this->nodes);
        }

        return $nodes;
    }

    /**
     * Prints a error message
     * @param int $id array-key of the message
     * @param boolean $continue continue script or not
     * @return void
     */
    public function error($id, $continue = false)
    {
        $errors = array();
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

$tree = new Tree();

// Id this is the key of the node array
// If we will not pass the Id then it gets a random generated value

$tree->createRootNode('test1', 0); // id = 0

$tree->createRootNode('test2', 1); // id = 1

$tree->createRootNode('test3', 2); // id = 2

$tree->insertChildNode('child node for node(id=1)', 1, 3); // id = 3

$tree->deleteNode(0); // delete node id = 0

$tree->insertChildNode('child node 2 for node(id=1)', 1, 4); // id = 4

$tree->insertChildNode('child node 3 for node(id=1)', 1, 5); // id = 5

//$tree->deleteNode(5); // delete node id = 5

//print_r($tree->getPath(1)); // getting category with branches. (category(id=1) with his sub categories)

print_r($tree->treeAsHtml()); // getting tree with his branches

die;