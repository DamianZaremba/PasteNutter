PasteNutter
===========

Simplistic pastebin aimed at IRC channels.

How to install
--------------

1. Download the code
1. Move irc_bot.py to somewhere not public (or just put an ACL in place) IT CONTAINS DB DETAILS
1. Edit irc_bot.py and config.inc.php to your liking mainly db details & rc port/ip needs to match.
1. Import database.sql into MySQL
1. Run irc_bot.py (probably in screen)
1. Edit the templates in templates/ to your liking
1. It works

License
-------

PasteNutter is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

PasteNutter is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PasteNutter.  If not, see <http://www.gnu.org/licenses/>. 


Bugs
----

It has been roughly tested but there probably will be bugs - I'm guessing around the IRC user handling stuff.
Report them via github and I might look at fixing them or just fix them yourself.
