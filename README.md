# openxmldir

Open79XX XMLDirectory is an open PHP-based XML menuing system for providing on-screen services to the Cisco 79XX Series IP Phones. It contains dynamically generated phone directories, on-screen memos, links to other services, and many more options.

# Original website

The original OpenXMLDir seems to have been abandoned at version 1.2

https://sourceforge.net/projects/open79xxdir/

This is an update of the project just sufficiently to get it to work for our purposes.  

Updated to work with PHP 7.
Applied fixes from the following URLs:
-	https://sourceforge.net/p/open79xxdir/bugs/1/

May also want to apply from:
-	https://sourceforge.net/p/open79xxdir/bugs/2/
-	https://sourceforge.net/p/open79xxdir/bugs/3/ (isn't clearly written)

Need to be writable during install:
chmod ug+w lib/mysql.php PhoneUI/lib/urlbase.php

Did a search/replace on:
-	WebUI/modules/templates/help.html
	Searched: "../
	Replaced: "
	(fixes relative URLs)
