<?php

class Node
{
    public $value = "";
    /**
     * @var self
     */
    public $directionRight = null;
    /**
     * @var self
     */
    public $directionLeft = null;

    public function __construct($value)
    {
        $this->setValue($value);
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setDirectionLeft(Node $node)
    {
        $this->directionLeft = $node;
    }

    public function setDirectionRight(Node $node)
    {
        $this->directionRight = $node;
    }

    public function outputRecursively($spaces, $direction)
    {

        for ($i = 0; $i < $spaces; $i++) {
            echo "-";
        }
        echo $direction . ":" . $this->value . "<br />";
        if ($this->directionLeft != null) {
            $this->directionLeft->outputRecursively($spaces + 1, "LEFT");
        }
        if ($this->directionRight != null) {
            $this->directionRight->outputRecursively($spaces + 1, "RIGHT");
        }
    }

}

class Tree
{

    public $treeStart;

    public function __construct($node)
    {

        $this->treeStart = $node;

    }

    public function outputTree()
    {

        $this->treeStart->outputRecursively(0, "");

    }

}


$root = new Node("Root element");
$root->setDirectionLeft(new Node("First left-side child"));
$root->setDirectionRight(new Node("First right-side child"));
$tree = new Tree($root);
$tree->outputTree();

?>