<?php

/**
 * @author Michael
 * @link category-tree algorithm explanation https://www.we-rc.com/blog/2015/07/19/nested-set-model-practical-examples-part-i
 * @see documentatio.html | to see open the file on the browser
 * @note import the tree.sql file in DB
 */

require_once('config.php'); // DB configuration
require_once('nestedset.class.php'); // class for categories

$object = new NestedSet($db);

echo '<pre>';

//$object->createRootNode('test 1'); // create new node

//$object->insertChildNode('test child node for "test 1"', 11);  // new child node for node(id = 11)

//print_r($object->getTree());  // get tree

//$object->deleteNode(12); // delete node(id = 12)

print_r($object->treeAsHtml()); // get tree as HTML

echo '</pre>';
