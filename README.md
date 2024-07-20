# joomla_com_usernotes
A Joomla 4.x/5.x component that provides individual users and groups with a method to store a library of fulltext notes. Each note can also have attachments.

Notes can be separately secured with an encryption phrase that is not stored anywhere (warning here ... if you can't remember the encryption phrase, you've lost the data in your note). With this method and the strength of the encryption, you can feel comfortable in storing the most confidential of information in your notes.

The component uses SQLite3 databases for notes information, allowing separate notes collections for all users, groups, or site-wide. The Joomla library [jooma_lib_rjuser](http://github.com/ron4mac/joomla_lib_rjuser) is **REQUIRED** to use this component. Also, the companion plugin, [plg_system_rjuserd](http://github.com/ron4mac/joomla_plg_rjuserd), should be used to manage the storage location for users and groups. To download a single, auto-generated package installer for the latest release versions of _com_usernotes_, _lib_rjuser_ and _plg_system_rjuserd_, [click here](http://rjcrans.net/git/com_usernotes/packager/).
