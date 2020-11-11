$data = [
   'one' => 1,
   'parent' => [
       'child1' => [
            'child2' => true
       ]
   ]
];

$this->dot('one'); //1
$this->dot('parent'); //['child1' => ['child2' => true]]
$this->dot('parent.child1'); //['child2' => true]
$this->dot('parent.child1.child2'); //true
$this->dot('parent.child1.child3'); //false
this->dot('parent.child1.child3', 'my_default'); //'my_default'