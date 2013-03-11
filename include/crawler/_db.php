<?php
/**
 * <h1>PHP Crawler</h1>
 * @author Vladimir Fedorkov, Doug Martin, and Sumit Dutta
 * @version 0.8 2007-05-04
 * @link http://astellar.com/
 * @copyright 2005-2007
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

// *** SQL WRAPPER - MYSQL ***
if (empty($GLOBALS["www_has_db"])) {
   $GLOBALS["www_has_db"] = 1;
   
   function sql_escape($arg) {
      // return addslashes($arg);
      return mysql_real_escape_string($arg);
   }
   
   function sql_open() {
      global $s_op, $M_SYS_SQL_SERVER, $M_SYS_SQL_DB, $M_SYS_REASON;
		
      if (!($crawldb = mysql_connect($s_op['mysql_host'], $s_op['mysql_user'], $s_op['mysql_pass']))) {
         $msg = mysql_error();
         die("Cannot connect to database server (Reason: $msg)");
      }
      if (!mysql_select_db($s_op['mysql_db'], $crawldb)) {
         $msg = mysql_error();
         die("Cannot select db (Reason: $msg)");
      }
      return $crawldb;
   }
   
   function sql_exec_va($args) {
      global $sql_query, $crawldb;
      
      $query = $args[0];
      $i = 1;
      $n = count($args);
      
      $a = explode("%", $query);
      $r = "";
      if (!empty($a)) foreach ($a as $p) {
         $c = $p[0];
         if ($c != "s" && $c != "u" && $c != "d" && $c != "f") {
            $r .= "%";
            if ($c == "P") $p = substr($p, 1);
            $r .= $p;
            continue;
         }
         if ($i >= $n) die("FATAL: not enough arguments to SQL query ($query_code: $query)");
         $arg = $args[$i++];
         switch ($c) {
         case "s": $r .= "'" . sql_escape($arg) . "'"; break;
			case "u": $r .= $arg; break;
			case "d": $r .= (int)$arg; break;
			case "f": $r .= (float)$arg; break;
         }
         $r .= substr($p, 1);
      }
      $query = substr($r, 1);
      
      $sql_query = $query;
      if (ECHO_QUERIES) {
      	  $bt = debug_backtrace();
      	  $bt_str = '';
      	  foreach (array_reverse($bt) as $e) {
      	  	$line = $e['line'];
      	  	$function = $e['function'];
      	  	if ($bt_str != '')
      	  		$bt_str .= '->';
      	  	$bt_str .= $function.'@'.$line;
      	  }
	      crawler_LOG("QUERY: [".$bt_str."] ".$query);
      }
      return @mysql_query($query, $crawldb);
   }
   
   function sql_query_va($args) {
      global $sql_query;
      
      if (!($r = sql_exec_va($args))) {
         $msg = mysql_error();
         die("Query failed (query: $sql_query, reason: $msg)");
      }
      return $r;
   }
   
   function sql_query($query) {
      $args = func_get_args();
      return sql_query_va($args);
   }
      
   function sql_exec($query) {
	   $args = func_get_args();	return sql_exec_va($args);
   }
   
   function sql_row($result) {
      return mysql_fetch_row($result);
   }
   function sql_rows($result) {
      return mysql_num_rows($result);
   }
   
   function sql_fetch($query) {
      $args = func_get_args();
      $r = sql_query_va($args);
      $a = sql_row($r);
      return $a[0];
   }
   
   function sql_row_hash($result) {
      return mysql_fetch_array($result);
   }
   
   function sql_fetch_hash($query) {
      $args = func_get_args();
      $r = sql_query_va($args);
      return sql_row_hash($r);
   }
   
   function sql_insert($query) {
      $args = func_get_args();
      sql_query_va($args);
      return sql_insert_id();
   }
   
   function sql_insert_id() {
      return mysql_insert_id();
   }
   
   /**
    * Adds a word if it is not already in table 'word'
    */
   function sql_add_word($word) {
      // Already connected to DB
      $query = "SELECT word FROM `words`";
      
   }
   
   /**
    * Gets word from table 'word' given integer
    */
   function sql_fetch_word($key) {
      // Already connected to DB
      $query = "SELECT word FROM `words` WHERE id=$key";
      sql_query($query); // or die(mysql_error());
      
   }
   
   /**
    * Gets integer key from table 'word' given a word
    */
   function sql_fetch_key($word) {
      // Already connected to DB
      
   }
   
   function sql_free($r) {
      return mysql_free_result($r);
   }
   
   /* Doug hash-text functions */
   /* What happens:
   
   Search:
   User search query -> query hash -> match with each page's hash -> return pages
   
   Crawl:
   Each page's content parsed -> content hash (words table populated meanwhile) -> hash put in database
   
   Word to hash:
   "word" assigned integer -> convert integer to base 2 (binary) -> 
   
   store up to 2^16 = 65,536 words this way
   
   */
   
   // Splits strings into arrays of pairs of characters.
   function stringSplit($str) {
      $str_array=array();
      $len=strlen($str);
      for($i=0;$i<$len;$i++) $str_array[]=$str{$i};
      $array_array=array();
      for($j=0;$j<sizeof($str_array);$j=$j+2) $array_array[]= array($str_array[$j],$str_array[$j+1]);
      return $array_array;
   }
   
   // Converts a string of [$CRAWL_CHARS_PER_WORD (usually 2)] characters into a decimal number.
   function toDecimal($str) {
      global $CRAWL_CHARS_PER_WORD;
      $numArray = array();
      for ($i = 0; $i < $CRAWL_CHARS_PER_WORD; $i ++) {
         $numArray[$i] = decbin(ord(substr($str, $i, 1)));
         $numArray[$i] = str_pad($numArray[$i], 8, "0", STR_PAD_LEFT);
      }
      $num = bindec(implode($numArray));
      return $num;
   }
   
   // Calls toDecimal() for the given array.
   function decimalize($str) {
      $stringArray = stringSplit($str);
      for($i=0;$i<sizeof($stringArray);$i++) $numArray[]=toDecimal($stringArray[$i]);
      return $numArray;
   }
   
   // Converts a decimal number into a (pair) of [$CRAWL_CHARS_PER_WORD] characters.
   function fromDecimal($dec) {
      global $CRAWL_CHARS_PER_WORD;
      $bigBin = decbin($dec);
      $bigBin = str_pad($bigBin, 8 * $CRAWL_CHARS_PER_WORD, "0", STR_PAD_LEFT); // binary representation up to (8 * $CRAWL_CHARS_PER_WORD) digits
      // corresponding 2 ASCII characters from 0 to 255 -> binary (8 * $CRAWL_CHARS_PER_WORD) digits
      // usually 16 digits in binary
      $len = strlen($bigBin) / $CRAWL_CHARS_PER_WORD;
      $binArray = array();
      for ($i = 0; $i < $CRAWL_CHARS_PER_WORD; $i ++) {
         $binArray[$i] = substr($bigBin, ($i) * 8, $len);
         $binArray[$i] = bindec($binArray[$i]);
         $binArray[$i] = chr($binArray[$i]);
      }
      $str = implode($binArray);
      return $str;
   }
   
   // Calls fromDecimal() for the given array.
   function unDecimalize($decs) {
      for($i=0;$i<sizeof($decs);$i++) $strArray[]=fromDecimal($decs[$i]);
      return $strArray;
   }
   
   function prepareHash($content) {
      // Content separated by single spaces
      // Ensure case-insensitive string comparisons
      
      $words = pageWords($content, true);
      $hash = '';
      foreach ($words as $word) {
         $hash .= $word;
      }
      
      return $hash;
   }
   
   // puts words into DB, returns array of character pairs
   function pageWords($content, $insertWords = false) {
      $hashPairs = array();
      $words = processContent($content);
      foreach ($words as $word) {
         $word = sql_escape($word);
         $num = sql_fetch("SELECT COUNT(*) FROM `words` WHERE word='$word'");
         $id = 0;
         if ($num == 0) {
            $result = sql_query("SELECT * FROM `words`");
            $prevId = -1;
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
               $nextId = $row["id"];
               if ($id < $nextId && $id > $prevId) {
                  break;
               }
               $prevId = $nextId;
               $id ++;
            }
            if ($insertWords) {
               sql_query("INSERT INTO `words` (id, word) VALUES ($id, '$word')");
            }
            else {
               $id = -1;
            }
         }
         else {
            // assign $decIndex from table
            $id = sql_fetch("SELECT id FROM `words` WHERE word='$word'");
         }
         if ($id != -1) {
            $hashPairs[] = fromDecimal($id); // id is a [decimal]
         }
      }
      return $hashPairs;
   }
   
   /**
    * Gets simplified words in an array from content
    */
   function processContent($content) {
      $wordsTmp = explode(' ', str_replace(array('(', ')', '[', ']', '{', '}', "'", '"', ':', ',', '.', '?'), ' ', $content));
      $words = array();
      // prioritize: skipped
      // remove conjunctions/prepositions if needed
      $omit = array('and', 'or', 'but', 'yet', 'for', 'not', 'so', '&', '&amp;', '+', '=', '-', '*', '/', '^', '_', '\\', '|');
      foreach ($wordsTmp as $wordTmp) {
         $wordTmp = trim($wordTmp);
         while (substr($wordTmp, strlen($wordTmp) - 1) == ".") {
            $wordTmp = substr($wordTmp, 0, strlen($wordTmp) - 2);
         }
         while (substr($wordTmp, 0, 1) == ".") {
            $wordTmp = substr($wordTmp, 1);
         }
         $wordTmp = strtolower($wordTmp);
         if (!empty($wordTmp) && !in_array($wordTmp, $omit)) {
            $words[] = $wordTmp;
         }
      }
      return $words;
   }
   
   if (!function_exists('str_split')){
      function str_split($string,$split_length=1) {
         $count = strlen($string);
         
         if ($split_length < 1) {
            //      return false if split length is less than 1
            //      to mimic php 5 behavior
            return false;
         }
         elseif ($split_length > $count) {
            //      the entire string becomes a single element
            //      in an array
            return array($string);
         }
         else {
            //      split the string at desired length
            $num = (int)ceil($count/$split_length);
            $ret = array();
            for ($i=0;$i<$num;$i++) {
               $ret[] = substr($string,$i*$split_length,$split_length);
            }
            return $ret;
         }
      }
   }
   
   $CONNECT_TO_DB = true;
}

?>