<?php namespace AppName\abilities;

class DataBase
{
    public $useful;
    public $config;
    private $DataBase;
    private $config_file = "database.json"; // It's niether a straight path to file or just a file
    private $ping_loops = 0;
    
    /**
     * @param object|array|null $mysqli 
     */
    public function __construct($mysqli = null)
    {
        switch (gettype($mysqli)) {
            case "object":
                $this->DataBase = &$mysqli;
                break;
            case "array":
                $this->setSettings($mysqli);
                break;
            default:
                print_r($this->getSettings());
                break;
        }
    }

    public function getSettings() : array
    {
        if (file_exists($this->config_file)) {
            $file = file_get_contents($this->config_file);
            $config = json_decode($file, true);
        } else throw new Exception("Error: config file doesn't exist in '{$this->config_file}'");
        console("DataBase settings were successfully recieved")->paint("WHITE", "GREEN");
        return $this->config = $config;
    }

    public function setSettings(array $settings) : void
    {
        $this->config = $settings;
        $data = json_encode($settings, JSON_PRETTY_PRINT);
        if (file_put_contents($this->config_file, $data)) console("DataBase settings were successfully set up")->paint("WHITE", "GREEN");
    }

    public function connect()
    {
        try {
            $this->DataBase = new \mysqli($this->config['hostname'], $this->config['username'], $this->config['password'], $this->config['database']);
            if ($this->DataBase->connect_errno) {
                $error = "Failed to connect to MySQL: (" . $this->DataBase->connect_errno . ") " . $this->DataBase->connect_error;
                console($error)->paint("WHITE", "RED");
            }
        } catch (\Exception $e) {
            console($e->getMessage())->paint("WHITE", "RED");
        }
    }

    public function reconnect()
    {
        if (!$this->DataBase || $this->DataBase->close()) {
            console("Reconnection...")->paint("BLACK", "LIGHTGRAY", true);
            $this->connect();
            print "     ";
            if (!$this->DataBase->connect_errno) console("OK")->paint("BLACK", "LIGHTGRAY");
        }
        else return false;
    }

    public function query($query, $close = false)
    {
        try {
            if (!$this->DataBase) $this->ping();
            if ($query != false) $result = $this->DataBase->query($query);
            if (isset($this->DataBase->errno) && $this->DataBase->errno) {
                throw new Exception("Failed to send a query to MySQL: (" . $this->DataBase->errno . ") " . $this->DataBase->error);
            }
            if ($close) $this->DataBase->close(); else $this->ping();
            if (isset($result)) return $result; else return false;
        } catch (\Exception $e) {
            console($e->getMessage())->paint("WHITE", "RED");
        }
    }
    
    public function ping(int $loops = 1000)
    {
        if ($this->DataBase && @$this->DataBase->ping()) {
            if ($this->ping_loops % 5 == 0) console("Connection pinged - OK")->paint("WHITE", "CYAN");
            if ($this->ping_loops > $loops) {
                $this->reconnect();
                $this->ping_loops = 0;
            }
            $this->ping_loops++;
            return true;
        } else {
            console("Connection pinged - ERROR: ".@$this->DataBase->error)->paint("WHITE", "RED");
            $this->connect(); // here is not 'reconnect' because it is already disconnected
            return false;
        }
    }
}

?>