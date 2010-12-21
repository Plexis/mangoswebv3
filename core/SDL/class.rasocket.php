<?php
// RA class for MangosWebSDL. Originally from TrinMangSDK
// Re-written and added SOAP functions by Steven Wilson (Wilson212)

class RA
{
	private $handle;
    private $errorstr, $errorno;
    private $auth;
    public $com;
	
	var $logFile = 'core/logs/ra.log';
	var $consoleReturn = array();

//	************************************************************
    /**
      Class constructer.
    */
    public function __construct()
    {
        $this->handle = FALSE;
    }

//	************************************************************
    /**
      Class destructor. Closes the connection.
      Called with unset($parent).
    */
    public function __destruct()
    {
        if($this->handle)
        {
            fclose($this->handle);
            $this->auth = FALSE;
        }
    }

//	************************************************************
    /**
      Once connected to the server, this allows you to login
      Returns TRUE if it was successful.
      Returns FALSE if it was unable to authenticate.
    */
    public function auth($user, $pass)
    {
        $user = strtoupper($user);
        fwrite($this->handle, $user."\n");
        usleep(300);
        fwrite($this->handle, $pass."\n");
        usleep(300);
		
		$return = trim(fgets($this->handle));

        if(strpos($return, "+") === FALSE || strpos($return, "U") === FALSE)
		{
			$this->authReturn = $return;
			$this->writeLog('Telnet - AUTH Error: '.$this->authReturn);
			return FALSE;
		}
        else
        {
            $this->auth = TRUE;
            return TRUE;
        }
    }

//	************************************************************
    /**
      Attempts to connect to console. Returns false if it was unable to connect.
      Returns true if it is successfully connected.
      @param $host the IP or the DNS name of the server
      @param $port the port on which try to connect (default 3443)
    */
    public function connect($host, $port = 3443)
    {
        if($this->handle)
		{
			fclose($this->handle);
		}
        $this->handle = @fsockopen($host, $port, $errno, $errstr, 3);
        if(!$this->handle)
		{
			return FALSE;
		}
        else 
		{
			// get the message of the day
			$motd = fgets($this->handle);
            return TRUE;
        }
    }
	
//	************************************************************	
// Writes into the log file, a message

	private function writeLog($msg)
	{
		$outmsg = date('Y-m-d H:i:s')." : ".$msg."<br />\n";
		
		$file = fopen($this->logFile,'a');
		fwrite($file, $outmsg);
		fclose($file);
	}

//	************************************************************	
	 /**
      Inputs a command into an active connection to MaNGOS/Trinity
      Adds the output of the console into ralog.
      Returns 0 if it's not connected
      Returns 1 if it the command was sent successfully
      Returns 2 if it's not authenticated
      @param $command the command to enter on console
    */
    public function executeCommand($type, $shost, $remote, $command)
    {
		global $Config;
		if($type == 0)
		{
			if(!$this->connect($shost, $remote[1]))
			{
				return 0;
			}
			
			if(!$this->auth($remote[2], $remote[3]))
			{
				return 2;
			}
			
			if(is_array($command))
			{
				foreach($command as $cmd)
				{
					fwrite($this->handle, $cmd."\n");
					sleep(1);
					$this->consoleReturn[] = fgets($this->handle, 1024);
				}
			}
			else
			{
				fwrite($this->handle, $command."\n");
				sleep(1);
				$this->consoleReturn[] = fgets($this->handle, 1024);
			}
			return 1;
		}
		else
		{
			$client = $this->soapHandle($shost, $remote);
			
			if(is_array($command))
			{
				foreach($command as $cmd)
				{
					try
					{					
						$result = $client->executeCommand(new SoapParam($cmd, "command"));
						$this->consoleReturn[] = $result;
					}
					catch(Exception $e)
					{
						$this->consoleReturn[] = $e->getMessage();
						$this->writeLog('Soap - Send Mail Problem: '.$e->getMessage());
					}
				}
			}
			else
			{
				try
				{				
					$result = $client->executeCommand(new SoapParam($command, "command"));
					$this->consoleReturn[] = $result;
				}
				catch(Exception $e)
				{
					$this->consoleReturn[] = $e->getMessage();
					$this->writeLog('Soap - Send Mail Problem: '.$e->getMessage());
				}
			}
			return 1;
		}
    }

//	************************************************************
// Setups the Soap Handle	
	private function soapHandle($shost, $remote)
	{
		global $Config, $DB;
		if($Config->get('emulator') == 'mangos')
		{
			$client = new SoapClient(NULL,
			array(
			"location" => "http://".$shost.":".$remote[1]."/",
			"uri" => "urn:MaNGOS",
			"style" => SOAP_RPC,
			"login" => $remote[2],
			"password" => $remote[3]
			));
		}
		elseif($Config->get('emulator') == 'trinity')
		{
			$client = new SoapClient(NULL,
			array(
			"location" => "http://".$shost.":".$remote[1]."/",
			"uri" => "urn:TC",
			"style" => SOAP_RPC,
			"login" => $remote[2],
			"password" => $remote[3]
			));
		}
		return $client;
	}

//	************************************************************	
	/*
		Main sending function for the site
		This function gets the RA info for the realm.
		and executes the command.
		send( Command, realm ID )
		returns 1 if unable to connect
		return 2 if unauthorized
		returns console return upon success
	*/
	function send($command, $realm)
	{
		global $user, $Config, $DB;
		$get_remote = $DB->selectRow("SELECT * FROM `realmlist` WHERE id='".$realm."'");
		$remote = explode(';', $get_remote['ra_info']);
		$shost = $get_remote['address'];
		if($remote[0] == 0 || $remote[0] == 1)
		{
			$result = $this->executeCommand($remote[0], $shost, $remote, $command);
			if($result != 1)
			{
				if($result == 0)
				{
					return 1;
				}
				elseif($result == 2)
				{
					return 2;
				}
			}
			else
			{
				return $this->consoleReturn;
			}
		}
	}
}
?>