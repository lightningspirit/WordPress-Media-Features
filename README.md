WordPress-Media-Features
========================

Adds a bunch of new features to Media Files like category organization, file type filter/browser, uploaded image resize, crop and JPEG quality controllers.


Description
-----------

Add functionalities for your media library:

* Organize media files with categories
* Bulk categorization
* Add PDF and Document mime types support
* Adjust JPEG quality percentage (from 0 to 100) thus increase image quality
* Crop and resize uploaded images
* Add more file types support (documents, spreadsheets, video, audio, flash, ZIP (packages) and more image types)
* Support for PDF and all type of documents organization

Common Supported file extensions:
---------------------------------

* Images: jpg, jpeg, png, gif, tiff, wpmb, svg, swf (flash)
* Documents: pdf, doc, docx, wri, rtf, xls, xlsx, ppt, pptx, and all OASIS OpenDocument standard extensions
* Text: txt, csv, plain, HTML, XML, CSS, ...
* Video: asf, wmv, avi, mov, qt, mpg, mpeg, mp4, ...
* Audio: mp3, m4a, ra, ram, wav, ogg, midi, wma, ...
* Packages: zip, rar, 7z, iso, img, tar, gz, deb, rpm, ...
* Generic: mo, po, fla, ttf, woff, bmp, ico, ...

Just install this plugin and adjust to your needs.

Provided Custom Hooks
---------------------

This plugin provides 2 custom filters:

1. Change labels and other arguments passed to register_taxonomy function:
`add_filter( 'media_category_args', 'my_media_category_args' );`

2. Hooks inside the category postbox HTML used to edit individual file information: 
`add_filter( 'media_category_html', 'my_media_category_html' );`

The use of these hooks are recommended, thus, if the plugin gets deactivated, those hooks will not be called anymore and then you get things consistent and clean.

Do you want to translate it to your language? Just reply to the «Translations» topic in the forum.


Installation
------------

1. Upload `media-features` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure averything in Options Media page


Frequently Asked Questions
--------------------------

1. Will this affect my Wordpress Instalation?

	No. The plugin uses the WordPress API to expand functionality.

2. I would like to ask for a feature...

	Create a new topic in the plugins forum.

Changelog
-----------

= 0.2 =

* Compatible with version 3.5
* Bulk categorization of media files

= 0.1 =

* Initial Release
* Functionalities:
 * Organize media files with categories
 * Add PDF and Document mime types support
 * Adjust JPEG quality percentage
 * Crop and resize uploaded images

Upgrade Notice
--------------

* Compatible (only) with version 3.5
* Bulk categorization of media files
