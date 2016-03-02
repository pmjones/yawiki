; <?php die() ?>
; $Id: Yawp.conf-example.php,v 1.8 2005/01/18 15:19:07 pmjones Exp $

[CONSTANT]
HREF_BASE = /
;DOCUMENT_ROOT = 
;HTTP_HOST = example.com

[Yawp]
start = %DOCUMENT_ROOT%yawiki/lib/start.hook.php
logout = %DOCUMENT_ROOT%yawiki/lib/logout.hook.php


[Auth]
container          = DB
expire             = 7200
idle               = 1800
%AUTH_IDLED%       = Your session has been idle for too long.  Please sign in again.
%AUTH_EXPIRED%     = Your session has expired.  Please sign in again.
%AUTH_WRONG_LOGIN% = You provided an incorrect username or password.  Please try again.

[Auth_DB]
dsn         = mysql://username:password@localhost/database
table       = users
usernamecol = username
passwordcol = password

[Auth_LDAP]
url      = ldap://ds.example.com/
basedn   = ou=people, o=my company, st=tn, c=us
userattr = uid
useroc   = person


[DB]
phptype  = mysql
hostspec = localhost
username = your_username
password = your_password
database = some_database


[yawiki]

; The default area and page to show when entering the wiki.
; also, the first area and page to be created when Yawiki
; runs the first time.
default_area = Main
default_page = HomePage

; if only_area is an area name, the wiki will show only
; that area and none of the others.  remove or set to
; false to show all areas in the wiki.
only_area = false

; when showing diffs, wrap at this many characters in each column
diff_wrap = 40

; rss defaults
rss_type = days
rss_amt = 3

; strftime() format for dates
dateformat = %a, %d %b %Y, %R

; Location for user-defined Savant templates, filters, plugins
;theme_dir = 

; Add Interwiki sites in "SiteName, Base-URL" format (c.f. Text_Wiki)
interwiki_site = Wiki, http://c2.com/cgi/wiki?
interwiki_site = YaWiki, http://yawiki.com/yawiki/index.php?page=%s
interwiki_site = Yawiki, http://yawiki.com/yawiki/index.php?page=%s
interwiki_site = Text_Wiki, http://wiki.ciaweb.net/yawiki/index.php?area=Text_Wiki&page=%s

; i18n strings for submit buttons
op_preview = Preview
op_cancel = Cancel
op_save = Save
op_revert = Revert
op_delete = Delete


[yawiki_acl]

; create this username in the access control list as the
; wiki administrator ("Root" or "System Administrator")
; when Yawiki runs the first time.
create_wiki_admin = YOUR_USERNAME


[yawiki_locks]
timeout = 15


[Text_Wiki]
; disable these rules by default
disable = include
disable = embed
disable = html

;[Text_Wiki_Parse]
;path = /path/to/parse/rules

;[Text_Wiki_Render]
;path = /path/to/render/rules

;[Text_Wiki_Render_Xhtml_Anchor]
;css = 

;[Text_Wiki_Render_Xhtml_Blockquote]
;css = 

;[Text_Wiki_Render_Xhtml_Break]
;css = 

;[Text_Wiki_Render_Xhtml_Code]
;css = 
;css_code = 
;css_php = 
;css_html = 

;[Text_Wiki_Render_Xhtml_Deflist]
;css_dl = 
;css_dt = 
;css_dd = 

;[Text_Wiki_Render_Xhtml_Embed]
;base = %DOCUMENT_ROOT%

;[Text_Wiki_Render_Xhtml_Emphasis]
;css = 

;[Text_Wiki_Render_Xhtml_Freelink]
;new_text = 
;new_text_pos = 
;css = 
;css_new =

;[Text_Wiki_Render_Xhtml_Heading]
;css_h1 = 
;css_h2 = 
;css_h3 = 
;css_h4 = 
;css_h5 = 
;css_h6 = 

;[Text_Wiki_Render_Xhtml_Horiz]
;css = 

;[Text_Wiki_Render_Xhtml_Image]
;base = %HREF_BASE%
;css = 
;css_link = 

;[Text_Wiki_Render_Xhtml_Include]
;base = %DOCUMENT_ROOT%

;[Text_Wiki_Render_Xhtml_Interwiki]
;target = _blank
;css = 

;[Text_Wiki_Render_Xhtml_List]
;css_ol = 
;css_ol_li = 
;css_ul = 
;css_ul_li = 

;[Text_Wiki_Render_Xhtml_Paragraph]
;css = 

;[Text_Wiki_Render_Xhtml_Phplookup]
;css = 

;[Text_Wiki_Render_Xhtml_Revise]
;css_del = 
;css_ins = 

;[Text_Wiki_Render_Xhtml_Strong]
;css = 

;[Text_Wiki_Render_Xhtml_Superscript]
;css = 

[Text_Wiki_Render_Xhtml_Table]
css_table = yawiki
css_tr = yawiki
css_th = yawiki
css_td = yawiki

[Text_Wiki_Render_Xhtml_Toc]
div_id = toc
css_list = toc_list
css_item = toc_item
title = Table of Contents

;[Text_Wiki_Render_Xhtml_Tt]
;css = 

;[Text_Wiki_Render_Xhtml_Url]
;target = 
;images = 
;css_inline = 
;css_footnote = 
;css_descr = 
;css_img = 

;[Text_Wiki_Render_Xhtml_Wikilink]
;new_text = 
;new_text_pos = 
;css = 
;css_new =
