<?php

/*
        Author: © Noli
        Edited: 21.06.2015
        Description: Klasse zur Verwendung der Ungarischem Methode
                    - Ermittlung des optimalzustands einer relation anhand von bipatite graph matching. 
        Portierung von © Noli
    */


class HungarianBipatiteMatching{
  public $costMatrix = array();
  public $rows = 0;
  public $cols = 0;
  public $dim = 0;
  public $labelByWorker = array();
  public $labelByJob =array();
  public $minSlackWorkerByJob=array();
  public $minSlackValueByJob=array();
  public $matchJobByWorker=array();
  public $matchWorkerByJob=array();
  public $parentWorkerByCommittedJob=array();
  public $committedWorkers=array();


  public function HungarianBipatiteMatching($intMatrix){
    $this->rows = sizeof($intMatrix);

    $this->cols = sizeof($intMatrix[0]);
    $this->dim = max($this->rows,$this->cols);

    for($i = 0;$i<$this->dim;$i++){
        $costMatrix[$i] = array_fill(0,$this->dim,0);
    }
   for ($w = 0; $w < $this->dim; $w++) {
            if ($w < sizeof($intMatrix)){
                if (sizeof($intMatrix[$w]) != $this->cols){
                    throw new InvalidArgumentException("Irregular cost matrix");
                }
                $this->costMatrix[$w] = $this->arrayCopyOf($intMatrix[$w],$this->dim);
            }
            else{
               $this->costMatrix[$w] = array();
                for($i = 0;$i<$this->dim;$i++){
                                               $this->costMatrix[$w][] = 0;
                }

            }

        }

        for($i = 0;$i<$this->dim;$i++){
        $this->labelByWorker[] = 0;
        $this->labelByJob[] = 0;
        $this->minSlackWorkerByJob[] = 0;
        $this->minSlackValueByJob[] = 0;
        $this->parentWorkerByCommittedJob[] = 0;
        $this->matchJobByWorker[] = 0;
        $this->matchWorkerByJob[] = 0;

        }
        $this->committedWorkers = array_fill(0, $this->dim, false);
        $this->matchJobByWorker = array_fill(0,$this->dim,-1);
        $this->matchWorkerByJob = array_fill(0,$this->dim,-1);


  }

  public function computeInitialFeasibleSolution(){
        for ($j = 0; $j < $this->dim; $j++){
            $this->labelByJob[$j] = INF;
        }
        for ($w = 0; $w < $this->dim; $w++){
            for ($j = 0; $j < $this->dim; $j++){
                if ($this->costMatrix[$w][$j] < $this->labelByJob[$j]){
                    $this->labelByJob[$j] = $this->costMatrix[$w][$j];
                }
            }
        }
    }

    public function execute(){
        $this->reduce();
        $this->computeInitialFeasibleSolution();
        $this->greedyMatch();
        $w = $this->fetchUnmatchedWorker();

        while ($w < $this->dim) {
            $this->initializePhase($w);
            $this->executePhase();
            $w = $this->fetchUnmatchedWorker();
        }

        $result = $this->arrayCopyOf($this->matchJobByWorker, $this->rows);

        for ($w = 0; $w < sizeof($result); $w++){
            if ($result[$w] >= $this->cols){
                $result[$w] = -1;
            }
        }
        return $result;

    }

    protected function executePhase(){

        while (true)

        {

            $minSlackWorker = -1;
            $minSlackJob = -1;

            $minSlackValue = INF;

            for ($j = 0; $j < $this->dim; $j++)

            {

                if ($this->parentWorkerByCommittedJob[$j] == -1)

                {

                    if ($this->minSlackValueByJob[$j] < $minSlackValue)

                    {

                        $minSlackValue = $this->minSlackValueByJob[$j];

                        $minSlackWorker = $this->minSlackWorkerByJob[$j];

                        $minSlackJob = $j;

                    }

                }

            }

            if ($minSlackValue > 0)

            {

                $this->updateLabeling($minSlackValue);

            }

            $this->parentWorkerByCommittedJob[$minSlackJob] = $minSlackWorker;

            if ($this->matchWorkerByJob[$minSlackJob] == -1)

            {

               $committedJob = $minSlackJob;

                $parentWorker = $this->parentWorkerByCommittedJob[$committedJob];

                while (true)

                {

                    $temp = $this->matchJobByWorker[$parentWorker];

                    $this->match($parentWorker, $committedJob);

                    $committedJob = $temp;

                    if ($committedJob == -1)

                    {

                        break;

                    }

                    $parentWorker = $this->parentWorkerByCommittedJob[$committedJob];

                }


                return;

            }

            else

            {


                $worker = $this->matchWorkerByJob[$minSlackJob];

                $this->committedWorkers[$worker] = true;

                for ($j = 0; $j < $this->dim; $j++)

                {

                    if ($this->parentWorkerByCommittedJob[$j] == -1)

                    {

                        $slack = $this->costMatrix[$worker][$j]

                                - $this->labelByWorker[$worker] - $this->labelByJob[$j];

                        if ($this->minSlackValueByJob[$j] > $slack)

                        {

                            $this->minSlackValueByJob[$j] = $slack;

                            $this->minSlackWorkerByJob[$j] = $worker;

                        }

                    }

                }

            }

        }

    }

