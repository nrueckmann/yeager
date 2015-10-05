<?php

function sanitize($string) {
	return rtrim(ltrim(addslashes((strip_tags(nl2br($string))))));
}

function generatePassword ($length = 8)
{

  // start with a blank password
  $password = "";

  // define possible characters
  $possible = "0123456789bcdfghjkmnpqrstvwxyz";

  // set up a counter
  $i = 0;

  // add random characters to $password until $length is reached
  while ($i < $length) {

    // pick a random character from the possible ones
    $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

    // we don't want this character if it's already in the password
    if (!strstr($password, $char)) {
      $password .= $char;
      $i++;
    }

  }

  // done!
  return $password;

}


function genPassword()
{


		return generatePassword(8);
      #********** Set of words ****************

       $word_arr_one = array ("rose","pink","blue","cyan","gold", "lime","silk","slim",
                             "walk","warm","zoom","high","hell","posh","face","hand",
                             "dose","cool","club","clue","baby","body","auto","acid");

       $word_arr_two = array("aunt","love","girl","time","door","disc","book","news",
                             "cock","bond","bomb","joke","tall","tank","drum","hill",
                             "hand","cook","look","mate","main","pack","page","palm");



      $arr_one_len=sizeof($word_arr_one);
      $arr_two_len=sizeof($word_arr_two);

      #********* Randomize on microseconds ********

      mt_srand ((double)microtime()*1000000);

      #**********************************************************
      #  Construct a string by picking up 8 words in random
      #  from first array of words
      #  Add word at start if pick up word is at even position
      #  otherwise add at end of the string
      #************************************************************

      for($i=0; $i<10; $i++)
      {
         $pos_one=mt_rand(0, ($arr_one_len-1));
         if($pos_one % 2 == 0 )
         {
              $pwd_one = $word_arr_one[$pos_one].$pwd_one;
         }
         else
         {
              $pwd_one.= $word_arr_one[$pos_one];
         }
      }


      #**********************************************************
      #  Construct a string by picking up 8 words in random
      #  from second array of words
      #  Add word at end if pick up word is at even position
      #  otherwise add at start of the string
      #************************************************************

      for($i=0; $i<10; $i++)
      {
         $pos_two=mt_rand(0, ($arr_two_len-1));
         if($pos_two % 2 == 0 )
         {
           $pwd_two.= $word_arr_two[$pos_two];
         }
         else
         {
           $pwd_two = $word_arr_two[$pos_two].$pwd_two;
         }
      }

     #********* pick up a random number between 1 and 9 ***********

     $rnd_int=mt_rand(2,9);

     #******************************************************************************
     # Now to generate password
     # pick up first word(4 letters) from first string(constructed from array one)
     #          +
     #  number you picked up in random
     #          +
     # pich up first word(4 letters) from second string(constructed from array two)
     #******************************************************************************


     $pwd=substr($pwd_one,0,4).$rnd_int.substr($pwd_two,0,4);


     return $pwd;
}

?>