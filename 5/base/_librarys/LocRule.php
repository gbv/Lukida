<?php






























class LocRule
{
  private static $Cache = array();

  
  public static function decode($Value)
  {
    return str_replace(array("{semikolon}", "{pipe}", "{paragraph}"), array(";", "|", "§"), $Value);
  }

  
  public static function isRegex($Value)
  {
    return substr((string) $Value, 0, 1) === "^";
  }

  
  public static function isRegexRule($Rule, $Sep = ":")
  {
    $Pos = strpos((string) $Rule, $Sep);
    return $Pos !== false && substr($Rule, $Pos + 1, 1) === "^";
  }

  
  public static function check($Value)
  {
    $P = self::parse($Value);
    return $P["error"];
  }

  
  public static function matches($Rule, $Value, $Sep = ":")
  {
    if ( self::isRegexRule($Rule, $Sep) )
    {
      $RPos = strpos($Rule,  $Sep);
      $VPos = strpos($Value, $Sep);
      if ( $VPos === false || substr($Rule, 0, $RPos) !== substr($Value, 0, $VPos) )  return false;

      $P = self::parse(substr($Rule, $RPos + 1));
      if ( $P["error"] !== "" )  return false;

      set_error_handler(function() { return true; });
      $Hit = preg_match($P["pcre"], substr($Value, $VPos + 1));
      restore_error_handler();
      return $Hit === 1;
    }

    if ( substr($Rule, -1) == "*" )
    {
      $Pos = strpos($Rule, "*");
      return substr($Rule, 0, $Pos) == substr($Value, 0, $Pos);
    }
    return $Rule == $Value;
  }

  
  
  
  
