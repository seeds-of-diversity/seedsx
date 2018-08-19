<?
define( SITEROOT, "../../" );
include( SITEROOT."site.php" );
include( STDINC."SEEDWiki.php" );
include( SEEDCOMMON."siteutil.php" );   // MailFromOffice

$verdana = "font-family:verdana,arial,helvetica,sans serif";


$parser = new SEEDWikiParser();

$n = SEEDSafeGPC_GetInt('n');
$name = SEEDSafeGPC_GetStrPlain('name');
$text = SEEDSafeGPC_GetStrPlain('text');
$nStyle = SEEDSafeGPC_GetInt('style');


switch( SEEDSafeGPC_GetStrPlain('action') ) {
    case 'Start':
        if( $name )  $n = 1;
        break;
    case 'Next Lesson':
        ++$n;
        break;
    case 'Format':
        break;
    case 'Reference':
        draw_Reference( $parser );
        exit;
        break;
    default:
        $n = 0;
}


$lesson = array(
array( 'title' => 'Introduction',
       'instructions' => "<P>Seeds of Diversity has many people who enter information on different parts of our web site. We want to make our work as "
                        ."easy as possible, but we also want our posts to look interesting.  We want colour, indentation, bold text, headers, links, and so on. "
                        ."The problem is, formatting is a tricky thing to "
                        ."do on a web site, because the language of the web (HTML) works differently than the word documents that we're normally used to.</P>"
                        ."<P>Sometimes we use a full-format online editor, which lets you use different fonts, boldface, links, and pictures. "
                        ."It's a very easy tool to learn because it looks like the office programs that we already know.  Unfortunately, it doesn't always do what you expect "
                        ."and it's especially difficult to make several posts look alike, particularly when more than one person is entering them.</P>"
                        ."<P>Another solution is 'Wiki' formatting, which you're going to learn here.  Wiki is a simple way to format web pages "
                        ."that keeps you focused on what you want to say, not how the words should look.  Literally thousands of people enter information "
                        ."into Wikipedia every day, and its formatting stays perfectly consistent. This is a tried and true user-friendly way to make "
                        ."web pages, but you have to learn a few simple techniques.  When you're done, you'll be able to write Wikipedia pages too!</P>"
                        ."<P>So let's try a few examples, and you'll get the idea. Type your name here, click the Start button, and we'll begin.</P>" ),
// 1
array( 'id' => "INTRO",
       'title' => 'Paragraphs',
       'instructions' => "<P>Hi $name!</P><P>First, let's take a tour of the screen. You'll type your 'Wiki' text in the white box to the left. "
                        ."At any time, click 'Format' to display your text in the box below it at bottom-left. That will show you what your text would look like "
                        ."on a real web site.</P><P>You can change your wiki text and click Format as many times as you want. Then, when you're finished "
                        ."with this lesson, click 'Next Lesson' below to move on.</P>"
                        ."<P>There are two links at the top of the page.  The first one starts the lessons at the beginning, and the second opens a "
                        ."complete reference chart of all the Wiki formatting codes.  That will be useful later. For now, let's get started with the lessons.</P>"
                        ."<HR width=80%><P>The first thing you'll do is to make a few paragraphs. This is easy and natural. Type the following into the white "
                        ."box to the left. Don't indent the sentences, and make the spacing just like you see it below. "
                        ."<FONT size=-1 color=green>To save time, copy and paste it!</FONT>"
                        ."<DIV class='xmpType'>Conference Announcement<BR><BR>Saskatoon, SK<BR><BR>See the flowers.<BR>Smell the roses!<BR><BR>"
                        ."You can buy<BR>flowers,<BR>plants,<BR>and seeds!<BR><BR>To register<BR>go to the web site<BR>enter your name<BR>pay the registration.</DIV>"
                        ."<P>Click Format, and see the text appear at the bottom.</P><P>What happened?<BR>Wiki makes a new paragraph wherever there's a blank line. "
                        ."Notice how the adjacent lines were joined up into sentences and paragraphs. We'll format those as lists later.</P>"
                        ."<P>Keep your Wiki text the way it is, and click Next Lesson.</P>" ),

// 2
array( 'title' => 'Headings',
       'instructions' => "<P>This conference announcement needs more exciting headings. Wiki has an easy way to make headings, in lots of sizes. "
                        ."Change the first two lines to look like this (using equal signs '='):"
                        ."<DIV class='xmpType'>=Conference Announcement=<BR><BR>===Saskatoon, SK===</DIV>"
                        ."<P>Click Format to see the difference.</P>"
                        ."<P>Wiki makes headings when there are equal signs '=' at the start and end of a line. One equal sign is the biggest heading size, "
                        ."more equal signs make smaller headings.  You should put the same number at the start and end.</P>"
                        ."<P>Try using different numbers of equal signs, and click Format to see what they do. When you're finished, click Next Lesson.</P>" ),

// 3
array( 'title' => 'Emphasis',
       'instructions' => "<P>You can use Wiki formatting to make <B>boldface</B> and <I>italic</I> text too.  Just like headings, you put special characters "
                        ."around the words that you want to emphasize.  This time, it's the apostrophe (').</P>"
                        ."<P>Back in your Wiki text, make it clear that 'You' means You! Put double apostrophes around that word."
                        ."<DIV class='xmpType'>''You'' can buy</DIV>"
                        ."<P>Also, make sure they know that they have to register.  Put triple apostrophes on that word."
                        ."<DIV class='xmpType'>To '''register'''</DIV>"
                        ."<P>Click Format to see the difference.</P>"
                        ."<P>In this example, two apostrophes make the word <I>italicized</I>, and three make it <B>boldface</B>. "
                        ."The actual effect depends on the way the Wiki is set up, so it might be different sometimes, but this is the usual formatting.</P>"
                        ."<P>Try using different numbers of apostrophes, and click Format to see what they do. When you're finished, click Next Lesson.</P>"
                        ."<P>By the way, why don't single apostrophes do anything?  Because they're used in normal writing as quotations. \"'Isn't that so?', I ask?\"</P>" ),

// 4
array( 'title' => 'Lists',
       'instructions' => "<P>Now let's make those lists look right. We'll put 'flowers, plants, and seeds' into a bullet-point list, and we'll put "
                        ."the registration steps into a numbered list.</P>"
                        ."<P>Wiki has different kinds of lists, and you make them by putting special characters at the beginning of each line. Start with "
                        ."the bullet-point list by putting asterisks (*) at the beginning of the lines.</P>"
                        ."<DIV class='xmpType'>* flowers,<BR>* plants,<BR>* and seeds!</DIV>"
                        ."<P>Then make a numbered list by putting the number sign (#) at the beginning of the lines.</P>"
                        ."<DIV class='xmpType'># go to the web site<BR># enter your name<BR># pay the registration.</DIV>"
                        ."<P>Click Format to see the difference.</P>"
                        ."<P>Wiki makes a new list for each group of lines.  If you put a blank line between '# enter your name' and '# pay the registration', "
                        ."and click Format again, you'll see where the new numbered list starts.</P>"
                        ."<P>Experiment with lists, even combining asterisks and number signs (put ** or *# at the beginning of a line) and see what happens.</P>"


        ),

// 5
array( 'title' => 'Links',
       'instructions' => "<P>The web is all about links, so it's pretty boring to have a web page that doesn't link anywhere. Wiki has several ways "
                        ."to link to other pages, other web sites, and even email links. For now, we'll just make one link here.</P>"
                        ."<P>The registration instructions shouldn't just say 'go to the web site'. They should actually link to the web site to make it easy. "
                        ."Use copy and paste to replace that line with the following.</P>"
                        ."<DIV class='xmpType'># go to the [[http://www.seeds.ca | new | web site]]</DIV>"
                        ."<P>Click Format to see the difference.</P>"
                        ."<P>You can click on that link now, in the bottom-left box, and you'll see Seeds of Diversity's web site in a new window!</P>"
                        ."<P>Seems complicated?  Let's break it apart.  The link enclosed in double square brackets [[ ]] has three parts, the link "
                        ."destination, the destination window (new), and the text that goes on the screen (web site).</P>"
                        ."<P>You can try different kinds of links, but if you link away from this page try to find your way back!</P>"
                        ."<DIV class='xmpType'>This makes a link using the current window<BR>[[http://www.seeds.ca | | web site]]<BR><BR>"
                        ."This makes a link using the same window, and shows the web address<BR>[[http://www.seeds.ca]]<BR><BR>"
                        ."This makes an email link<BR>[[mailto:test@example.com | send me an email]]</DIV>"
                        ."<P>There are several combinations, all listed in the Wiki Reference Chart (see the link at the top of the page).</P>" ),

// 6
array( 'title' => 'Images',
       'instructions' => "<P>What would a web site be without pictures?  Wiki lets you put pictures on the page, using a similar method to links. "
                        ."We're going to put a maple leaf in the corner of our conference announcement.</P>"
                        ."<P>Copy and paste this line to the top of the wiki text, so the 'Conference Announcement' heading will now be the second line.</P>"
                        ."<DIV class='xmpType'>[[Image: http://www.seeds.ca/img/canleaf.gif]]</DIV>"
                        ."<P>Click Format.  There's a maple leaf!  The Image link tells Wiki to fetch it from the given web address (on our web site). "
                        ."Once you get the hang of this, you'll learn about easier ways to name images.  For now, we'll just use this one.</P>"
                        ."<P>Wouldn't it look better on the right?  Try this instead.</P>"
                        ."<DIV class='xmpType'>[[Image: http://www.seeds.ca/img/canleaf.gif | right]]</DIV>"
                        ."<P>Much better. When you look at the Wiki Reference Chart, you'll see lots of different ways to position and adjust images. "
                        ."You can adjust their sizes, and even put captions on them.  Though the example below looks complicated, try it to see what it does. "
                        ."<FONT size=-1 color=green>Copy and paste it to get it exactly right. It has to be on a single line in the wiki text.</FONT></P>"
                        ."<DIV class='xmpType'>[[Image: http://www.seeds.ca/img/canleaf.gif | frame right {height=60} | Sept 13]]</DIV>"
                        ."<P>You should see a smaller maple leaf in the top-right corner, with a caption underneath it 'Sept 13'.</P>"

                         ),

// 7
array( 'id' => 'STYLES',
       'title' => 'Styles',
       'instructions' => "<P>That's most of the formatting that Wiki can do.  But what about colours, fonts, and font sizes?</P>"
                        ."<P>The goal of Wiki is to keep the formatting style consistent for all pages, and for every person who enters information. "
                        ."If two people write articles for Wikipedia, those articles have the same 'look'.</P>"
                        ."<P>Wiki does this by <B>not</B> giving you the choice of colours and fonts.  Instead, those are defined in a separate <B>style</B> "
                        ."that's applied to every piece of Wiki text.  At some point, you'll get to design styles too, but for now, let's see what "
                        ."your Wiki text looks like with some different styles.</P>"
                        ."<P>Choose a style to the left and click Format to see what it looks like.</P>"
        ),

// 8
array( 'id' => "END",
       'title' => 'The End',
       'instructions' => "<P>Great job, $name!</P><P>You can do a lot of things with Wiki, and hopefully you've found it easy to use.  It doesn't let you do everything though.</P>"
                        ."<P>For instance, it doesn't let you make tables, it's difficult to justify or center text, and you can't always control where "
                        ."images will appear.  But it's pretty good for most purposes, it's easy to use, and it gives perfectly consistent formatting every "
                        ."time.</P>"
                        ."<P>Sometimes, we'll want more precise control of the page content, or we'll want to control colour, font, or other elements. "
                        ."That's when we'll use the full-text editor.  Mostly, you should plan to use Wiki formatting a lot on our web site.</P>"
                        ."<P>This is the last page of this course, so spend some time trying a few things.  Try making an italicized header (use double "
                        ."apostrophes and equal signs in just the right way). Take a look at the reference chart, and see what else you can do."
        )
);


