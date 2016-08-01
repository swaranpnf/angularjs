<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CategoriesController extends AppController {

    public function index() {
        $allChildren = $this->Category->children(0); // a flat array with 11 items
       $allChildren= $this->Category->getPath(15);
        pr($allChildren); die;
        //    pr($allChildren); die;
                
// -- or --
$this->Category->id = 1;
$allChildren = $this->Category->children(); // a flat array with 11 items

// Only return direct children
$directChildren = $this->Category->children(1, true);

    
    }
}
