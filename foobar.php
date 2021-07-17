<?php
for ($x =1; $x<=100; $x++){
    if($x% 15 ==0 ){echo "foobar\n";}
    else
    if($x% 5 ==0 ){echo "bar\t";}
    else
    if($x% 3 ==0 ){echo "foo\t";}
    else{
    echo $x."\t";}

}
?>