  public static function toSolr($Rule, $Sep = ":")
  {
    $Pos = strpos($Rule, $Sep);
    if ( $Pos === false )  return "";

    $P = self::parse(substr($Rule, $Pos + 1));
    if ( $P["error"] !== "" )  return "";

    $Head = self::luceneLiteral(substr($Rule, 0, $Pos + 1));
    $Body = $P["lucene"] === "" ? "" : "(" . $P["lucene"] . ")";
    return "/" . $Head . $Body . ($P["end"] ? "" : ".*") . "/";
  }

  
  private static function luceneLiteral($Text)
  {
    return preg_replace('/[^A-Za-z0-9_:\x80-\xFF]/', '\\\\$0', $Text);
  }

  
  private static function parse($Value)
  {
    if ( isset(self::$Cache[$Value]) )  return self::$Cache[$Value];

    $Res = array("pcre" => "", "lucene" => "", "end" => false, "error" => "");
    if ( !self::isRegex($Value) )
    {
      $Res["error"] = "beginnt nicht mit ^";
      return self::$Cache[$Value] = $Res;
    }

    $Body     = substr($Value, 1);
    $Len      = strlen($Body);
    $Pcre     = "";
    $Luc      = "";
    $InClass  = false;
    $ClassPos = 0;      
    $Quant    = false;  
    $Depth    = 0;
    $Special  = "/@#&<>~\" ";  

    for ( $i = 0; $i < $Len; $i++ )
    {
      $C = $Body[$i];

      if ( $C === "\\" )
      {
        if ( $i + 1 >= $Len )  return self::fail($Value, "endet mit \\");
        $N = $Body[$i + 1];
        if ( ctype_alnum($N) && strpos("dDwWsS", $N) === false )
          return self::fail($Value, "\\" . $N . " ist nicht erlaubt (nur \\d \\D \\w \\W \\s \\S)");
        if ( ctype_alpha($N) )
        {
          
          
          $Set = array("d" => "0-9", "w" => "A-Za-z0-9_", "s" => " \\t\\n\\r\\f\\x0B");
          $L   = strtolower($N);
          if ( $InClass && $N !== $L )  return self::fail($Value, "\\" . $N . " ist innerhalb von [...] nicht erlaubt");
          $Pcre .= $InClass ? $Set[$L] : ( "[" . ($N !== $L ? "^" : "") . $Set[$L] . "]" );
        }
        else
        {
          $Pcre .= $C . $N;
        }
        $Luc  .= $C . $N;
        $i++;
        $Quant = false;
        if ( $InClass )  $ClassPos++;
        continue;
      }

      if ( $InClass )
      {
        if ( $C === "]" && $ClassPos > 0 )
        {
          $InClass = false;
          $Pcre   .= $C;
          $Luc    .= $C;
        }
        elseif ( $C === "^" && $ClassPos == 0 && substr($Body, $i - 1, 1) === "[" )
        {
          $Pcre .= $C;
          $Luc  .= $C;
        }
        else
        {
          if ( $C === "[" && substr($Body, $i + 1, 1) === ":" )  return self::fail($Value, "[:...:]-Klassen sind nicht erlaubt");
          $Pcre .= $C;
          $Luc  .= ( $C === "]" || strpos($Special, $C) !== false ) ? "\\" . $C : $C;
          $ClassPos++;
        }
        continue;
      }

      switch ( $C )
      {
        case "[":
          $InClass  = true;
          $ClassPos = 0;
          $Quant    = false;
          $Pcre    .= $C;
          $Luc     .= $C;
          break;

        case "(":
          if ( substr($Body, $i + 1, 1) === "?" )
          {
            if ( substr($Body, $i + 2, 1) !== ":" )  return self::fail($Value, "(?...) ist nicht erlaubt, nur (?:...)");
            $Pcre .= "(?:";
            $Luc  .= "(";
            $i    += 2;
          }
          else
          {
            $Pcre .= $C;
            $Luc  .= $C;
          }
          $Depth++;
          $Quant = false;
          break;

        case ")":
          if ( --$Depth < 0 )  return self::fail($Value, "Klammer ) ohne (");
          $Pcre .= $C;
          $Luc  .= $C;
          $Quant = false;
          break;

        case "*":
        case "+":
        case "?":
          if ( $Quant )  return self::fail($Value, "doppelter Quantor (z. B. *? oder ++) ist nicht erlaubt");
          if ( $Pcre === "" || substr($Pcre, -1) === "(" || substr($Pcre, -1) === "|" )  return self::fail($Value, $C . " ohne Zeichen davor");
          $Pcre .= $C;
          $Luc  .= $C;
          $Quant = true;
          break;

        case "{":
          if ( preg_match('/\G\{(\d+)(,(\d*))?\}/', $Body, $M, 0, $i) )
          {
            if ( $Quant )  return self::fail($Value, "doppelter Quantor ist nicht erlaubt");
            if ( isset($M[3]) && $M[3] !== "" && intval($M[3]) < intval($M[1]) )  return self::fail($Value, $M[0] . ": Obergrenze kleiner als Untergrenze");
            $Pcre .= $M[0];
            $Luc  .= $M[0];
            $i    += strlen($M[0]) - 1;
            $Quant = true;
          }
          else
          {
            
            
            return self::fail($Value, "{ ohne gueltigen Quantor {n}, {n,} oder {n,m} (als Zeichen: \\{)");
          }
          break;

        case "}":
          return self::fail($Value, "} ohne { (als Zeichen: \\})");

        case "$":
          if ( $i != $Len - 1 )  return self::fail($Value, "\$ ist nur am Ende erlaubt");
          $Res["end"] = true;
          break;

        case "^":
          return self::fail($Value, "^ ist nur am Anfang erlaubt");

        default:
          $Pcre .= $C;
          $Luc  .= strpos($Special, $C) !== false ? "\\" . $C : $C;
          $Quant = false;
      }
    }

    if ( $InClass )   return self::fail($Value, "Zeichenklasse [ ohne ]");
    if ( $Depth > 0 ) return self::fail($Value, "Klammer ( ohne )");

    $Res["pcre"]   = "\x01^(?:" . $Pcre . ")" . ($Res["end"] ? "$" : "") . "\x01uD";
    $Res["lucene"] = $Luc;

    
    set_error_handler(function() { return true; });
    $Ok = preg_match($Res["pcre"], "");
    restore_error_handler();
    if ( $Ok === false )  return self::fail($Value, "ungueltiger Ausdruck");

    return self::$Cache[$Value] = $Res;
  }

  private static function fail($Value, $Reason)
  {
    return self::$Cache[$Value] = array("pcre" => "", "lucene" => "", "end" => false, "error" => $Reason);
  }
}
