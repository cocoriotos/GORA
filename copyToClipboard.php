<?php 
session_cache_limiter('nocache');
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
$linktoclipboard = $_SESSION['linktoclipboard'];
?>

<script>
function copyToClipboard(text) {
    const tempTextarea = document.createElement("textarea");
    tempTextarea.value = text;
    document.body.appendChild(tempTextarea);
    tempTextarea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextarea);
}
</script>
<?php
$_SESSION['linktoclipboard']=1;
?>