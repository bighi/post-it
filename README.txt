Post-it Documentation

=== INSTALATION ===

To use this plugin in your project, it should be declared as a component on your controller.
You can declare it inside your controller like this:

var $components = array('Postit.Postit');

If you want this component in ALL your controllers, just add the code above to your app_controller.php

You can access this components through $this->Postit inside your controller.



=== USAGE ===

To use this plugin, you should call $this->Postit, passing all parameters as chained methods (see below).
You ALWAYS need to call upload() method last. If you do not call it, nothing will happen.

Example:
$this->Postit->image("avatar")->size("160x320")->save_to("avatars")->upload();

All parameters are called after $this->Postit, and separated by the arrow ->

The only parameters not optional are IMAGE() and UPLOAD().

If a param has an alias, you can call its alias instead of the real name. You can see below that image() param 
has an alias called picture. So you can call it image() or picture() and the result is the same.

See below all parameters available and their aliases.


=== SPECIAL NOTE: MAXIMIZATION ===

Both main picture and thumbnail have their 'maximization' variable set to false. What does it mean?

If you set the desired size (lets say '300x300') and you have an image with size 400x270, then its width 
will be reduced to 300, and its height WILL STAY 270. Why does it happen? Maximization!

When you set image size, you are telling the plugin what's the biggest size you want. If width or height of 
the image is SMALLER then the size you passed, it will not change if maximization if OFF (set to false).
There are params that set image maximization to ON (set to true). When it's on, and you informed a size bigger 
than 0, than this size will always expand to the desired size. So, in the example aboxe, the picture width
will be reduced to 300, and its height will be expanded to 300.

Never forget maximization when using this plugin.

*** If you want your images ALWAYS maximized, you can use a maximize param in your beforeFilter, like this:
$this->Postit->maximize_both();
And then the magic of maximization will always happen.



=== PARAMETERS ===

Default parameters are located inside parentheses ( )


-- Main Picture Parameters --


These parameters work on the main picture only, and does not affect thumbnail.

image('image') --> This param tells the plugin wich of the form fields holds image information. 
You should pass the name of the input form your HTML form where the image was uploaded.
Aliases: picture

size('0x0') --> This param defines image width and height. It should be a string where width and height are 
separated by an 'x'.
If width or height are 0, the original size is not changed. Example: ('0x300') will change the height, but the
width will be intact.
If you want width OR height to be automatically resized to keep image scale, set it as -1 and the other dimension above 0.
Example: size('395x-1')

maximize_picture() --> Activates maximization of the main picture. See chapter Maximization above.
Aliases: max_picture, max_pic


-- Thumbnail Parameters --


with_thumbnail('0x0') --> The main thumbnail method. This is used to tell the system a thumbnail should be 
generated, and sets the size at the same time. This follows the same patter of widthxheight.
Size is not mandatory. You can call this method with no size, and then call the smart methods width() and 
height() to define size.
If no size is defined by any means (or defined as 0) then the original size of the image is not changed.
Aliases: thumb, with_thumb, copy

maximize_thumbnail() --> Activates maximization of the thumbnail. See chapter Maximization above.
Aliases: max_thumbnail, max_thumb


-- Smart Parameters --

Smart parameters are special. They can be applied to the main picture OR the thumbnail, but will not affect 
both at the same time (exception: maximize_both()). The effect of the smart methods depends on the moment 
you call then.

If you call a smart method after calling a main picture method, then it will work on the main picture.
Example: 
$this->Postit->image("avatar")->to_folder("avatars")->upload();
In this example, to_folder is being applied to the main picture

If you call a smart method after calling a thumbnail method, then it will work on the thumbnail.
Example:
$this->Postit->image("avatar")->with_thumbnail("90x90")->to_folder("thumbs")->upload();
In this example, to_folder is being applied to the thumbnail.


maximize() --> Activates image maximization. See chapter Maximization above.
Aliases: max.

maximize_both() --> Activates the main picture's AND thumbnail's maximization. See chapter Maximization above.
Aliases: max_both

height('0') --> Set the image height. If it was already set before, this new value overwrite the old one.
Aliases: h

width('0') --> Set the image width. If it was already set before, this new value overwrite the old one.
Aliases: w

to_folder('img/') --> Defines the folder the image is going to be saved at. It is not necessary to add img/ at the 
beginning, because the system does it for you.
Aliases: save_to, folder, dir

name('imagename') --> Defines what the image file will be named after its been resized and moved.

upload() --> This is the final method. It should be called last! This method gets all passed params and then 
handles file upload, resizing, copying, moving and renaming. It is the magic method that makes everything 
happen!
Aliases: send, up