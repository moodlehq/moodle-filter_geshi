Syntax highlight filter based on GeSHi

It can highlight c,asm,bash,cpp,css,lisp,matlab,html4strict,php,pascal,xml
and many other languages (see list in geshi/geshi/)


To Install it:
    - Enable if from "Administration/Filters".

To Use it:
    - Enclose your code in <span syntax="langname"> </span>
    - There are some options available:
    - linenumbers="yes": Enable line numbers
    - urls="yes": Enable keyword-to-URL conversion
    - indentsize="num": Change indent size. Note this applies only to TABS in the source
    - example usage:
      <span syntax="langname" linenumbers="yes" urls="no" indentsize="2">
    - Syntax attribute must come first, the others are optional
    - line numbers are off by default, and URLs are on.

To modify colors:
    First way (with brute force)
     	- Go to file geshi/geshi/language_name.php, find there 'STYLES' array.
	- Make changes you wish.
	- NOT recommended!
    Second way
	- Open /filter/geshi/styles.php with a web browser
	- Copy and paste the generated stylesheet into your theme directory as geshi.css
	- Modify colours as you see fit
	- In theme config.php add 'geshi' element to $THEME->sheets array

Enjoy!
RGBeast, rgbeast@onlineuniversity.ru
Nigel McNie, nigel@geshi.org
