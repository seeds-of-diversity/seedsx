<?

// "Anyone can register"
// "We just sent a confirmation email to a@b.c"  Please click on the link in the email to confirm your subscription.

/*
CREATE TABLE bull_list (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

--  id      INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email   VARCHAR(200) NOT NULL,
    name    VARCHAR(200) NOT NULL,
    hash    VARCHAR(200),
    status  INTEGER DEFAULT 0,
    ts0     DATETIME,
    ts1     DATETIME,
    ts2     DATETIME,
    lang    ENUM('','E','F','B') DEFAULT '',
    comment TEXT,

    INDEX (email)
);
*/


function bull_page_header()
/**************************
 */
{
    echo "<TABLE width='".ALT_PAGE_WIDTH."' align=center>"
         ."<TR><TD>"
         ."<SPAN style='font-family:Times Roman;font-size:18pt;color:green'>Seeds of Diversity's Email Bulletin</SPAN><BR/>"
         ."<FONT size='+2' color='green'>Seeds of Diversity's Email Bulletin</FONT>"
         ."<BR><FONT color='green'>".SEEDStd_StrNBSP("",6)." <SPAN style='font-size:13pt'>keeps you up-to-date</SPAN> on news and events about seeds, biodiversity and gardening in Canada.</FONT></P>"
         ."<HR width='75%'>";
}


function bull_page_footer()
/**************************
 */
{
    echo "</TD></TR></TABLE>";
    std_footer();
    echo "</BODY></HTML>";
}


function bull_hash()
/*******************
    return a random string
 */
{
    return( substr( md5( time() ), 0, 10 ) );
}

?>
