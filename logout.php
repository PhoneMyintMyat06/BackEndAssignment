<?php
session_start();
session_destroy();
header("Location: index.php");
?>

<!-- PHPကို sessionကိုအသုံးပြုမယ်လို့စတင်ပြောတာ မခေါ်ရင် session data ကို access မရ
session_start(), session ကိုဖွင့်
session_destroy(), login data ဖျက်
header() → page ပြန်ပို့ -->