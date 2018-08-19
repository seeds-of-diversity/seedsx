changed Facts & Folklore to Heritage Varieties Database
    - global.inc
    - ff.php
    - sourcesearch.php
    - species.php

changed Copyright 2001 to Copyright 2002
    - global.inc

changed the formatting of the Source Information section
    - cultivar.php

urlencode href parameters - so parms with spaces are not truncated in Netscape 4.7
    - seed.inc
    - seedsource.inc
    - cultivar.php
    - species.php

changed subheadings (Seed Catalogue References -> Historic Seed Catalogue References,
                     Misc References -> Miscellaneous References,
                     Source Information -> Seed Availability)
    - cultivar.php

21113 - Bug: link for (Unnamed) in cultivarlist always had blank species.
             Added "global $species" to cultivarlist.php@start_cultivar_list.

40117 - Change name from HVD to HPD
      - use common siteroot/global.php instead of local global.inc
      - require strong PHP parms
40303 - rename species.php -> cv.php (this is the cultivar frameset)
      - rename cultivarlist.php -> cvlist.php (this is the left frame)
      - rename cultivar.php -> cvdetail.php (this is the right frame)
40314 - put search results and sourcesearch results in the left frame of the CVFRAMESET


TODO:  Sure would be nice for the sodclist to have a boolean field "same as previous year".
       You need to Update across a join to fill this field.
            INSERT INTO t1 SELECT sp,pname,mbr FROM 2002 X 2003 WHERE fields same
            INSERT INTO new2003 SELECT sp,pname,mbr,fields FROM 2002 X 2003 WHERE fields not same
            INSERT INTO new2003 SELECT sp,pname,mbr,bSame,fields FROM t1
       We do need to store duplicated fields of latter years, because we can't write a general query to retrieve
       the fields from N previous years.  i.e. if we omit descriptions to save space, we have to look back an
       arbitrary number of years to find the description text.
