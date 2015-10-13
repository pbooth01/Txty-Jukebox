<?php

while(1){ 

if( filemtime("styles.less") > filemtime("styles.css") ){
  exec("lessc styles.less > styles.css");
  echo "Rebuilding....\n";
}

sleep(1);

}
