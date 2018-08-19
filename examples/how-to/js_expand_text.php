<?php
define("STDROOT","../../");
include STDROOT."std.php";
?>
<script src="<?php echo W_ROOT_JQUERY; ?>"></script>

<script>
$(document).ready(function(){

    $('.expand-div').click( function() {
        var pos = $(this).find('img').css('left');
        if(pos == '0px') {
            $(this).find('img').css('left', '-14px');
            $(this).find('.expandable-text').slideDown(500);
        } else {
            $(this).find('img').css('left', '0px');
            $(this).find('.expandable-text').slideUp(500);
        }
    });
});
</script>

<style>
.expand-title {
    float: left;
    padding-right: 5px;
    font-size: 14px;
    font-family: Helvetica, Arial, sans-serif;
}
.expand-button {
    height: 14px;
    overflow: hidden;
    padding-bottom: 20px;
    position: relative;
    top: 15px;
    width: 14px;
}
.expand-button img {
    left: 0;
    position: absolute;
}
.expandable-text {
    font-family: helvetica, arial, sans-serif;
    color: #6C6B6B;
    font-size: 10pt;
    line-height: 15pt;
}
</style>


<div class='expand-div'>
<h2 class='expand-title'>Expand me:</h2>
<div class='expand-button'><img src='js_expand_text_button.gif'/></div>
<p class='expandable-text' style='display:none'>
This is the expanded text.
</p>
</div>

<P style='clear:both'>&nbsp;</P>

<div class='expand-div'>
<h2 class='expand-title'>Expand me too:</h2>
<div class='expand-button'><img src='js_expand_text_button.gif'/></div>
<p class='expandable-text' style='display:none'>
This is the other expanded text.
</p>
</div>