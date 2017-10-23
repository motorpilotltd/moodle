This is the local Arup costcentre plugin.
It is for managing settings, users, and roles within cost centres.

In the user table the icq field holds the cost centre id, the department the cost centre name.
e.g.
icq = 01-774
department = Learning & Collaboration

DB tables are:
local_costcentre - Stores cost centre settings,
    e.g. enableappraisal which is polled by the plugin local/onlineappraisal as an access check.
local_costcentre_user - Stores permissions for users against cost centres.

The users that you can assign are only users authenticated by saml. 
costcentre.php line 280: $select = "auth = 'saml' AND deleted = 0 AND suspended = 0 AND confirmed = 1";
ajax.php line 37: auth = 'saml' AND deleted = 0 AND suspended = 0 AND confirmed = 1

Possible permissions for users are defined in
/classes/costcentre.php (the constants)

Index (index.php) is for viewing and updating cost centre settings and user permission assignments.

NOTE
In the amd/build folder there is a file called select2.min.js. This is there because it failed to build using grunt.
It is used in enhance.js.