/* These override the default styles for the .view css class
 */
$raStyles = array(
    array( 'name'=>"Default",
           'css'=> "" ),

    array( 'name'=>"Earthy",
           'css'=> ".view {background-color:#ddbb88;  color: #445511;}"
                  .".view em, .view strong, .view h1, .view h2, .view h3 {color: brown} "
            ),


    array( 'name'=>"Candy Cane",
           'css'=> ".view {background-color:#ffffff;  color: green;} "
                  .".view em, .view strong, .view h1, .view h2, .view h3 {color: red} "
            ),

    array( 'name'=>"Comic",
           'css'=> ".view {background-color:#f7f2a0;  color: black; font-family:Comic Sans MS;} "
                  .".view em, .view strong, .view h1, .view h2, .view h3 {color: blue} "
            ),
    array( 'name'=>"Comic compact",
           'css'=> ".view {background-color:#f7f2a0;  color: black; font-family:Comic Sans MS; font-size:9pt;} "
                  .".view em, .view strong, .view h1, .view h2, .view h3 {color: blue} "
            ),



);





/* Show the current lesson
 */

if( $lesson[$n]['id'] != 'STYLES' )  $nStyle = 0;


echo "<STYLE>"
    .".xmpType { font-size:9pt; background:#f7f2d0; padding:10px; font-family:courier new,courier,fixed; width:75%; float:center; overflow:auto; }"
    .".view    { background-color:#eeeeee; padding:10px; $verdana; font-size:11pt; }"
    .$raStyles[$nStyle]['css']
    ."</STYLE>";

