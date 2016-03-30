<?php

// Connect database
$con=mysqli_connect("localhost","gbv_discovery","gbv_discovery","gbv_discovery");

// Check connection
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

// Set Charset
if (!$con->set_charset("utf8"))
{
  printf("Error loading character set utf8: %s\n", $mysqli->error);
  exit();
}

// Query Database
$sql="SELECT * from words_unsolved";
$result=mysqli_query($con,$sql);

// Fetch all unsolved word lists
$Daten = mysqli_fetch_all($result,MYSQLI_ASSOC);

// Free result set
mysqli_free_result($result);

// Loop word lists
$DelIDs = array();
$Count  = 0;
foreach ( $Daten  as $Row )
{
  $worte = str_replace(array("\"","¬",",",".",";",":","_","#","'","+","*","´","`","?","=","(",")","/","&","%","$","§","!","°","^","[","]","<",">","{","}","~","\"","/","²","³","@","€","|","-","0","1","2","3","4","5","6","7","8","9"), " ", $Row["worte"]);
  //$worte = trim(str_replace("  ", " ", $worte));
  $worte    = trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $worte));
  $worte    = explode(" ", $worte);
  $WortStg  = array();
  foreach ( $worte as $wort )
  {
    $wort = trim($wort);
    if ( strlen(utf8_decode($wort)) >= 2 && strlen(utf8_decode($wort)) <= 200 )
    {
      $Count++;
      $WortStg[] = "('" . mysqli_real_escape_string($con,$wort) . "', 1,'" . $Row["datumzeit"] . "')";
    }
  }
  if ( count($WortStg) > 0 )
  {
    $sql="insert into words (wort, anzahl, datumzeit) values " . implode(",", $WortStg) . " ON DUPLICATE KEY UPDATE anzahl=anzahl+1, datumzeit='" . $Row["datumzeit"] . "'";
    if ( !mysqli_query($con,$sql))
    {
      printf("Error processing statement: %s\n", $mysqli->error);
      exit();
    }
    else
    {
      $DelIDs[] = $Row["id"];
    }
  }
}

// Delete processed records
if ( count($DelIDs) > 0 )
{
  $sql="delete from words_unsolved where id in (" . implode(",",$DelIDs) . ")";
  //echo $sql;
  $result=mysqli_query($con,$sql);
}

// Close database connection
mysqli_close($con);

// Answer to cron based logfile
echo date("d.m.Y H:i:s") . " " . count($Daten) . " result-packages prosessed containing " . $Count . " words\n";

?>