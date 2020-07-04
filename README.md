Docs For Class NestedSet
========================

*NOTE: Before running this project import the tree.sql in MySQL DB*
-------------------------------------------------------------------

* * * * *

### 

Click to the blow link to see the tree category algorithm and full
documentation

[https://www.we-rc.com/blog/2015/07/19/nested-set-model-practical-examples-part-i](https://www.we-rc.com/blog/2015/07/19/nested-set-model-practical-examples-part-i)

**Rule 1**

The lft and rgt columns store left and right side values of a "tree
flow". Note the left and right are reserved keywords (R) in MySQL.

\
 \

**Rule 2**

The right side value rgt is allways greater than the node left side
value lft.

If rgt - lft = 1 then node does not have any leaves (is not a parent).

![](https://www.we-rc.com/fotosklad/articles/large/3/32/32e833f7.png)

Class NestedSet (file - nestedset.class.php) {.class-name}
--------------------------------------------

Description

Description | [details](#)) | [details](#))

A class to use nested sets easiely

Located in [/nestedset.class.php](#) (line [10](#))

Variable Summary

[Description](#) | Vars ([details](#)) | [details](#))

mysqli object [\$db](#)

string [\$name](#)

string [\$pk](#)

string [\$table](#)

Method Summary

[Description](#) | [details](#)) | Methods ([details](#))

boolean [\_\_construct](#) (object \$mysqli)

boolean [createRootNode](#) (string \$name)

boolean [deleteNode](#) (integer \$id)

boolean [deleteSingleNode](#) (integer \$id)

void [error](#) (int \$id, [boolean \$continue = false])

integer [getIdLft](#) (integer \$lft)

integer [getIdRgt](#) (integer \$rgt)

object object [getNode](#) (integer \$id)

array [getPath](#) (integer \$id)

array [getTree](#) ()

boolean [insertChildNode](#) (string \$name, integer \$parent)

boolean [insertNode](#) (string \$name, integer \$lft, integer \$rgt)

boolean [moveLft](#) (\$nodeId \$nodeId)

boolean [moveRgt](#) (\$nodeId \$nodeId)

string [treeAsHtml](#) ()

Variables

[Description](#) | [details) |](#)[details](#))

object \$db (line [18](#))

Mysqli object

-   access: protected

unknown\_type \$name = '' (line [36](#))

Namefield in the database table

-   access: public

string \$pk = '' (line [30](#))

Primary key of the database table

-   access: public

string \$table = '' (line [24](#))

Name of the database table

-   access: public

Methods

[Description](#) | [details](#)) details)

Constructor \_\_construct (line [45](#))

Stores a Mysqli object for further use

-   return: true
-   access: public

boolean \_\_construct (object \$mysqli)

-   object \$mysqli: Mysqli object

createRootNode (line [56](#))

Creates the root node

-   return: true
-   access: public

boolean createRootNode (string \$name)

-   string \$name: Name of the new node

deleteNode (line [172](#))

Deletes a node an all it's children

-   return: true
-   access: public

boolean deleteNode (integer \$id)

-   integer \$id: id of the node to delete

deleteSingleNode (line [191](#))

Deletes a node and increases the level of all children by one

-   return: true
-   access: public

boolean deleteSingleNode (integer \$id)

-   integer \$id: id of the node to delete

error (line [342](#))

Prints a error message

-   access: public

void error (int \$id, [boolean \$continue = false])

-   int \$id: array-key of the message
-   boolean \$continue: continue script or not

getIdLft (line [287](#))

Gets the id of a node depending on it's lft value

-   return: id of the node
-   access: protected

integer getIdLft (integer \$lft)

-   integer \$lft: lft value of the node

getIdRgt (line [233](#))

Gets the id of a node depending on it's rgt value

-   return: id of the node
-   access: protected

integer getIdRgt (integer \$rgt)

-   integer \$rgt: rgt value of the node

getNode (line [98](#))

Gets an object with all data of a node

-   return: with node-data (id, lft, rgt)
-   access: protected

object object getNode (integer \$id)

-   integer \$id: id of the node

getPath (line [212](#))

Gets a multidimensional array containing the path to defined node

-   return: multidimensional array with the data of the nodes in the
    tree
-   access: public

array getPath (integer \$id)

-   integer \$id: id of the node to which the path should point

getTree (line [128](#))

Creates a multi-dimensional array of the whole tree

-   return: multi-dimenssional array of the whole tree
-   access: public

array getTree ()

insertChildNode (line [115](#))

Creates a new child node of the node with the given id

-   return: true
-   access: public

boolean insertChildNode (string \$name, integer \$parent)

-   string \$name: name of the new node
-   integer \$parent: id of the parent node

insertNode (line [82](#))

Creates a new node

-   return: true
-   access: protected

boolean insertNode (string \$name, integer \$lft, integer \$rgt)

-   string \$name: name of the new node
-   integer \$lft: lft of parent node
-   integer \$rgt: rgt of parent node

moveLft (line [249](#))

Moves a node one position to the left staying in the same level

-   return: true
-   access: public

boolean moveLft (\$nodeId \$nodeId)

-   \$nodeId \$nodeId: id of the node to move

moveRgt (line [303](#))

Moves a node one position to the right staying in the same level

-   return: true
-   access: public

boolean moveRgt (\$nodeId \$nodeId)

-   \$nodeId \$nodeId: id of the node to move

treeAsHtml (line [148](#))

Get the HTML code for an unordered list of the tree

-   return: HTML code for an unordered list of the whole tree
-   access: public

string treeAsHtml ()