echo "<DIV style='background-color:#cccccc;width:100%'>"
    ."<DIV style='float:right;font-size:9pt;$verdana;'><A HREF='{$_SERVER['PHP_SELF']}?n=0'>Start over</A>".SEEDStd_StrNBSP("",10)
    ."<A HREF='{$_SERVER['PHP_SELF']}?action=Reference' target='_blank'>Reference Chart</A></DIV>"
    ."<H2 style='$verdana'>Learn about Wiki with Seeds of Diversity</H2>"
    ."<H3 style='$verdana'>".(($n ? "Lesson $n: " : "").$lesson[$n]['title'])."</H3>"
    ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
    ."<INPUT type=hidden name=n value=$n>"
    ."<TABLE border=1 width=100% cellpadding=10><TR><TD valign='top'>";
if( $n ) {
    echo "Type here:<BR>"
        ."<TEXTAREA name=text cols=50 rows=15 wrap=off>".htmlspecialchars($text,ENT_QUOTES)."</TEXTAREA><BR><BR>"
        ."<INPUT type=submit name='action' value='Format'>";
    if( $lesson[$n]['id'] == "STYLES" ) {
        echo SEEDStd_StrNBSP("",10)."Choose a Style: <SELECT name='style'>";
        foreach( $raStyles as $k => $v ) {
            echo "<OPTION value='$k'".($k == $nStyle ? " SELECTED" : "").">".$v['name']."</OPTION>";
        }
        echo "</SELECT>";
    }
}
echo "</TD><TD rowspan=2 valign='top' width=50% style='$verdana; font-size:10pt;'><B>Instructions</B><BR><BR>"
    .$lesson[$n]['instructions']
    .($n ? "<INPUT type=hidden name=name value='".htmlspecialchars($name,ENT_QUOTES)."'>"
         : "<INPUT type=text name=name size=30>")
    .($lesson[$n]['id'] == 'END' ? "" : ("<BR><BR><INPUT type=submit name='action' value='".($n ? "Next Lesson" : "Start")."'></TD></TR>"))
    ."<TR><TD valign='top'>";
