
<DOCTYPE html> 
<html lang="en"> 
<head> 
 <meta charset="UTF-8"> 
 <title>loading ...</title> 
</head> 
<body onload="document.MyForm.submit();"> 
 <form name="MyForm" action="<?php echo $url; ?>" method="post"> 
 <?php 
 foreach($paramEncrypt as $key=>$val){ 

 echo '<input type="hidden" name="'.$key.'" value="'.$val.'" />'; 
 } 
 ?> 
</form> 
</body> 
</html>