    protected function fetchUnmatchedWorker()

    {

        $w;

        for ($w = 0; $w < $this->dim; $w++)

        {

            if ($this->matchJobByWorker[$w] == -1)

            {

                break;

            }

        }

        return $w;

    }



    protected function greedyMatch()

    {

        for ($w = 0; $w < $this->dim; $w++)

        {

            for ($j = 0; $j < $this->dim; $j++)

            {

                if ($this->matchJobByWorker[$w] == -1

                        && $this->matchWorkerByJob[$j] == -1

                        && $this->costMatrix[$w][$j] - $this->labelByWorker[$w] - $this->labelByJob[$j] == 0)

                {

                    $this->match($w, $j);

                }

            }

        }

    }



    protected function initializePhase($w)

    {
        $this->committedWorkers = array_fill(0,sizeof($this->committedWorkers),false);
        //Arrays.fill(committedWorkers, false);
        $this->parentWorkerByCommittedJob = array_fill(0,sizeof($this->parentWorkerByCommittedJob),-1);
        //Arrays.fill(parentWorkerByCommittedJob, -1);

        $this->committedWorkers[$w] = true;

        for ($j = 0; $j < $this->dim; $j++)

        {

            $this->minSlackValueByJob[$j] = $this->costMatrix[$w][$j] - $this->labelByWorker[$w]

                    - $this->labelByJob[$j];

            $this->minSlackWorkerByJob[$j] = $w;

        }

    }



    protected function match($w, $j)

    {

        $this->matchJobByWorker[$w] = $j;

        $this->matchWorkerByJob[$j] = $w;

    }



    protected function reduce()

    {

        for ($w = 0; $w < $this->dim; $w++)

        {

           $min = INF;

            for ($j = 0; $j < $this->dim; $j++)

            {

                if ($this->costMatrix[$w][$j] < $min)

                {

                    $min = $this->costMatrix[$w][$j];

                }

            }

            for ($j = 0; $j < $this->dim; $j++)

            {

                $this->costMatrix[$w][$j] -= $min;

            }

        }

        $min = array_fill(0,$this->dim,0); //ALERT

        for ($j = 0; $j < $this->dim; $j++)

        {

            $min[$j] = INF;

        }

        for ($w = 0; $w < $this->dim; $w++)

        {

            for ($j = 0; $j < $this->dim; $j++)

            {

                if ($this->costMatrix[$w][$j] < $min[$j])

                {

                    $min[$j] = $this->costMatrix[$w][$j];

                }

            }

        }

        for ($w = 0; $w < $this->dim; $w++)

        {

            for ($j = 0; $j < $this->dim; $j++)

            {

                $this->costMatrix[$w][$j] -= $min[$j];

            }

        }

    }



    protected function updateLabeling($slack)

    {

        for ($w = 0; $w < $this->dim; $w++)

        {

            if ($this->committedWorkers[$w])

            {

                $this->labelByWorker[$w] += $slack;

            }

        }

        for ($j = 0; $j < $this->dim; $j++)

        {

            if ($this->parentWorkerByCommittedJob[$j] != -1)

            {

                $this->labelByJob[$j] -= $slack;

            }

            else

            {

                $this->minSlackValueByJob[$j] -= $slack;

            }

        }

    }




  public function arrayCopyOf($array, $size){ // Java API port
    $tmp = array();
    foreach($array as $arr){
        $tmp[] = $arr;
    }

if(sizeof($array)<$size){
for($i =0;$i<$size-sizeof($array);$i++){
    $tmp[]=0;
}
}
return $tmp;
}




}

$m = array(
array(73, 52, 35, 83, 97, 18, 74, 58),
array(39, 61, 69, 93, 8, 29, 21, 80),
array(88, 54, 55, 28, 80, 32, 77, 86),
array(73, 82, 25, 34, 26, 13, 74, 25),
array(65, 65, 17, 92, 71, 85, 69, 39),
array(40, 32, 43, 45, 12, 41, 72, 41),
array(75, 33, 82, 48, 25, 65, 71, 9),
array(39, 32, 12, 48, 86, 77, 36, 69)

);

$hungarian = new HungarianBipatiteMatching($m);
$result = $hungarian->execute();
echo("RESULT");
print_r($result);
echo("<br>done");

?>