if( $n ) {
    echo "This is what your wiki text will look like on a web site<BR>"
        ."<DIV class='view'>".$parser->parse( $text )."</DIV>";
}
echo "</TD>"
    ."</TR></TABLE>"
    ."</DIV>";


/* Notify progress by email
 */
switch( $lesson[$n]['id'] ) {
    case 'INTRO':
        MailFromOffice( "bob@seeds.ca", "$name started Wiki training", $text );
        break;
    case 'END':
        MailFromOffice( "bob@seeds.ca", "$name finished Wiki training", $text );
        break;
}


function draw_Reference( $parser )
/*********************************
    Output a reference chart of all Wiki capabilities
 */
{
    $wiki = array(

// ***** Title
"= Wiki Reference Chart =

This chart shows what you can do with
Seeds of Diversity's wiki formatting.
",
"== Paragraphs ==

A blank line makes a new paragraph.

A blank line makes a new paragraph.
",

// ***** Headings
"== Headings ==
===Three===
====Four====
=====Five=====
",

// ***** Emphasized Text
"== Emphasized Text ==

What I said.
What I ''really'' said.
What I ''''really meant''''.

N.B. different Wiki pages might emphasize
text in different ways (bold, italic,
underline, etc). It depends on the
style of the page.
",

// ***** Lists
"== Lists ==

==== Types of Lists ====
* Unordered lists
** show bullet points
** are used when items have no natural order
* Numbered lists
** show numbers
** are used when items have a natural order
* Definition lists
** show related pairs of items
** people don't use them very much
*** that's why you don't see this format in most tutorials

==== Lists can contain lists ====

# Put your socks on
# Put your shoes on
# Go outside
#* grab an umbrella first, if it's raining
#* or a hat if it's sunny
# Get a bite to eat

==== A Definition List ====

; Solanaceae : the tomato family (potatoes, peppers)
; Asteraceae : the daisy family (sunflowers, lettuce)
; Chenopodiaceae
: the beet family (swiss chard, quinoa)

Notice different wiki text structure on the third definition
(the colon is on the next line). Both structures work the same.
",

// ***** Variable
"== Variables ==

There are several variables that can be embedded in a wiki page.

Today's Month = {{CURRENTMONTH}} or {{CURRENTMONTHNAME}}

Today's Day = {{CURRENTDAY}} or {{CURRENTDAYNAME}}

Today's Year = {{CURRENTYEAR}}

Current Time = {{CURRENTTIME}}

This web site = {{SITENAME}}

Examples:
* Today is {{CURRENTDAYNAME}} {{CURRENTMONTHNAME}} {{CURRENTDAY}}, {{CURRENTYEAR}}
* You're visiting {{SITENAME}} at {{CURRENTTIME}}

N.B. The current time is based on where the web server is, not where
the viewer is. It could easily be a different time zone!  This can be
confusing.
",

// ***** HR
"== Horizontal Lines ==

You make a horizontal line with four hyphens all alone.
----
Between the lines.
----
Under the lines.
",

// ***** Monospace
"== Monospace Format ==

Start a line with a single space to use a monospace font.

 A regular font wouldn't do the     o o
 spacing right to make this          b
 look like a face                  '---'
",

// ***** Indent
/*
This works, but it is handled by the variant of DL that allows the dd term to be on the following line, i.e. starting with a colon.
So each indented line is just enclosed by <dd>, which achieves an indent, but doesn't really make sense if we're using CSS to format our indentation.
Also, doesn't handle double indents.
Probably can be fixed easily within handle_definitionlist.

"== Indent ==

This is the normal position.
: This line is indented.
: Still indented.
Normal position again.
",
*/

// ***** Links
"== Links ==

See our web site [[http://www.seeds.ca]]

See our [[http://www.seeds.ca | web site]]

See our [[http://www.seeds.ca | new | web site]] in a new window.

See [[http://www.seeds.ca | new | http://www.seeds.ca]] in a new window.

Contact our office at [[mailto:office@seeds.ca]]

Contact our [[mailto:office@seeds.ca | office ]]

Alternate formats (not preferred): [http://www.google.ca],
[http://www.google.ca Google]

The alternate formats use only one square bracket.
This is not Seeds of Diversity's preferred format,
but it's listed here in case you wonder why it works.
",

// ***** Images
"== Images ==

Plain image inline
[[Image: http://www.seeds.ca/img/canleaf.gif]]
with the text

Sized image inline
[[Image: http://www.seeds.ca/img/canleaf.gif | {height=30} ]]
with the text

Aligned to left
[[Image: http://www.seeds.ca/img/canleaf.gif | left {height=30}]]

Aligned to right
[[Image: http://www.seeds.ca/img/canleaf.gif | right {height=30}]]

With a caption
[[Image: http://www.seeds.ca/img/canleaf.gif|frame right{height=30}|Maple Leaf]]
"
);


// can't show the <nowiki> example using the above, such that the tags appear on the left side (the browser hides them)
// and they don't appear on the right side (we can use entities to make them appear on the left, but then they also appear on the right
$wiki2 = array(
// ***** nowiki
"== Disabling wiki ==

<nowiki>
You can ''disable'' wiki formatting using nowiki tags with angle brackets.
</nowiki>

For instance, you can describe how an <nowiki>[[Image: ]]</nowiki> tag
works, without actually activating that tag. Or you can put symbols
where Wiki would normally process them.

Normally, this would make a numbered list and a heading.
<nowiki>
# of pineapples * price
= cost of all the pineapples = your price
</nowiki>
",
// ***** References
"== End notes ==
End notes are created using ''ref'' and ''references'' tags.

Put your end notes in pairs of ''ref'' tags.
The one at the start doesn't have a slash;
the one at the end does.
<ref>See ''XML'' tag syntax</ref>

You can put any formatting you want inside the ref.
<ref>It's true. You're allowed to put
line breaks
inside the ref tags. That means you can make
* lists
* new paragraphs
* horizontal lines
* pretty well anything

in a reference.
</ref>

This is mostly useful for formatting links <ref>Wiki reference
[[http://www.seeds.ca/int/learn/wiki.php?action=Reference]] </ref>

Then, wherever you put the special tag ''references''
(with a slash at the end - sorry, it's an XML rule)
the end notes are compiled and formatted.
You can even put the references tag above the refs, if you want to.

----
<references/>
",
// ***** Embedded HTML
"== HTML Tags ==
You can embed any <B>HTML tags</B> in wiki text.

<TABLE border=1 cellpadding=5>
<TR><TD>This</TD><TD>is</TD></TR>
<TR><TD>how</TD><TD>to</TD></TR>
<TR><TD>make</TD><TD>a</TD></TR>
<TR><TD colspan=2>table</TD></TR>
</TABLE>
"
);


    global $verdana;

    echo "<STYLE>"
        ."td { $verdana; font-size:11pt; }"
        ."</STYLE>";

    echo "<IMG src='".SITEIMG."logo./logo02_EN_600.png'><BR><BR>";


    echo "<TABLE border=1 cellpadding=20 width=100%>";
    foreach( $wiki as $w ) {
        echo "<TR><TD valign='top' width=50%><PRE style='font-size:9pt'>$w</PRE></TD>"
            ."<TD valign='top'>".$parser->parse($w)."</TD></TR>";
    }
    foreach( $wiki2 as $w ) {
        echo "<TR><TD valign='top' width=50%><PRE style='font-size:9pt'>".str_replace("<","&lt;",$w)."</PRE></TD>"
            ."<TD valign='top'>".$parser->parse($w)."</TD></TR>";
    }
    echo "</TABLE>";
}

?>
