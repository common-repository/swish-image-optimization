=== Swish Image Optimization ===
Plugin Name:  Swish Image Optimization
Contributors: Swish
Tags: image, optimization, resize, external url, ftp, 
Requires at least: 3.0.1
Tested up to: 4.9
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

If you are taking advantage of Cloudflare’s CDN and Optimisation features, you will certainly have issues with Cookie-free domains, as Cloudflare will always set cookies. This plugin allows you to insert images into either posts, pages or featured images as optimised external (cookie-free) images, which is a big factor in increasing Optimisation scores as well as decreasing page loading times.

== Features ==
FTP uploading feature to allow images to be stored on a different domain repository to the website (to allow your website to load images from a cookie-free domain to improve loading times)
Image optimisation function on upload, to reduce file size
Custom featured image size to prevent large images to be used than required 


== Installation ==

* Go to your Wordpress Admin Website and Login as an administrator
* Go to Plugin section and under it, select “Add New”
* Click the “Upload Plugin” button at the Top Left side of the Admin site.
* Click “Choose File” button and locate the "Swish Image Optimization" zip file. Once done, click “Install Now” Button beside the uploaded zip file
* Locate the “Swish Image Optimization” on the plugin page of the Admin site and click “Activate”
* Once done, go to the “Swish Image Optimization’s” settings for further actions, via “Settings” → “Swish Image optimisation”
	a.) On the “FTP OPTIONS” tab, enter the FTP login credentials of your image destination for your cookie-free domain (we recommend using stackpath.com) - example of Stack Path settings:
		FTP HOST:
		FTP PORT:
		FTP TIMEOUT:
		FTP USERNAME: 
		FTP PASSWORD:
		FTP DIRECTORY:
		HTML LINK URL:
	b.) On the “Optimisation Options” tab, select a value for “JPEG Image Quality” and “PNG Image Quality”, which a lower value for each meaning that the images will be optimised more, whereas a higher value will mean that the image quality will be higher, hover file size savings will not be as much. We recommend:
		i. JPEG Image Quality = 85
		ii. PNG Image Quality = 6
	c.) On the “Feature Image Size” tab, enter in the width and height of the size of the feature image that appears on your blog / news home page. 

== Quick Start ==

** Uploading of image from post or page **
* Click "Add Media" Button on Post or Page
* Click "Upload Files" Tab for uploading a new image, else click “Media Library/External Media Library” Tab if the image already uploaded/existed on the following Tab
* Select the image and Click "Insert optimised image into post" Button
* Once process is done, image link should now appear on your Post or Page text Field
see video guide: <a href="https://www.useloom.com/share/af8a94b77da3498d8baa34368291b60d">here</a>


** Uploading of featured image **
* Click "Set optimised featured image"
* Click "Upload Files" Tab for uploading a new image, else click “Media Library/External Media Library” Tab if the image already uploaded/existed on the following Tab
Select the image
* On "Attachment Display Settings", select the “featured image size” on the drop down list
* Click the "Set optimised featured image" Button
* Once processing is done, external featured image preview should now appear
Note: When replacing an existing featured image to a post, remove first the existing featured image, then Click "Set optimised featured image"
see example: <a href="https://www.useloom.com/share/c17f90e643034db4b291b07cb3c22571">here</a>

** Setup settings to Website ( image destination ) with stackpath CDN **
* Once you have already purchased a stackpath plan, Go to https://app.stackpath.com/ and login with your account
* On the Dashboard page, Click “Create new site” button and select Assets only see: <a href="https://support.stackpath.com/hc/en-us/articles/227101528">here</a>
* Assuming that stackpath integration is done, Find the site you created and click “Manage”
* Follow this settings on your end
	see: <a href="http://files.swishdesign.com.au/9Qqnabj">here</a>
	see: <a href="http://files.swishdesign.com.au/vY8NpaJ">here</a>

Note: Your CDN URL will be your new url for your images see:<a href="http://files.swishdesign.com.au/lXld6tq">here</a>
The CDN URL will be inserted to HTML LINK URL see: <a href="http://files.swishdesign.com.au/gboQaCy">here</a>


== Frequently Asked Questions ==

= The “Set optimised featured image” button doesn’t appear when selecting an image on media library =

It might be that you have clicked the existing featured image which should be removed first before replacing/adding a new featured image. To ensure that "Set optimised featured image" appears on media library

Remove first the existing featured image, click the Set optimised featured image on the metabox ( below the Set featured image ) then "Set optimised featured image" button should now appear on media library.

= The “Insert optimised image into post” button doesn’t appear when selecting an image on media library =

It might be that you are using a plugin to add images ( ie. custom field plugin, page builder, custom build theme etc )

= The selected external featured image doesn’t appear on the website =

It might be that you are using a custom theme that has a custom code for featured image. To resolve this, please contact your developer to resolve the issue.
	
= What is HTML LINK URL? =
	- URL directory of the image destination ( ie. swishonlinefiles.com/ )


== Changelog ==
= 1.0 =
* Released Date