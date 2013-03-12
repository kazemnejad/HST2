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

if (empty($GLOBALS["www_has_phpcrawler"])) {
   // ***** SEARCH ******
   
   $GLOBALS["www_has_phpcrawler"] = 1;
   
   function getResults($result, $q_hash) {
      global $CRAWL_CHARS_PER_WORD, $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH, $CRAWL_SEARCH_TEXT_BOLD_QUERY_WORD, $CRAWL_SEARCH_CONTENT_SIZE;
      
      $results = array();
      
      $q = str_split($q_hash, $CRAWL_CHARS_PER_WORD);
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
         $comparepage = str_split($row["content"], $CRAWL_CHARS_PER_WORD);
         $addRow = false;
         $keys = array();
         foreach ($q as $q_pair) {
            // getSimilarWords() Implements Levenshtein distance with actual words elsewhere
            $key = array_keys($comparepage, $q_pair);
            if (count($key) > 0) {
               // Found
               foreach ($key as $keyFrame) {
                  $keys[] = $keyFrame;
               }
               $addRow = true;
               // echo hashToText($q_pair);
               // print_r($keys);
            }
         }
         sort($keys);
         $i = 0;
         $usedKeys = array();
         $txtBlock = "";
         $looped = false;
         // try to make the following loop more efficient!
         for ($j = 0; $j < count($keys) && $j < $CRAWL_SEARCH_CONTENT_SIZE; $j += 1 + floor(20.0 / $CRAWL_SEARCH_CONTENT_SIZE)) {
            for ($m = $keys[$j] - $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH; $m <= $keys[$j] + $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH; $m ++) {
               if (isset($comparepage[$m])) {
                  $txtBlock .= hashToText($comparepage[$m], true).' ';
               }
            }
            $txtBlock .= "... ";
            $looped = true;
         }
         if ($looped)
            $txtBlock = substr($txtBlock, 0, strlen($txtBlock) - 4);
         /*
         foreach ($keys as $foundKey) {
            if (!in_array($foundKey, $usedKeys)) {
               $hashBlock = "";
               $m = 0;
               for ($j = $foundKey - $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH; $j <= $foundKey + $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH; $j ++) {
                  if (isset($comparepage[$j])) {
                     if ($m >= $CRAWL_SEARCH_CONTENT_SIZE) {
                        break;
                     }
                     $hashBlock .= $comparepage[$j];
                     $usedKeys[] = $j;
                     $m ++;
                  }
               }
               if ($i != 0) {
                  $txtBlock .= " ... ";
               }
               $txtBlock .= hashToText($hashBlock, true);
            }
            $i ++;
         }*/
         if ($addRow) {
            $results[count($keys)] = array("url_title" => (empty($row["url_title"])?$row["url"]:$row["url_title"]), "fmt_result" => $txtBlock, "url" => $row["url"], "last_crawled" => $row["last_crawled"]);
         }
      }
      
      krsort($results, SORT_DESC);
      return $results;
   }
   
   function searchOldFormatContent($content, $q) {
      global $CRAWL_SEARCH_TEXT_SURROUNDING_LENGHT, $CRAWL_SEARCH_MAX_RES_WORD_COUNT;
      $CRAWL_SEARCH_STRICT_RESULTS = false;
      
      // we shall use smaller alias ;-)
      $SL = $CRAWL_SEARCH_TEXT_SURROUNDING_LENGHT;
      
      if (empty($SL)) die("Empty CRAWL_SEARCH_TEXT_SURROUNDING_LENGHT");
      
      // remove some spaces from content
      $content = preg_replace("/(&nbsp;)+/i", " ", $content);
      $content = preg_replace("/\s[\s]+/ims", " ", $content);
      
      // === Creating chunks
      $chunks = array();
      $chunk_counter = 0;
      $words = preg_split ("/\s+/i", $q);
      foreach ($words as $dummy_id => $word) {
         if (empty($word)) continue;
         if (strlen($word) < 3) continue;
         $word_counter = 0;
         if ($CRAWL_SEARCH_STRICT_RESULTS) {
            /* Uncomment this to speed-up search
              $found = preg_match_all("/\s+" . $word . "\s+(.{0," . $SL . "})/ims", $content, $matches, PREG_SET_ORDER);
             */
            $found = preg_match_all("/(.{0," . $SL . "})\s+" . $word . "\s+(.{0," . $SL . "})/ims", $content, $matches, PREG_SET_ORDER);
         } else {
            /* Uncomment this to speed-up search
              $found = preg_match_all("/" . $word . "(.{0," . $SL . "})/ims", $content, $matches, PREG_SET_ORDER);
             */
           $found = preg_match_all("/(.{0," . $SL . "})" . $word . "(.{0," . $SL . "})/ims", $content, $matches, PREG_SET_ORDER);
         }
         if ($found == 0 || $found === false) continue;
         foreach($matches as $dummy => $match) {
            $chunks[$chunk_counter] = $match[0];
            $chunk_counter++;
            $word_counter++;
            if ($word_counter >= $CRAWL_SEARCH_MAX_RES_WORD_COUNT) break;
         }
      }
      
      // if no matches found
      if (count($chunks) == 0) {
         return substr($content, 0, $CONFIG_TEXT_SURROUNDING_LENGHT * 2);
      }
      
      // setting up positions
      $postitions = array();
      $chunk_counter = 0;
      foreach ($chunks as $dummy_id => $chunk) {
         if (empty($word)) continue;
         $chunk_pos = strpos($content, $chunk);
         //$chunk_pos = strpos($content, $word, 0);
         //$word_pos = preg_match("/{$word}/ims", $content, $matches);
         if ($chunk_pos === false) continue;
         $positions[$chunk] = $chunk_pos;
      }
      asort($positions, SORT_NUMERIC);
      
      //computing text marks
      $marks = array();
      $chunk_counter = 0;
      $last_chunk_end = 0;
      $content_len = strlen($content);
      foreach ($positions as $chunk => $text_pos) {
         $chunk_len = strlen($chunk);
         if ($chunk_len < 3) continue;
         if ($text_pos === false) continue;
         
         // *** check chunks overlapping
         if (($text_pos) < $last_chunk_end) {
            $marks[$chunk_counter]["end"] = (($text_pos + $chunk_len) > $content_len) ? $content_len : $text_pos + $chunk_len;
         }
         else {
            $marks[$chunk_counter]["from"] = (($text_pos) < 0) ? 0 : $text_pos;
            $marks[$chunk_counter]["end"] = (($text_pos + $chunk_len) > $content_len) ? $content_len : $text_pos + $chunk_len;
            $chunk_counter++;
         }
      }
      
      // *** making content
      $shown_result = "";
      foreach($marks as $chuck_id => $mark) {
         //var_dump($mark); die("stop");
         $text_chunk  = substr ( $content, $mark["from"], $mark["end"] - $mark["from"]);
         $text_chunk  = preg_replace("/^[^\s]*\s/i", "", $text_chunk);
         $text_chunk  = preg_replace("/\s[\S]*$/is", "", $text_chunk);
         $shown_result .= "..." . $text_chunk . "...  ";
      }
      
      foreach ($words as $dummy_id => $word) {
         if (strlen($word) < 3) continue;
         if ($CRAWL_SEARCH_STRICT_RESULTS) {
            $shown_result = preg_replace ("/\s+{$word}\s+/ims", "<b>\\0</b>", $shown_result);
         }
         else {
            $shown_result = preg_replace ("/{$word}/ims", "<b>\\0</b>", $shown_result);
         }
      }
      return $shown_result;
   }
   
   function getSimilarWords($content) {
      // Could implement Levenshtein distance with actual words here
      $words = processContent($content);
      $suggestions = array();
      foreach ($words as $word) {
         $result = sql_query("SELECT * FROM `words`");
         $shortest = -1;
         while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            // could use similar_text, but doing so is less efficient
            $lev = levenshtein($word, $row["word"]);
            // check for an exact match
            
            if ($lev == 0) {
               $closest = $word;
               $shortest = 0;
               // break out of the loop; we've found an exact match
               break;
            }
            
            // if this distance is less than the next found shortest
            // distance, OR if a next shortest word has not yet been found
            if ($lev <= $shortest || $shortest < 0) {
               // set the closest match, and shortest distance
               $closest  = $row["word"];
               $shortest = $lev;
            }
         }
         if ($closest != $word) {
            $suggestions[$word] = $closest;
         }
      }
      return $suggestions;
   }
   
   function prepareQuery($content) {
      // Content separated by single spaces
      // Ensure case-insensitive string comparisons
      
      $words = pageWords($content);
      // $words = pageWords(sql_escape(preg_replace("/[^a-zA-Z0-9s]/", "", $content)));
      $hash = '';
      foreach ($words as $word) {
         // $hash .= ord(substr($word,0,1)) . ord(substr($word,1,1)) . " ";
         $hash .= $word; // . " "; also uncomment substr line below
      }
      // $hash = substr($hash, 0, strlen($hash) - 1); also uncomment . " " above
      
      return $hash;
   }
   
   /**
    * Convert character (pairs) to actual words separated by spaces
    */
   function hashToText($content, $boldMiddle = false) {
      global $CRAWL_CHARS_PER_WORD, $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH;
      $pairs = str_split($content, $CRAWL_CHARS_PER_WORD);
      $text = "";
      $i = 0;
      foreach ($pairs as $pair) {
         $num = toDecimal($pair);
         $word = sql_fetch("SELECT word FROM `words` WHERE id=$num");
         $i ++;
         if ($boldMiddle && $i == $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH + 1) {
            $text .= "<b>";
         }
         $text .= $word;
         if ($boldMiddle && $i == $CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH + 1) {
            $text .= "</b>";
         }
         if ($i != count($pairs)) {
            $text .= " ";
         }
         else {
         }
      }
      return $text;
   }
   
   function searchFormatContent($hashContent, $q) {
      $content = hashToText($hashContent);
      $content = str_replace($q, "<b>".$q."</b>", $content);
      return $content;
   }
   
   // *** INIT SQL ***
   /* 'l' is alias in 'SELECT * FROM phpcrawler_links l' */

/* $sql_content_q = <<<
SELECT 
   *  
FROM 
   phpcrawler_links l 
WHERE 
   MATCH (l.content) AGAINST (%s) 
GROUP BY l.id 
LIMIT %d, %d
*/
   $sql_content_q = <<<MARVIN
SELECT * FROM `phpcrawler_links` l
MARVIN;
/* $sql_content_q_count = <<<
SELECT 
   count(distinct l.id) as cnt 
FROM 
   phpcrawler_links l 
WHERE 
   MATCH (l.content) AGAINST (%s)
*/
   $sql_content_q_count = <<<AURANGZEBMOGULAMORON
SELECT 
   count(distinct l.id) as cnt 
FROM 
   phpcrawler_links l 
AURANGZEBMOGULAMORON;
}

$crawldb = sql_open();

?>