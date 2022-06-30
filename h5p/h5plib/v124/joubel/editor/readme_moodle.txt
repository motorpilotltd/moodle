H5P Editor PHP Library
----------------------

Downloaded last release from: https://github.com/h5p/h5p-editor-php-library/releases

Import procedure:

- Copy all the files from the folder repository in this directory.
- In the method ns.LibrarySelector.prototype.appendTo (scripts/h5peditor-library-selector.js),
  comment the line "this.$selector.appendTo($element);" to avoid the display of the Hub Selector.
- Review strings in joubel/editor/language/en.js and compare them with
existing ones in lang/en/h5plib_vXXX.php: add the new ones and remove the
unexisting ones. Remember to use the AMOS script commands, such CPY, to copy
all the existing strings from the previous version. As you'll see, all the
strings in en.js have been converted following these rules:
  * Prefix  "editor:" has been added.
  * Keys have been lowercased.

Removed:
 * composer.json
 * .gitignore

Added:
 * readme_moodle.txt

Changed:
 * Updated H5peditor::getLibraryData parameters to fix PHP8.0 warnings. See MDL-70903 for details.
 * Make get_magic_quotes_gpc() use conditional, it has been removed in php80. See MDL-73502 for details.
 * Updated CKEditor to 4.17.1 from https://github.com/h5p/h5p-editor-php-library/commit/1ae19fdb80839b32dad3846d6b0a5c745f8f6187. It has been applied the commit on h5p-editor-php-library where CKEditor is updated to 4.17.1. Once, this library will be upgraded to the latest version, this changed should be removed.

Downloaded version: moodle-1.20.2 release
