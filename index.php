<a href="beeCode.php">Click here to see the code for the bee game</a>
<?php
echo '<h1>The Bee Game</h1>';

$beehive = new Beehive;
if(isset($_POST['shoot'])){
    // Shoot was fired
    $beehive->shoot();
}
if($beehive->message){
    echo '<h2>' . $beehive->message . '</h2>';
}
?>

<form action="" method="post">
    <input type="submit" name="shoot" value="<?php echo $beehive->startNew? 'Start New Game' : 'Shoot at the beehive!';?>">
</form>

<?php 
$bees = $beehive->getBeehive();
if(count($bees)){
    // If the beehive has any bees
    echo '<h2>Beehive has:</h2>';
    foreach($bees as $bee){
        $beeType = $bee['type'];
        $hitPoints = $bee['life'];
        echo "$beeType with $hitPoints life left<br><br>";
    }
}

class Beehive
{
    private $hit = array();// Contains the bee that was hit or killed
    private $beehive = array();// Contains all bees that are alive
    public $startNew = false;
    public $message = '';

    public function __construct(){
        // We start a session and save all data within there
        session_start();
        // Loads the beehive
        $this->beehive = isset($_SESSION['beehive'])? $_SESSION['beehive'] : array();
        if(empty($this->beehive)){
            // Beehive is empty
            $this->startNew = true;
        }
    }

    public function __destruct(){
        // Always saves the beehive for next session
        $_SESSION['beehive'] = $this->beehive;
    }

    // Returns the beehive
    public function getBeehive(){
        return $this->beehive;
    }

    // Shoot was fired
    public function shoot(){
        if($this->startNew){
            $this->newGame();
            $this->message = 'A new game has started';
        } else {
            // Get a random bee to hit
            $bee = $this->getRandomBee();
            // Hitting the given bee
            $this->hitBee($bee);
            if(!isset($this->beehive[$bee])){
                // The bee is no longer alive
                if(empty($this->beehive)){
                    // Beehive is empty, all the bees are died
                    $this->message = 'All Bees have been killed';
                } else {
                    $this->message = 'You killed a ' . $this->hit['type'] . ' bee';
                }
            } else {
                $this->message = 'You hit a ' . $this->hit['type'] . ' bee';
            }
        }
    }

    // Returns a random bee
    private function getRandomBee(){
        return array_rand($this->beehive);
    }

    // Hits a given bee
    private function hitBee($bee){
        $this->hit = $this->beehive[$bee];
        switch(strtolower($this->hit['type'])){
            case 'queen':
                $this->hit['life'] -= 8;
                break;
            case 'worker':
                $this->hit['life'] -= 10;
                break;
            case 'drone':
                $this->hit['life'] -= 12;
                break;
        }
        if($this->hit['life'] <=0){
            // The bee died
            $this->killBee($bee);
        } else {
            $this->beehive[$bee] = $this->hit;
        }
    }

    // Kills a given bee
    private function killBee($bee){
        if(strtolower($this->hit['type']) == 'queen'){
            // The queen was killed
            // This ends the game, Game Over
            $this->endGame();
        }
        unset($this->beehive[$bee]);
    }

    // Ends the game and resets all data
    private function endGame(){
        $this->startNew = true;
        $this->beehive = array();
        session_destroy();
    }

    // Restarts the game
    private function newGame(){
        // Create a fresh beehive
        $this->beehive = array();
        // 1 Queen
        $this->beehive[] = $this->newQueen();
        // 5 Workers
        for($x = 0; $x<5; $x++){
            $this->beehive[] = $this->newWorker();
        }
        // 8 Drones
        for($x = 0; $x<8; $x++){
            $this->beehive[] = $this->newDrone();
        }
    }

    // Returns a new Queen
    private function newQueen(){
        return array(
            'type' => 'Queen',
            'life' => 100,
        );
    }

    // Returns a new Worker
    private function newWorker(){
        return array(
            'type' => 'Worker',
            'life' => 75,
        );
    }

    // Returns a new Drone
    private function newDrone(){
        return array(
            'type' => 'Drone',
            'life' => 50,
        );
    }
}
