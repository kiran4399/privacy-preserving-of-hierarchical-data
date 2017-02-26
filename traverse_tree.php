<?php
class BinaryNode
{
...
    // perform an in-order traversal of the current node
    public function dump() {
        if ($this->left !== null) {
            $this->left->dump();
        }
        var_dump($this->value);
        if ($this->right !== null) {
            $this->right->dump();
        }
    }
}

class BinaryTree
{
...
    public function traverse() {
        // dump the tree rooted at "root"
        $this->root->dump();
    }
}