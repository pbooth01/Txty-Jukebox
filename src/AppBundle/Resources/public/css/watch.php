<?php

while(1){ 

if( filemtime("jukebox.less") > filemtime("jukebox.css") ){
  exec("lessc jukebox.less > jukebox.css");
  echo "Rebuilding....\n";
}

sleep(1);

}
