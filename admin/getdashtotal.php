<?php

for($i=1;$i<130;$i++){
    $data = file_get_contents("https://subtoshi.com:8084/data?method=getBalance&coin=dsh&user_id=".$i);
}

?>