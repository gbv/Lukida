<?php

$Tmp = $_SESSION["login"] + $_SESSION["items"] + $_SESSION["fees"];

  foreach ( $Tmp as $Field => $Value )
  {
    $Output .= "<tr><td>" . $Field . "</td><td>";
    
    if ( ! is_array($Value) )
    {
      $Output .= $Value;
    }
    else
    {
      $Output .= $this->printarray(0,$Value);
    }
    $Output .= "</td></tr>";
  }


?>