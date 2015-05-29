<?php   // ۞// text { encoding:utf-8 ; bom:no ; linebreaks:unix ; tabs:4sp ; }
                                    $cbp_version = '1.2'; // [xhtml compliant]
/*

	IMPORTANT:	if you are upgrading, and something stops working as you 
				expect, try reading the changes at the foot of this document.


	cbparser.php
	the corzblog bbcode to x|html and back to bbcode parser


	converts bbcode to html and back to bbcode, and does it quickly. a bit
	clunky, but it gets the job done each and every day. output is 100% valid
	xhtml 1.0 strict. we use css to style the output as desired, your call.

	feel free to use this code for your own projects, I designed it with
	this in mind; linear. leave a "corz.org" lying around somewhere.
	a link to my site is always cool.

	:!:  if this document is accessed directly, it goes into "demo mode"  :!:
	:!:  as well as being a cool, fun thang, this serves as an excellent  :!:
	:!:  test page if you're adding or removing stuff from the parser     :!:
	:!:  yourself, as well as a useful tags reference/test for all users  :!:
	:!:  and a good, well, working example of how to incorporate cbparser :!:

	There's a full "ALL THE TAGS" reference here.. http://corz.org/bbtags
	and a smaller guide, "cbguide.php", which you can include under your forms
	as a quick refrence for users. I've chucked this into the zip, too.

	These days, cbparser comes with a built-in front-end which you can access with 
	the do_bb_form() function, perhaps something like this..
			
		do_bb_form($exmpl_str,'', '', false, '', false, '', '', 'blogform', false, true);

	See below for more information about the automatic gui creation.


	to use cbparser:

	simply include this file somewhere in your php script, like so..

		include ($_SERVER['DOCUMENT_ROOT'].'/blog/inc/cbparser.php');

	or wherever you keep it. next, some string of text, probably from a $_POST variable,
	ie. a form..

		if (isset($_POST['form-text'])) { $my_string = $_POST['form-text']; }

	..is simply passed through one of cbparser's two functions..

		for bbcode to html conversion >>

			$my_string = bb2html($my_string, $title);

		for html to bbcode conversion >>

			$my_string = html2bb($my_string, $title);

		either can be simply ($my_string) if you don't require the extra unique
		entry functions, i.e. references.

	What comes back will be your string transformed into HTML or bbcode, depending
	on which direction you are going. If there was an error in your bbcode tags
	cbparser will return an empty string, so you can do some message for the user
	in that case. if cbparser recognises the poster as a spammer, it will return
	nothing. A global variable.. $GLOBALS['cbparser']['warning_message'], or $cb_warning_message,
	if you prefer, will be available with the message "spammer". You can catch that, 
	and also kill output at that point, or some other suitable action *hehe*

	cbparser doesn't care about errors in your HTML for the HTML>>bbcode conversion,
	it's main priority is to get "whatever the tags" back into an editable state.

	notes:

	the second argument of the functions is the 'title', which corzblog supplies
	and uses for an html <div id="$title">, but you could provide from anywhere you
	like. then we can do funky things unique to a particular entry, like
	individual references. see my blog, I use these a lot. my comments engine
	sets the <div id= from this too, allowing you/users to link directly to a
	particular comment. groovy.

	if you don't need references that point to individual "id" entries, you can
	just ommit the second argument. it's a good feature, though. worth a few quid
	in my PayPal account, I'd say. *g*

	if you add bits to the parser; complex stuff is better near the start. let me know 
	if you add anything funky, or about any bugs, of course.


	speed:
	my tests show even HUGE lists of str_replace statements are 'bloody fast'.
	there's a microtimer at the foot of my page, check yourself. I like this
	feature-filled approach a great deal, its linearity, and how easy it is to just
	plug stuff in. I hope you do to. I've certainly plugged in *a lot*! certainly
	worth a few quid in m- och forget it! heh. I've even added a few regex-type
	functions lately, once it's up and running, it's pretty fast.

	This very parser is responsible for all this..	http://corz.org/blog/
	well, I helped a bit.


	css rocks:

	I use css to style the various elements, mostly. the parser works fine
	without css, but you will probably want define a few styles. if you need 
	guidance, see.. 
	
		http://corz.org/blog/inc/style/blog.css
	or..
		http://corz.org/inc/css/comments.css

	(call the files with different browsers for slightly differrent versions)

	If you "include" this file in your site header, you can call the parser's 
	functions from anywhere onsite. it's tempting to use the phrase "parsing 
	engine", but that accolade probably belongs to the PEAR package. As well 
	as the parsing, and the built-in demo page, the one cbparser.php also 
	handles "that comments bits" at the foot of most of my onsite tuts and
	contenty type pages.

	you get the idea.

	;o)
	(or

	© (or + corz.org 2003->

	ps.. the in-built demo mode thing only works if this script's name ends in 
	"parser.php", or else edit that, below.



	extra notes:

	InfiniTags™

		With cbparser's unique "InfiniTags™", users can make up bbcode tags on-the-fly. So..
		Even though there is no [legend][/legend] tag, it will work just fine.

		cbparser will also translate < > into [ ] in the HTML >> BBCode translation. this isn't 
		perfect, but close enough for rock 'n' roll. the most used tags are "built-in", but with 
		InfiniTags™ you can create new bbcode as needed, and have it back again, too. real handy.



	built-in GUI		[graphic user interface, aka. front-end]

		do_bb_form() parameters reference.

		To create a gui automatically, call the do_bb_form() function with the following parameters..

		do_bb_form($textarea, $html_preview, $index, $do_title, $title, $do_pass, $hidden_post, $hidden_value, $form_id, $do_pre_butt, $do_pub_butt[, $nested])

		And they as follows..

		$textarea			:	the text you want to place in the textarea			[string]
		$html_preview		:	an html preview	(from bb2html() function)			[string] (use '' for no preview)
		$index				:	an optional numerical index for your form			[integer/integer as string]
		$do_title			:	do the title										[boolean]
		$title				:	an optional input for a title						[string, becomes input name/id]
		$do_pass			:	whether to create a password field or not			[boolean]
		$hidden_post		:	an optional hidden field (use to track a value)		[string, becomes input name/id]
								once set, it will remain set through previews, etc
		$hidden_value		:	the value of said hidden field						[string, defaults to 'true']
		$form_id			:	the main id for the form							[string]
		$do_pre_butt		:	whether to create a "preview" submit input.			[boolean] 
		$do_pub_butt		:	whether to create a "publish" submit input.			[boolean]


		optional parameters..

		$nested				:	whether you are nested inside another form.			[boolean] 
								if you are already inside a <form>, set to true. 
								If you want cbparser to create the form for you, set to false.


		example:
		here's the form cbparser uses for its own demo..

		do_bb_form($exmpl_str,'', '', false, '', false, '', '', 'blogform', true, false, false);

		note:	the gui has some fairly nifty, and totally portable JavaScript functions (for example, you can
				click "bold" and the selected text will get [b]bold[/b] tags around it. Some of the other buttons
				are even niftier.

				these functions are provided by the "func.js" file which lives inside the "js" folder. you will
				probably need to edit the location of where you keep the js file (at the top of cbguide.php) and 
				if you move it, you might want to edit the bottom of this file, for the bbcode demo, if you use
				that feature.

				If you know what a CSRF attack is, you may find the $hidden_value parameter most useful!

				The "preview" is the first submit button, interestingly, this will prevent most spam-bots from 
				posting to your comments facility. my recently written "post-dumper" has been most useful in 
				discovering these sort of things.


		Error Handling..

		you can check for errors by querying  $GLOBALS['cbparser']['state'] (or just $cb_state, from the global
		scope) which will return (odd numbers bad, even numbers good. zero best of all)..

			0 = no errors
			1 = tags don't balance
			2 = tags didn't balance, but were fixed
			3 = evil spammer 
			5 = xss attack or php injection

		If cbparser has automatically fixed some tags, $GLOBALS['cbparser']['text'] (or $cbparser['text'], 
		from the global scope) will contain the "fixed" text, and you will probably want to put that back
		into their textarea, as opposed to the raw, unfixed input from your input $_POST variables. 
		
		In fact, $GLOBALS['cbparser']['text'] ALWAYS contains the bbcode text, so you may reliably use that 
		for your textarea at all times. If any tags were closed, you can access *only* those tags, if you 
		need to, via $GLOBALS['cbparser']['close_tags'] (or $cbparser['close_tags'] from the global scope), 
		which might contain something like '[/i][/b]'.
*/


/*
	preferences..
					*/


/*
	smileys. optional, but fun..
	the full path to the smiley folder relative to your http root..

	alternatively you can use a URL, like "http://domain.com/blog/inc/smileys/"

*/
$smiley_folder = '/cbparser/img/smileys/';
// for the distro, perhaps a __FILE__ based relative path?
/*
	while it seems like an idea to hard-code in some cbparser-relative link, in practice
	this limits the parser. this way, you can use cbparser all over your site, and
	always have the smileys available from one central copy, rather than having to
	duplicate your smiley folder everywhere you want to use the parser.

	so, think about where you want to put them, FIRST!

	A nice idea is to use a mod_rewrite and create a permanent smiley location which
	can be moved around later. perhaps http://mysite/smileys/..

		RewriteRule ^smileys/(.*) /some/path/to/real/location/$1 [nc]
	
	then it doesn't matter where they *really* are, the link is always.. /smileys/some.gif
	if you need to move the *actual* smiley folder in the future, you simply edit the rule.
*/


// cb guide 
// handy bbcode guide with some automatic bbcode buttons, used for the built-in form 
// and also the demo page. provide the FULL path, please..
$cb_guide_path = $_SERVER['DOCUMENT_ROOT'].'/cbparser/cbguide.php';

/*
	we call the guide with a regular include() later on. so anything that would work 
	in for a php include path will work just fine. the full path is the safest bet, though.
*/


/*
	pajamas login.

	as well as a regular password input for your form, the built-in GUI can also integrate
	with the pajamas authentication system..

		http://corz.org/serv/security/pajamas.php
	
	If you want a secure php + javascript login system, check it out. Note: you must 
	initialize your pajamas object as "$auth" for this to work straight off the bat, ie.. 

		$auth = new pajamas();		or..	$auth = new pajamas('MyUID');

*/
$use_pajamas = false;	//if (isset($GLOBALS['corzblog']['use_pajamas'])) { $use_pajamas = $GLOBALS['corzblog']['use_pajamas']; } else { $use_pajamas = false; }


/*
	SPAMMERS!!

	if they want to place their casino link on your site, ask them to pay for it.
	if their hot casino tips are really so hot, a few quid shouldn't be a problem.

	if you set this to false just before calling the function for a "preview", you
	can do a "mock" output. to the spammer, it looks like their link will work just
	fine, but for the actual post, set it to true.. hahahah!

	Or else just set it to true here, and be done with it.
	*/
$prevent_spam = false;



/*
	so your page gets popular..

	apart from the pesky casinos, you may find other spammers taking advantage
	of your nice comments facility, especially if you have high Google PR.
	Add any strings they use to this list and have them defeated!
*/
$spammer_strings = array(
	 'casino', 'astromarv.com', 'carmen-electra', 'angelina-jolie', 'justin-timberlake', 'dish-network', 'missy-elliott', 'byondart.com', 'getmydata.cn', 'bag1881.com', 'krasaonline.cz', 'mut.cz', 'inetmag.cz', 'kavglob.cz', 'casino poker black jack', 'Nice design, good work !', 'reality-inzert.cz', 'spkk.cz', 'hotelcecere.it', 'autoscuolevalenza.it', 'eversene.com', 'gerhardt-wein.de', 'evonshireavenue.org.uk', 'billedprojektkonsulenten.dk', 'dbh.dk', 'amctheatres.com', 'newsdirectory.com', 'morecambebayfs.co.uk', 'maxsms.pl', 'marmota.ro', 'premierestudios.ro', 'spportal.co.uk', 'sunscreenmultimedia.de', 'qbix.pl', 'imperialrugby.co.uk', 'mansfield-notts.co.uk', 'imr.org.pl', 'popag.co.uk', 'oliverbrunotte.de', 'katerpage.de', 'svenkorzer.de', 'taywoodphotographic.co.uk', 'vbsh.dk', 'divshop.com', 'alti-staal.dk', 'dixis.dk', '9er.dk', 'ein.dk', 'poker-fix.com', 'forfattervaerkstedet.dk', 'it-radiologi.dk', 'luftmadrassen.dk', 'metallbau-net.de', 'ostsee-ferienwohnung-eckernfoerde.de', 'kloster-sion.de', 'prommiweb.de', 'spowa-oebisfelde.de', 'yjshs.com', 'law12.com', '191law.com', 'online-gambling-area.org', 'bomm.cn', 'gzhero.com', 'feiyangjipiao.com', 'cqjp.cn', 'jd1718.com.cn'
	);

// yes, I'll think we'll need a separate file!


/*
	so your page gets *really* popular..
		
	so use a seperate spammers file. good idea. easier to update.
	this file is a simple plain text list, one spammer string on each line
	and UNIX "\n" linebreaks. make it so.

	setting this to anything but empty ('') overrides the previous 
	("$spammer_strings") preference (above).
*/
//$spammer_file = $_SERVER['DOCUMENT_ROOT'].'/inc/db/spammers.txt';


// Spammer User Agents..
// list of known spammer agent strings, separated by commas.
// it's okay to put spaces between the entries.
$spammer_agents = 'AIRF, Indy Library';


// cbparser will return this string instead of the input text..
$GLOBALS['spammer_return_string'] = "<h2>&nbsp;&nbsp;spammer!</h2><br /><br />";


/* 
	prevent xss (cross-site scripting) attacks

	This seems to be a hot topic among web developers. xss attacks can vary from annoying 
	pop-ups planted by dodgy users, to cookie-theft and other nice stuff. If you run a site 
	with sensitive user data, especially sensitive data in cookies, you'll probably want to 
	enable this. Surely *you* see the comments first, though. 
*/
$prevent_xss = true;


// what to pop-up over the references..
$cb_ref_title = 'go to the reference';


/*	now we can do mailto: URL's, like this.. [mmail=the big red thing]mail me![/mmail]
	"the big red thing" being the subject (you can use quotes, if you like)
	enter your email address here. it will be "mashed" to protect against spambots

	if you are running this inside corzblog, it will already have been set, there.
*/
if (!isset($corzblog['mail_addy'])) $corzblog['mail_addy'] = 'me@myaddress.com';

/*	if you use cbparser in a "public" setting, (like site comments or something)
	there is now a regular email tag for them, too..

		[email="soso@email.com"]mail me![/email]

	Their address will also be "mashed". (curious? look at the HTML page source.)

		[email="soso@email.com?subject=yo!"]mail me![/email]

	would work fine.
	*/


// php syntax highlighting
// for the cool colored code tags [ccc][/ccc]..
ini_set('highlight.string','#E53600');
ini_set('highlight.comment','#FFAD1D');
ini_set('highlight.keyword','#47A35E');
ini_set('highlight.bg','#FFFFFF');
ini_set('highlight.default','#3F6DAE');
ini_set('highlight.html','#0D3D0D');

// note: you need to include the <?php tags to get the highlighting
// if you use cbguide, its button thoughfully adds these for you.


// if there are any errors, they will be in here..
$GLOBALS['cbparser']['warning_message'] = '';

/*
	you could do something like..

		if (!empty($cb_warning_message)) { 
			echo'<span class="cb-notice">'.$cb_warning_message.'</span>';
		}

	my debug script pops up a nice dialog if it finds anything in this array
	but you can do whatever you like with it.
*/


/*
	the individual cbparser warning messages..
	note: it is possible to get more than one of these.

											*/
$GLOBALS['cbparser']['warnings']['spammer'] = '<div class="centered" id="message">spammer!</div>';

$GLOBALS['cbparser']['warnings']['balance_fixed'] = '
		<div class="centered" id="message">
			<span class="red">note</span>: some tags were automatically closed for you.<br />
			(check your bbcode)
		</div>';

$GLOBALS['cbparser']['warnings']['imbalanced'] = '
		<div class="centered" id="message">
			<span class="red">note</span>: your tags are not <a 
			title="in other words; you have opened a tag, but not closed it.">balanced!</a><br />
			(check your bbcode)<br />
			<br />
		</div>
		<div id="bbinfo">
			<strong>notes..</strong><br />
			to produce a square bracket, double it! -&gt; <code><strong>[[ ! ]]</strong></code><br />
			to insert shell code or ascii art, use <code><strong>[pre][/pre]</strong></code> or <code><strong>[tt][/tt]</strong></code> tags.<br />
			for php web code, use [ccc] tags..<pre>
[ccc]&lt;?php
echo "foo!";
?&gt;[/ccc]</pre>
			for more information, check out <a href="http://corz.org/bbtags" 
			onclick="window.open(this.href); return false;" title="ALL the tags!">the <big>instructions</big></a>!
		</div>';

// probably your script should be catching this, but this is handy, anyway..
$GLOBALS['cbparser']['warnings']['empty'] = '
		<div class="centered" id="message">
			there was no text!
		</div>';


/*
end prefs
	*/



// debugging prefs..
// you shouldn't ever need to mess with these, but I'll leave them here, anyway.
$trans_warp_drive = false;
$check_tag_balance = true;
$thingamie_jig = true;
// whatever you do, don't enable the trans-warp drive!


/*
	The above variables will be loaded into your script when it is "included"
	but you can override any of them temporarily by declaring new values (in
	your script) anytime after that, but *before* you call either of the two
	magic functions. And here they are..
*/




/*
	bbcocode to xhtml

	converts bbcode to xhtml 1.0 strict.

	usage:

		string ( string to transform [, string title])

				   */
function bb2html() {
global $cb_ref_title, $check_tag_balance, $smiley_folder, $insert_link, $prevent_spam, $prevent_xss;

$bb2html = func_get_arg(0);
if (func_num_args() == 2) {
	$title = func_get_arg(1);
	$id_title = make_valid_id($title); // fix up bad id's
} else { 
	$id_title = $title = '';
}

	// init.. [useful global array]
	$GLOBALS['cbparser']['state'] = 0;
	$GLOBALS['cbparser']['close_tags'] = '';
	$GLOBALS['cbparser']['text'] = slash_it($bb2html);
if (!empty($GLOBALS['do_debug'])) { debug("\n\n".'cbparser incoming [$bb2html]: '. $bb2html ."\n\n"); }// :debug:

	// oops!
	if ($bb2html == '') {
		$GLOBALS['cbparser']['state'] = 1;
		$GLOBALS['cbparser']['warning_message'] .= $GLOBALS['cbparser']['warnings']['empty'];
		return false;
	}

	// grab any *real* square brackets first, store 'em..
	$bb2html = str_replace('[[[[', '**$@$**[[', $bb2html); // catch demo tags next to demo tags
	$bb2html = str_replace(']]]]', ']]**@^@**', $bb2html); // ditto
	$bb2html = str_replace('[[[', '**$@$**[', $bb2html); // catch tags next to demo tags
	$bb2html = str_replace(']]]', ']**@^@**', $bb2html); // ditto
	$bb2html = str_replace('[[', '**$@$**', $bb2html); // finally!
	$bb2html = str_replace(']]', '**@^@**', $bb2html);


	// ensure bbcode is lowercase..
	$bb2html = bbcode_to_lower($bb2html);

	/*
		pre-formatted text

		even bbcode inside [pre] text will remain untouched, as it should be.
		there may be multiple [pre] or [ccc] blocks, so we grab them all and create arrays..
		*/

	$pre = array(); $i = 9999;
	while ($pre_str = stristr($bb2html, '[pre]')) {
if (!empty($GLOBALS['do_debug'])) debug("\n".'$pre_str: '."$pre_str\n\n");// :debug:
		$pre_str = substr($pre_str, 0, strpos($pre_str, '[/pre]') + 6);
		$bb2html = str_replace($pre_str, "***pre_string***$i", $bb2html);
		$pre[$i] = encode(str_replace(array('**$@$**', '**@^@**'), array('[[', ']]'), $pre_str));
if (!empty($GLOBALS['do_debug'])) debug("\n".'$pre[$i]: '."$pre[$i]\n\n");// :debug:
		$i++; //	^^	we encode this, for html tags, etc.
	}

	/*
		syntax highlighting (Cool Colored Code™)
		och, why not!
		*/
	$ccc = array(); $i = 0;
	while ($ccc_str = stristr($bb2html, '[ccc]')) {
		$ccc_str = substr($ccc_str, 0, strpos($ccc_str, '[/ccc]') + 6);
		$bb2html = str_replace($ccc_str, "***ccc_string***$i", $bb2html);
		$ccc[$i] = str_replace(array('**$@$**', '**@^@**', "\r\n"), array('[[', ']]', "\n"), $ccc_str);
		$i++;
	}

	// rudimentary tag balance checking..
	if ($check_tag_balance) { $bb2html = check_balance($bb2html); }
	if ($GLOBALS['cbparser']['state'] == 1)  { return false; } // imbalanced tags


	// xss attack prevention [99.9% safe!]..
	if ($prevent_xss) { $bb2html = xssclean($bb2html); }

	// generic entity encode
	$bb2html = htmlentities($bb2html, ENT_NOQUOTES, 'utf-8');
	$bb2html = str_replace('[sp]', '&nbsp;', $bb2html);


	// process links?
	$GLOBALS['is_spammer'] = false;
	$bb2html = process_links($bb2html);

	//	no tinned pidgeon!! (you probably have to be Scottish to understand this joke)
	if ($prevent_spam and $GLOBALS['is_spammer']) {
		$GLOBALS['cbparser']['state'] = 3;
		$GLOBALS['cbparser']['warning_message'] .= $GLOBALS['cbparser']['warnings']['spammer'];
		$GLOBALS['cbparser']['text'] = '';
		//return false; // zero-tolerance!
		return $GLOBALS['spammer_return_string']; // zero-tolerance!
	}

	// the bbcode proper..

	// news headline block
	$bb2html = str_replace('[news]', '<div class="cb-news">', $bb2html);
	$bb2html = str_replace('[/news]', '<!--news--></div>', $bb2html);

	// references - we need to create the whole string first, for the str_replace
	$r1 = '<a class="cb-refs-title" href="#refs-'.$id_title.'" title="'.$cb_ref_title.'">';
	$bb2html = str_replace('[ref]', $r1 , $bb2html);
	$bb2html = str_replace('[/ref]', '<!--ref--></a>', $bb2html);
	$ref_start = '<div class="cb-ref" id="refs-'.$id_title.'">
<a class="ref-title" title="back to the text" href="javascript:history.go(-1)">references:</a>
<div class="reftext">';
	$bb2html = str_replace('[reftxt]', $ref_start , $bb2html);
	$bb2html = str_replace('[/reftxt]', '<!--reftxt-->
</div>
</div>', $bb2html);

	// ordinary transformations..

	// we rely on the browser producing \r\n (DOS) carriage returns, as per spec.
	$bb2html = str_replace("\r",'<br />', $bb2html);		// the \n remains, and makes the raw html readable
	$bb2html = str_replace('[b]', '<strong>', $bb2html);	// ie. "\r\n" becomes "<br />\n"
	$bb2html = str_replace('[/b]', '</strong>', $bb2html);
	$bb2html = str_replace('[i]', '<em>', $bb2html);
	$bb2html = str_replace('[/i]', '</em>', $bb2html);
	$bb2html = str_replace('[u]', '<span class="underline">', $bb2html);
	$bb2html = str_replace('[/u]', '<!--u--></span>', $bb2html);
	$bb2html = str_replace('[big]', '<big>', $bb2html);
	$bb2html = str_replace('[/big]', '</big>', $bb2html);
	$bb2html = str_replace('[sm]', '<small>', $bb2html);
	$bb2html = str_replace('[/sm]', '</small>', $bb2html);

	// tables (couldn't resist this, too handy)
	$bb2html = str_replace('[t]', '<div class="cb-table">', $bb2html);
	$bb2html = str_replace('[bt]', '<div class="cb-table-b">', $bb2html);
	$bb2html = str_replace('[st]', '<div class="cb-table-s">', $bb2html);
	$bb2html = str_replace('[/t]', '<!--table--></div><div class="clear"></div>', $bb2html);
	$bb2html = str_replace('[c]', '<div class="cell">', $bb2html);	// regular 50% width
	$bb2html = str_replace('[c2]', '<div class="cell">', $bb2html);	// in-case they do an intuitive thang
	$bb2html = str_replace('[c1]', '<div class="cell1">', $bb2html);	// cell data 100% width
	$bb2html = str_replace('[c3]', '<div class="cell3">', $bb2html);
	$bb2html = str_replace('[c4]', '<div class="cell4">', $bb2html);
	$bb2html = str_replace('[c5]', '<div class="cell5">', $bb2html);
	$bb2html = str_replace('[/c]', '<!--end-cell--></div>', $bb2html);
	$bb2html = str_replace('[r]', '<div class="cb-tablerow">', $bb2html);	// a row
	$bb2html = str_replace('[/r]', '<!--row--></div>', $bb2html);

	$bb2html = str_replace('[box]', '<span class="box">', $bb2html);
	$bb2html = str_replace('[/box]', '<!--box--></span>', $bb2html);
	$bb2html = str_replace('[bbox]', '<div class="box">', $bb2html);
	$bb2html = str_replace('[/bbox]', '<!--box--></div>', $bb2html);

	// simple lists..
	$bb2html = str_replace('[*]', '<li>', $bb2html);
	$bb2html = str_replace('[/*]', '</li>', $bb2html);
	$bb2html = str_replace('[ul]', '<ul>', $bb2html);
	$bb2html = str_replace('[/ul]', '</ul>', $bb2html);
	$bb2html = str_replace('[list]', '<ul>', $bb2html);
	$bb2html = str_replace('[/list]', '</ul>', $bb2html);
	$bb2html = str_replace('[ol]', '<ol>', $bb2html);
	$bb2html = str_replace('[/ol]', '</ol>', $bb2html);

	// fix up gaps..
	$bb2html = str_replace('</li><br />', '</li>', $bb2html);
	$bb2html = str_replace('<ul><br />', '<ul>', $bb2html);
	$bb2html = str_replace('</ul><br />', '</ul>', $bb2html);
	$bb2html = str_replace('<ol><br />', '<ol>', $bb2html);
	$bb2html = str_replace('</ol><br />', '</ol>', $bb2html);
	

	// smileys..
	//if (file_exists($_SERVER['DOCUMENT_ROOT'].$smiley_folder)) {
		$bb2html = str_replace(':lol:', '<img alt="smiley for :lol:" title=":lol:" src="'
		.$smiley_folder.'lol.gif" />', $bb2html);
		$bb2html = str_replace(':ken:', '<img alt="smiley for :ken:" title=":ken:" src="'
		.$smiley_folder.'ken.gif" />', $bb2html);
		$bb2html = str_replace(':evil:', '<img alt="smiley for :evil:" title=":evil:" src="'
		.$smiley_folder.'evil.gif" />', $bb2html);
		$bb2html = str_replace(':D', '<img alt="smiley for :D" title=":D" src="'
		.$smiley_folder.'grin.gif" />', $bb2html);
		$bb2html = str_replace(':)', '<img alt="smiley for :)" title=":)" src="'
		.$smiley_folder.'smile.gif" />', $bb2html);
		$bb2html = str_replace(';)', '<img alt="smiley for ;)" title=";)" src="'
		.$smiley_folder.'wink.gif" />', $bb2html);
		$bb2html = str_replace(':eek:', '<img alt="smiley for :eek:" title=":eek:" src="'
		.$smiley_folder.'eek.gif" />', $bb2html);
		$bb2html = str_replace(':geek:', '<img alt="smiley for :geek:" title=":geek:" src="'
		.$smiley_folder.'geek.gif" />', $bb2html);
		$bb2html = str_replace(':roll:', '<img alt="smiley for :roll:" title=":roll:" src="'
		.$smiley_folder.'roll.gif" />', $bb2html);
		$bb2html = str_replace(':erm:', '<img alt="smiley for :erm:" title=":erm:" src="'
		.$smiley_folder.'erm.gif" />', $bb2html);
		$bb2html = str_replace(':cool:', '<img alt="smiley for :cool:" title=":cool:" src="'
		.$smiley_folder.'cool.gif" />', $bb2html);
		$bb2html = str_replace(':blank:', '<img alt="smiley for :blank:" title=":blank:" src="'
		.$smiley_folder.'blank.gif" />', $bb2html);
		$bb2html = str_replace(':idea:', '<img alt="smiley for :idea:" title=":idea:" src="'
		.$smiley_folder.'idea.gif" />', $bb2html);
		$bb2html = str_replace(':ehh:', '<img alt="smiley for :ehh:" title=":ehh:" src="'
		.$smiley_folder.'ehh.gif" />', $bb2html);
		$bb2html = str_replace(':aargh:', '<img alt="smiley for :aargh:" title=":aargh:" src="'
		.$smiley_folder.'aargh.gif" />', $bb2html);
		$bb2html = str_replace(':-[', '<img alt="smiley for :-[" title=":-[" src="'
		.$smiley_folder.'embarassed.gif" />', $bb2html);
	//}

	// anchors and stuff..
	$bb2html = str_replace('[img]', '<img class="cb-img" src="', $bb2html);
	$bb2html = str_replace('[/img]', '" alt="an image" />', $bb2html);
	// encode the URI part? //:2do.
	// what about custom alt tags. hmm. //:2do.


	// clickable mail URL ..
	$bb2html = preg_replace_callback("/\[mmail\=(.+?)\](.+?)\[\/mmail\]/i", "create_mmail", $bb2html);
	$bb2html = preg_replace_callback("/\[email\=(.+?)\](.+?)\[\/email\]/i", "create_mail", $bb2html);

	// other URLs..
	$bb2html = str_replace('[url]', '<br /><br /><div class="warning">please check your URL bbcode syntax!!!</div><br /><br />', $bb2html);
	$bb2html = str_replace('[eurl=', '<a class="eurl" onclick="window.open(this.href); return false;" href=', $bb2html);
	$bb2html = str_replace('[turl=', '<a class="turl" title=', $bb2html); /* title-only url */
	$bb2html = str_replace('[purl=', '[url=', $bb2html); /* title-only url */
	$bb2html = str_replace('[url=', '<a class="url" href=', $bb2html); /* on-page url */
	$bb2html = str_replace('[/url]', '<!--url--></a>', $bb2html);
	// encode the URI part? //:2do.
	// check for spammer strings in URL right here //:2do.

	// floaters..
	$bb2html = str_replace('[right]', '<div class="right">', $bb2html);
	$bb2html = str_replace('[/right]', '<!--right--></div>', $bb2html);
	$bb2html = str_replace('[left]', '<div class="left">', $bb2html);
	$bb2html = str_replace('[/left]', '<!--left--></div>', $bb2html);

	// code
	$bb2html = str_replace('[tt]', '<tt>', $bb2html);
	$bb2html = str_replace('[/tt]', '</tt>', $bb2html);
	$bb2html = str_replace('[code]', '<span class="code">', $bb2html);
	$bb2html = str_replace('[/code]', '<!--code--></span>', $bb2html);
	$bb2html = str_replace('[coderz]', '<div class="coderz">', $bb2html);
	$bb2html = str_replace('[/coderz]', '<!--coderz--></div>', $bb2html);

	// simple quotes..
	$bb2html = str_replace('[quote]', '<cite>', $bb2html);
	$bb2html = str_replace('[/quote]', '</cite>', $bb2html);

	// divisions..
	$bb2html = str_replace('[hr]', '<hr class="cb-hr" />', $bb2html);
	$bb2html = str_replace('[hr2]', '<hr class="cb-hr2" />', $bb2html);
	$bb2html = str_replace('[hr3]', '<hr class="cb-hr3" />', $bb2html);
	$bb2html = str_replace('[hr4]', '<hr class="cb-hr4" />', $bb2html);
	$bb2html = str_replace('[hrr]', '<hr class="cb-hr-regular" />', $bb2html);
	$bb2html = str_replace('[block]', '<blockquote><div class="blockquote">', $bb2html);
	$bb2html = str_replace('[/block]', '</div></blockquote>', $bb2html);
	$bb2html = str_replace('</div></blockquote><br />', '</div></blockquote>', $bb2html);

	$bb2html = str_replace('[mid]', '<div class="cb-center">', $bb2html);
	$bb2html = str_replace('[/mid]', '<!--mid--></div>', $bb2html);

	// dropcaps. five flavours, small up to large.. [dc1]I[/dc] -> [dc5]W[/dc]
	$bb2html = str_replace('[dc1]', '<span class="dropcap1">', $bb2html);
	$bb2html = str_replace('[dc2]', '<span class="dropcap2">', $bb2html);
	$bb2html = str_replace('[dc3]', '<span class="dropcap3">', $bb2html);
	$bb2html = str_replace('[dc4]', '<span class="dropcap4">', $bb2html);
	$bb2html = str_replace('[dc5]', '<span class="dropcap5">', $bb2html);
	$bb2html = str_replace('[/dc]', '<!--dc--></span>', $bb2html);

	$bb2html = str_replace('[h2]', '<h2>', $bb2html);
	$bb2html = str_replace('[/h2]', '</h2>', $bb2html);
	$bb2html = str_replace('[h3]', '<h3>', $bb2html);
	$bb2html = str_replace('[/h3]', '</h3>', $bb2html);
	$bb2html = str_replace('[h4]', '<h4>', $bb2html);
	$bb2html = str_replace('[/h4]', '</h4>', $bb2html);
	$bb2html = str_replace('[h5]', '<h5>', $bb2html);
	$bb2html = str_replace('[/h5]', '</h5>', $bb2html);
	$bb2html = str_replace('[h6]', '<h6>', $bb2html);
	$bb2html = str_replace('[/h6]', '</h6>', $bb2html);

	// fix up input spacings..
	$bb2html = str_replace('</h2><br />', '</h2>', $bb2html);
	$bb2html = str_replace('</h3><br />', '</h3>', $bb2html);
	$bb2html = str_replace('</h4><br />', '</h4>', $bb2html);
	$bb2html = str_replace('</h5><br />', '</h5>', $bb2html);
	$bb2html = str_replace('</h6><br />', '</h6>', $bb2html);

	// oh, all right then..
	// my [color=red]colour[/color] [color=blue]test[/color] [color=#C5BB41]test[/color]
	$bb2html = preg_replace('/\[color\=(.+?)\](.+?)\[\/color\]/is', "<span style=\"color:$1\">$2<!--color--></span>", $bb2html);

	// I noticed someone trying to do these at the org. use standard pixel sizes
	$bb2html = preg_replace('/\[size\=(.+?)\](.+?)\[\/size\]/is', "<span style=\"font-size:$1px\">$2<!--size--></span>", $bb2html);

	// for URL's, and InfiniTags™..
	$bb2html = str_replace('[', ' <', $bb2html); // you can just replace < and >  with [ and ] in your bbcode
	$bb2html = str_replace(']', ' >', $bb2html); // for instance, [strike] cool [/strike] would work!
	$bb2html = str_replace('/ >', '/>', $bb2html); // self-closers
	$bb2html = str_replace('-- >', '-->', $bb2html); // close comments

	// get back any real square brackets..
	$bb2html = str_replace('**$@$**', '[', $bb2html);
	$bb2html = str_replace('**@^@**', ']', $bb2html);

	// prevent some twat running arbitary php commands on our web server
	// I may roll this into the xss prevention and just keep it all enabled. hmm.
	$php_str = $bb2html;
	$bb2html = preg_replace("/<\?(.*)\? ?>/is", "<strong>script-kiddie prank: &lt;?\\1 ?&gt;</strong>", $bb2html);
	if ($php_str != $bb2html) { $GLOBALS['cbparser']['state'] = 5; }

	// re-insert the preformatted text blocks..
	$cp = count($pre) + 9998;
	for ($i=9999;$i <= $cp;$i++) {
		$bb2html = str_replace("***pre_string***$i", '<pre>'.$pre[$i].'</pre>', $bb2html);
	}
if (!empty($GLOBALS['do_debug'])) debug("\n".'$bb2html (after pre back in): '."$bb2html\n\n");// :debug:
	// re-insert the cool colored code..
	// we fix-up the output, too, make it xhtml strict.
	$cp = count($ccc) - 1;
	for ($i=0 ; $i <= $cp ; $i++) {
		$tmp_str = substr($ccc[$i], 5, -6);
		$tmp_str = highlight_string(stripslashes($tmp_str), true);
		$tmp_str = str_replace('font color="', 'span style="color:', $tmp_str);
		$tmp_str = str_replace('font', 'span', $tmp_str); // erm.
		if (get_magic_quotes_gpc()) $tmp_str = addslashes($tmp_str);
		$bb2html = str_replace("***ccc_string***$i", '<div class="cb-ccc">'.$tmp_str.'<!--ccccode--></div>', $bb2html);
	}

	$bb2html = slash_it($bb2html);
if (!empty($GLOBALS['do_debug'])) { debug("\n\n".'cbparser outgoing [$bb2html]: '. $bb2html ."\n\n"); }// :debug:
	if ($GLOBALS['trans_warp_drive']) { $bb2html = strrev($bb2html); }

	return $bb2html;

}/* end function bb2html()
*/












/*
function html2bb()   */

function html2bb() {
global $cb_ref_title, $smiley_folder;
if (func_num_args() == 2) { $id_title = func_get_arg(1); } else { $id_title = ''; }
$html2bb = func_get_arg(0);

	// legacy bbcode conversion..
	if (stristr($html2bb, '<font') or stristr($html2bb, 'align=')  or stristr($html2bb, 'border=') or stristr($html2bb, '<i>') or stristr($html2bb, 'target=') or stristr($html2bb, '<u>') or stristr($html2bb, '<br>')) {
		return oldhtml2bb($html2bb, $id_title);
	}

	// we presume..
	$GLOBALS['cbparser']['state'] = 0;

	// pre-formatted text
	$pre = array();$i=9999;
	while ($pre_str = stristr($html2bb,'<pre>')) {
		$pre_str = substr($pre_str,0,strpos($pre_str,'</pre>')+6);
		$html2bb = str_replace($pre_str, "***pre_string***$i", $html2bb);
		$pre[$i] = str_replace("\n","\r\n",$pre_str);
		$i++;
	}

	// cool colored code
	$ccc = array();$i=0;
	while ($ccc_str = stristr($html2bb,'<div class="cb-ccc">')) {
		$ccc_str = substr($ccc_str,0,strpos($ccc_str,'<!--ccccode--></div>')+20);
		$html2bb = str_replace($ccc_str, "***ccc_string***$i", $html2bb);
		$ccc[$i] = str_replace("<br />","\r\n",$ccc_str);
		$i++;
	}

	$html2bb = str_replace('[', '***^***', $html2bb);
	$html2bb = str_replace(']', '**@^@**', $html2bb);

	// news
	$html2bb = str_replace('<div class="cb-news">', '[news]', $html2bb);
	$html2bb = str_replace('<!--news--></div>', '[/news]', $html2bb);

	// references..
	$r1 = '<a class="cb-refs-title" href="#refs-'.$id_title.'" title="'.$cb_ref_title.'">';
	$html2bb = str_replace($r1, "[ref]", $html2bb);
	$html2bb = str_replace('<!--ref--></a>', '[/ref]', $html2bb);
	$ref_start = '<div class="cb-ref" id="refs-'.$id_title.'">
<a class="ref-title" title="back to the text" href="javascript:history.go(-1)">references:</a>
<div class="reftext">';
	$html2bb = str_replace($ref_start, '[reftxt]', $html2bb);
	$html2bb = str_replace('<!--reftxt-->
</div>
</div>', '[/reftxt]', $html2bb);

	// let's remove all the linefeeds, unix
	$html2bb = str_replace(chr(10), '', $html2bb); //		"\n"
	// and Mac (windoze uses both)
	$html2bb = str_replace(chr(13), '', $html2bb); //		"\r"

	// 'ordinary' transformations..
	$html2bb = str_replace('<strong>', '[b]', $html2bb);
	$html2bb = str_replace('</strong>', '[/b]', $html2bb);
	$html2bb = str_replace('<em>', '[i]', $html2bb);
	$html2bb = str_replace('</em>', '[/i]', $html2bb);
	$html2bb = str_replace('<span class="underline">', '[u]', $html2bb);
	$html2bb = str_replace('<!--u--></span>', '[/u]', $html2bb);
	$html2bb = str_replace('<big>', '[big]', $html2bb);
	$html2bb = str_replace('</big>', '[/big]', $html2bb);
	$html2bb = str_replace('<small>', '[sm]', $html2bb);
	$html2bb = str_replace('</small>', '[/sm]', $html2bb);

	// tables..
	$html2bb = str_replace('<div class="cb-table">','[t]',  $html2bb);
	$html2bb = str_replace('<div class="cb-table-b">','[bt]',  $html2bb);
	$html2bb = str_replace('<div class="cb-table-s">','[st]',  $html2bb);
	$html2bb = str_replace('<!--table--></div><div class="clear"></div>','[/t]',  $html2bb);
	$html2bb = str_replace('<div class="cell">','[c]',  $html2bb);
	$html2bb = str_replace('<div class="cell1">','[c1]',  $html2bb);
	$html2bb = str_replace('<div class="cell3">','[c3]',  $html2bb);
	$html2bb = str_replace('<div class="cell4">','[c4]',  $html2bb);
	$html2bb = str_replace('<div class="cell5">','[c5]',  $html2bb);
	$html2bb = str_replace('<!--end-cell--></div>','[/c]',  $html2bb);
	$html2bb = str_replace('<div class="cb-tablerow">','[r]',  $html2bb);
	$html2bb = str_replace('<!--row--></div>','[/r]',  $html2bb);

	$html2bb = str_replace('<span class="box">','[box]',  $html2bb);
	$html2bb = str_replace('<!--box--></span>','[/box]',  $html2bb);
	$html2bb = str_replace('<div class="box">','[bbox]',  $html2bb);
	$html2bb = str_replace('<!--box--></div>','[/bbox]',  $html2bb);

	// lists. we like these.
	$html2bb = str_replace('<li>', '[*]', $html2bb);
	$html2bb = str_replace('</li>', '[/*]<br />', $html2bb); // we convert <br /> to \r\n later..
	$html2bb = str_replace('<ul>', '[list]<br />', $html2bb);
	$html2bb = str_replace('</ul>', '[/list]<br />', $html2bb);
	$html2bb = str_replace('<ol>', '[ol]<br />', $html2bb);
	$html2bb = str_replace('</ol>', '[/ol]<br />', $html2bb);

	// legacy "smilie" locations..
	if (stristr($html2bb, 'smilie')) { $smiley_str = 'smilie'; } else { $smiley_str = 'smiley'; }

	// smileys..
	//if (file_exists($_SERVER['DOCUMENT_ROOT'].$smiley_folder)) {
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :lol:" title=":lol:" src="'
		.$smiley_folder.'lol.gif" />',':lol:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :ken:" title=":ken:" src="'
		.$smiley_folder.'ken.gif" />',':ken:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :evil:" title=":evil:" src="'
		.$smiley_folder.'evil.gif" />',':evil:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :D" title=":D" src="'
		.$smiley_folder.'grin.gif" />',':D',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :)" title=":)" src="'
		.$smiley_folder.'smile.gif" />',':)',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for ;)" title=";)" src="'
		.$smiley_folder.'wink.gif" />',';)',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :eek:" title=":eek:" src="'
		.$smiley_folder.'eek.gif" />',':eek:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :geek:" title=":geek:" src="'
		.$smiley_folder.'geek.gif" />',':geek:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :roll:" title=":roll:" src="'
		.$smiley_folder.'roll.gif" />',':roll:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :erm:" title=":erm:" src="'
		.$smiley_folder.'erm.gif" />',':erm:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :cool:" title=":cool:" src="'
		.$smiley_folder.'cool.gif" />',':cool:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :blank:" title=":blank:" src="'
		.$smiley_folder.'blank.gif" />',':blank:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :idea:" title=":idea:" src="'
		.$smiley_folder.'idea.gif" />',':idea:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :ehh:" title=":ehh:" src="'
		.$smiley_folder.'ehh.gif" />',':ehh:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :aargh:" title=":aargh:" src="'
		.$smiley_folder.'aargh.gif" />',':aargh:',  $html2bb);
		$html2bb = str_replace('<img alt="'.$smiley_str.' for :-[" title=":-[" src="'
		.$smiley_folder.'embarassed.gif" />',':-[',  $html2bb);
	//}

	// more stuff

	// images..
	$html2bb = str_replace('<img class="cb-img" src="', '[img]', $html2bb);
	$html2bb = str_replace('<img class="cb-img-right" src="', '[img]', $html2bb);// deprecation in action!
	$html2bb = str_replace('<img src="', '[img]', $html2bb); // catch certain legacy entries
	$html2bb = str_replace('<img class="cb-img-left" src="', '[img]', $html2bb);
	$html2bb = str_replace('" alt="an image" />', '[/img]', $html2bb);


	// anchors, etc..

	// da "email" tags..
	$html2bb = preg_replace_callback("/\<a class=\"cb-mail\" title=\"mail me\!\" href\=(.+?)\>(.+?)\<\!--mail--\><\/a\>/i", "get_mmail", $html2bb);

	$html2bb = preg_replace_callback("/\<a title\=\"mail me\!\" href\=(.+?)\>(.+?)\<\/a\>/i",
	"get_email", $html2bb);

	$html2bb = str_replace('<a onclick="window.open(this.href); return false;" href=','[eurl=', $html2bb);
	$html2bb = str_replace('<a class="eurl" onclick="window.open(this.href); return false;" href=','[eurl=', $html2bb);
	$html2bb = str_replace('<a class="turl" title=','[turl=', $html2bb);
	$html2bb = str_replace('<a class="purl" href=','[url=', $html2bb);
	$html2bb = str_replace('<a class="url" href=','[url=', $html2bb);
	$html2bb = str_replace('<!--url--></a>', '[/url]', $html2bb);
	$html2bb = str_replace('</a>', '[/url]', $html2bb); // catch for early beta html

	// floaters..
	$html2bb = str_replace('<div class="right">','[right]', $html2bb);
	$html2bb = str_replace('<!--right--></div>','[/right]', $html2bb);
	$html2bb = str_replace('<div class="left">','[left]', $html2bb);
	$html2bb = str_replace('<!--left--></div>','[/left]', $html2bb);

	// code..
	$html2bb = str_replace('<tt>', '[tt]', $html2bb);
	$html2bb = str_replace('</tt>', '[/tt]', $html2bb);
	$html2bb = str_replace('<span class="code">', '[code]', $html2bb);
	$html2bb = str_replace('<!--code--></span>', '[/code]', $html2bb);
	$html2bb = str_replace('<div class="coderz">', '[coderz]', $html2bb);
	$html2bb = str_replace('<!--coderz--></div>', '[/coderz]', $html2bb);


	$html2bb= str_replace('<cite>', '[quote]', $html2bb);
	$html2bb= str_replace('</cite>', '[/quote]', $html2bb);

	// etc..
	$html2bb = str_replace('<hr class="cb-hr" />', '[hr]', $html2bb);
	$html2bb= str_replace('<hr class="cb-hr2" />', '[hr2]', $html2bb);
	$html2bb= str_replace('<hr class="cb-hr3" />', '[hr3]', $html2bb);
	$html2bb= str_replace('<hr class="cb-hr4" />', '[hr4]', $html2bb);
	$html2bb= str_replace('<hr class="cb-hr-regular" />', '[hrr]', $html2bb);
	$html2bb = str_replace('<blockquote><div class="blockquote">', '[block]', $html2bb);
	$html2bb = str_replace('</div></blockquote>', '[/block]<br />', $html2bb);

	$html2bb = str_replace('<div class="cb-center">', '[mid]', $html2bb);
	$html2bb = str_replace('<!--mid--></div>', '[/mid]', $html2bb);

	// the irresistible dropcaps (good name for a band)
	$html2bb = str_replace('<span class="dropcap1">', '[dc1]', $html2bb);
	$html2bb = str_replace('<span class="dropcap2">', '[dc2]', $html2bb);
	$html2bb = str_replace('<span class="dropcap3">', '[dc3]', $html2bb);
	$html2bb = str_replace('<span class="dropcap4">', '[dc4]', $html2bb);
	$html2bb = str_replace('<span class="dropcap5">', '[dc5]', $html2bb);
	$html2bb = str_replace('<!--dc--></span>', '[/dc]', $html2bb);

	$html2bb = str_replace('<h2>', '[h2]', $html2bb);
	$html2bb = str_replace('</h2>', '[/h2]<br />', $html2bb);
	$html2bb = str_replace('<h3>', '[h3]', $html2bb);
	$html2bb = str_replace('</h3>', '[/h3]<br />', $html2bb);
	$html2bb = str_replace('<h4>', '[h4]', $html2bb);
	$html2bb = str_replace('</h4>', '[/h4]<br />', $html2bb);
	$html2bb = str_replace('<h5>', '[h5]', $html2bb);
	$html2bb = str_replace('</h5>', '[/h5]<br />', $html2bb);
	$html2bb = str_replace('<h6>', '[h6]', $html2bb);
	$html2bb = str_replace('</h6>', '[/h6]<br />', $html2bb);

	// pfff..
	$html2bb = preg_replace("/\<span style\=\"color:(.+?)\"\>(.+?)\<\!--color--\>\<\/span\>/is", "[color=$1]$2[/color]", $html2bb);

	// size, in pixels.
	$html2bb = preg_replace("/\<span style\=\"font-size:(.+?)px\"\>(.+?)\<\!--size--\>\<\/span\>/is", "[size=$1]$2[/size]", $html2bb);

	// bring back the brackets
	$html2bb = str_replace('***^***', '[[', $html2bb);
	$html2bb = str_replace('**@^@**', ']]', $html2bb);

	// I just threw this down here for the list fixes.
	$html2bb = str_replace('<br />', "\r\n", $html2bb);
	$html2bb = str_replace('&nbsp;', '[sp]', $html2bb);

	// InfiniTag™ enablers!
	$html2bb = str_replace(' <', '[', $html2bb);
	$html2bb = str_replace(' >', ']', $html2bb); 
	$html2bb = str_replace('-->', '--]', $html2bb); // comments within comments!
	$html2bb = str_replace('/>', '/]', $html2bb); // self-closers

	//$html2bb = str_replace('&amp;', '&', $html2bb);

	$cp = count($ccc) - 1;
	for ($i=0 ; $i <= $cp ; $i++) {
		$html2bb = str_replace("***ccc_string***$i", '[ccc]'
			.trim(strip_tags($ccc[$i])).'[/ccc]', $html2bb);
	}

	$cp = count($pre) + 9998; // it all hinges on simple arithmetic
	for ($i=9999 ; $i <= $cp ; $i++) {
		$html2bb = str_replace("***pre_string***$i", '[pre]'.substr($pre[$i],5,-6).'[/pre]', $html2bb);
	}
if (!empty($GLOBALS['do_debug'])) { debug("\n\n".'cbparser outgoing [$html2bb]: '. $html2bb ."\n\n"); }// :debug:
//if (!empty($GLOBALS['do_debug'])) { debug('$GLOBALS: '."\t".print_r($GLOBALS, true)."\n\n\n"); }// :debug:

	return ($html2bb);
}




/* 
	legacy bbcode conversion..
	seamless upgrading! ish.
*/
/*
function oldhtml2bb($htmltext, $title)   */
	
function oldhtml2bb($html2bbtxt,$title) {
global $smiley_folder;
	$pre = array();$i=0;
	while ($pre_str = stristr($html2bbtxt,'<pre>')) {
		$pre_str = substr($pre_str,0,strpos($pre_str,'</pre>')+6);
		$html2bbtxt = str_replace($pre_str, "***pre_string***$i", $html2bbtxt);
		$pre[$i] = str_replace("\r\n","\n",$pre_str);
		$i++;
	}
	$html2bbtxt = str_replace('[', '***^***', $html2bbtxt);
	$html2bbtxt = str_replace(']', '**@^@**', $html2bbtxt);
	$html2bbtxt = str_replace('<table width="20%" border="0" align="right"><tr><td align="center"><span class="news"><b><big>', '[news]', $html2bbtxt);
	$html2bbtxt = str_replace('</big></b></span></td></tr></table>', '[/news]', $html2bbtxt);
		$r1 = '<a href="#refs-'.$title.'" title="'.$title.'"><font class="ref"><sup>';
	$html2bbtxt = str_replace($r1, "[ref]", $html2bbtxt);
	$r2 = '<p id="refs-'.$title.'"></p>
<font class="ref"><b><u><a title="back to the text" href="javascript:history.go(-1)">references:</a></u><br><br>1: </b></font><font class="reftext">';
	$r3 = '<p id="refs-'.$title.'"></p>
<font class="ref"><b><u><a href="javascript:history.go(-1)">references:</a></u><br><br>1: </b></font><font class="reftext">';
	$html2bbtxt = str_replace($r2, "[reftxt][ol]", $html2bbtxt);
	$html2bbtxt = str_replace($r3, "[reftxt][ol]", $html2bbtxt);
	$html2bbtxt = str_replace('<font class="ref"><b>2: </b></font><font class="reftext">', '[*]', $html2bbtxt);
	$html2bbtxt = str_replace('<font class="ref"><b>3: </b></font><font class="reftext">', '[*]', $html2bbtxt);
	$html2bbtxt = str_replace('<font class="ref"><b>4: </b></font><font class="reftext">', '[*]', $html2bbtxt);
	$html2bbtxt = str_replace('<font class="ref"><b>5: </b></font><font class="reftext">', '[*]', $html2bbtxt);
	$html2bbtxt = str_replace('</sup></font></a>', '[/ref]', $html2bbtxt);
	$html2bbtxt = str_replace('</font>', '[/ol][/reftxt]', $html2bbtxt); // you could add more refs here, if needed.
	$html2bbtxt = str_replace(chr(10), '', $html2bbtxt); //		"\n"
	$html2bbtxt = str_replace(chr(13), '', $html2bbtxt); //		"\r"
	$html2bbtxt = str_replace('<br>', "\r\n", $html2bbtxt); // and they're back!
	$html2bbtxt = str_replace('<br />', "\r\n", $html2bbtxt); // catch grepped xhtml updates!
	$html2bbtxt = str_replace('<b>', '[b]', $html2bbtxt);
	$html2bbtxt = str_replace('</b>', '[/b]', $html2bbtxt);
	$html2bbtxt = str_replace('<i>', '[i]', $html2bbtxt);
	$html2bbtxt = str_replace('</i>', '[/i]', $html2bbtxt);
	$html2bbtxt = str_replace('<u>', '[u]', $html2bbtxt);
	$html2bbtxt = str_replace('</u>', '[/u]', $html2bbtxt);
	$html2bbtxt = str_replace('<big>', '[big]', $html2bbtxt);
	$html2bbtxt = str_replace('</big>', '[/big]', $html2bbtxt);
	$html2bbtxt = str_replace('<small>', '[sm]', $html2bbtxt);
	$html2bbtxt = str_replace('</small>', '[/sm]', $html2bbtxt);
	$html2bbtxt = str_replace('<table width="100%" border=0 cellspacing=0 cellpadding=0>','[t]',  $html2bbtxt);
	$html2bbtxt = str_replace('<table width="100%" border=1 cellspacing=0 cellpadding=3>','[bt]',  $html2bbtxt);
	$html2bbtxt = str_replace('<table width="100%" border=0 cellspacing=3 cellpadding=3>','[st]',  $html2bbtxt);
	$html2bbtxt = str_replace('</table>','[/t]',  $html2bbtxt);
	$html2bbtxt = str_replace('<td valign=top>','[c]',  $html2bbtxt);
	$html2bbtxt = str_replace('<td valign=top width="50%">','[c5]',  $html2bbtxt);	// 50% width
	$html2bbtxt = str_replace('<td valign=top width="50%" align=left>','[c5l]',  $html2bbtxt);
	$html2bbtxt = str_replace('<td valign=top width="50%" align=right>','[c5r]',  $html2bbtxt);
	$html2bbtxt = str_replace('<td valign=top colspan=2>','[c2]',  $html2bbtxt);
	$html2bbtxt = str_replace('<td valign=top colspan=3>','[c3]',  $html2bbtxt);
	$html2bbtxt = str_replace('<td valign=top colspan=4>','[c4]',  $html2bbtxt);
	$html2bbtxt = str_replace('</td>','[/c]',  $html2bbtxt);
	$html2bbtxt = str_replace('<tr>','[r]',  $html2bbtxt);
	$html2bbtxt = str_replace('</tr>','[/r]',  $html2bbtxt);
	$html2bbtxt = str_replace('<li>', '[*]', $html2bbtxt);
	$html2bbtxt = str_replace('<ul>', '[list]', $html2bbtxt);
	$html2bbtxt = str_replace('</ul>', '[/list]', $html2bbtxt);

	// legacy "smilie" locations..
	if (stristr($html2bbtxt, 'smilie')) { 
		//$smiley_folder = str_replace('smiley', 'smilie', $smiley_folder); 
		$smiley_str = 'smilie';
	} else { $smiley_str = 'smiley'; }

	//if(file_exists($_SERVER['DOCUMENT_ROOT'].$smiley_folder)) {
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :lol:" title=":lol:" src="'
		.$smiley_folder.'lol.gif">',':lol:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :ken:" title=":ken:" src="'
		.$smiley_folder.'ken.gif">',':ken:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :D" title=":D" src="'
		.$smiley_folder.'grin.gif">',':D',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :eek:" title=":eek:" src="'
		.$smiley_folder.'eek.gif">',':eek:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :geek:" title=":geek:" src="'
		.$smiley_folder.'geek.gif">',':geek:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :roll:" title=":roll:" src="'
		.$smiley_folder.'roll.gif">',':roll:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :erm:" title=":erm:" src="'
		.$smiley_folder.'erm.gif">',':erm:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :cool:" title=":cool:" src="'
		.$smiley_folder.'cool.gif">',':cool:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :blank:" title=":blank:" src="'
		.$smiley_folder.'blank.gif">',':blank:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :idea:" title=":idea:" src="'
		.$smiley_folder.'idea.gif">',':idea:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :ehh:" title=":ehh:" src="'
		.$smiley_folder.'ehh.gif">',':ehh:',  $html2bbtxt);
		$html2bbtxt = str_replace('<img alt="'.$smiley_str.' for :aargh:" title=":aargh:" src="'
		.$smiley_folder.'aargh.gif">',':aargh:',  $html2bbtxt);
	//}

	$html2bbtxt = str_replace('<img border="0" src="', '[img]', $html2bbtxt);
	$html2bbtxt = str_replace('<img src="', '[img]', $html2bbtxt);
	$html2bbtxt = str_replace('<img align="right" border="0" src="', '[imgr]', $html2bbtxt);
	$html2bbtxt = str_replace('<img align="left" border="0" src="', '[imgl]', $html2bbtxt);
	$html2bbtxt = str_replace('" alt="an image">', '[/img]', $html2bbtxt);
	$html2bbtxt = str_replace('<a target="_blank" href=','[eurl=', $html2bbtxt);
	$html2bbtxt = preg_replace("/\<a title\=\"mail me!\" href\=(.*)\?subject\=/i","[murl=",$html2bbtxt);
	$html2bbtxt = preg_replace_callback("/\<a title\=\"email me!\" href\=(.*)\>(.*)\<\/a\>/i",
	"get_email", $html2bbtxt);
	$html2bbtxt = str_replace('<a title=','[turl=', $html2bbtxt);
	$html2bbtxt = str_replace('<a id="purl" href=','[url=', $html2bbtxt);
	$html2bbtxt = str_replace('</a>', '[/url]', $html2bbtxt);
	$html2bbtxt = str_replace(' >', ']', $html2bbtxt);
	$html2bbtxt = str_replace('<div class="simcode">', '[code]', $html2bbtxt);
	$html2bbtxt = str_replace('<div class="code">', '[coderz]', $html2bbtxt);
	$html2bbtxt = str_replace('</div>', '[/code]', $html2bbtxt);
	$html2bbtxt = str_replace('<hr size=1 width="70%" align=center>', '[hr]', $html2bbtxt);
	$html2bbtxt = str_replace('<hr width="50" align="left">', '[hr2]', $html2bbtxt);
	$html2bbtxt = str_replace('<hr width="100" align="left">', '[hr3]', $html2bbtxt);
	$html2bbtxt = str_replace('<hr width="150" align="left">', '[hr4]', $html2bbtxt);
	$html2bbtxt = str_replace('<blockquote>', '[block]', $html2bbtxt);
	$html2bbtxt = str_replace('</blockquote>', '[/block]', $html2bbtxt);
	$html2bbtxt = str_replace('<center>', '[mid]', $html2bbtxt);
	$html2bbtxt = str_replace('</center>', '[/mid]', $html2bbtxt);
	$html2bbtxt = str_replace('<span class="dropcap1">', '[dc1]', $html2bbtxt);
	$html2bbtxt = str_replace('<span class="dropcap2">', '[dc2]', $html2bbtxt);
	$html2bbtxt = str_replace('<span class="dropcap3">', '[dc3]', $html2bbtxt);
	$html2bbtxt = str_replace('<span class="dropcap4">', '[dc4]', $html2bbtxt);
	$html2bbtxt = str_replace('<span class="dropcap5">', '[dc5]', $html2bbtxt);
	$html2bbtxt = str_replace('<dc></span>', '[/dc]', $html2bbtxt);
	$html2bbtxt = str_replace('&nbsp;', '[sp]', $html2bbtxt);
	$html2bbtxt = str_replace('***^***', '[[', $html2bbtxt);
	$html2bbtxt = str_replace('**@^@**', ']]', $html2bbtxt);
	$html2bbtxt = str_replace('<', '[', $html2bbtxt);
	$html2bbtxt = str_replace('>', ']', $html2bbtxt);
	$cp = count($pre)-1; // it all hinges on simple arithmetic
	for($i=0;$i <= $cp;$i++) {
		$html2bbtxt = str_replace("***pre_string***$i", '[pre]'.substr($pre[$i],5,-6).'[/pre]', $html2bbtxt);
	}
	return ($html2bbtxt);
}



/*
create_mail
a callback function for the email tag	*/
function create_mail($matches) {
	$removers = array('"','\\'); // in case they add quotes
	$mail = str_replace($removers,'',$matches[1]);
	$mail = str_replace(' ', '%20', bbmashed_mail($mail));
	return '<a title="mail me!" href="'.$mail.'">'.$matches[2].'</a>';
}


/*
create *my* email
a callback function for the mmail tag	*/
function create_mmail($matches) {
global $corzblog;
	$removers = array('"','\\'); // in case they add quotes
	$mashed_address = str_replace($removers,'',$matches[1]);
	$mashed_address = bbmashed_mail($corzblog['mail_addy'].'?subject='.$mashed_address);
	$mashed_address = str_replace(' ', '%20', $mashed_address); // hmmm
	return '<a class="cb-mail" title="mail me!" href="'.$mashed_address.'\">'.$matches[2].'<!--mail--></a>';
}


/*
get email
a callback function for the html >> bbcode email tag	*/
function get_email($matches) {
	$removers = array('"','\\', 'mailto:');
	$href = str_replace($removers,'', un_mash($matches[1]));
	return '[email='.str_replace('%20', ' ', $href).']'.$matches[2].'[/email]';
}


/*
get *my* mail
a callback function for the html >> bbcode mmail tag	*/
function get_mmail($matches) {
global $corzblog;
	$removers = array('"','\\'); // not strictly necessary
	$href = str_replace($removers,'',$matches[1]);
	$href = str_replace('mailto:'.$corzblog['mail_addy'].'?subject=', '', un_mash($href));
	return '[mmail='.str_replace('%20', ' ', $href).']'.$matches[2].'[/mmail]';
}


/*
	function bbmashed_mail()

	it's handy to keep this here. used to encode your email addresses
	so the spam-bots don't chew on it.

	see <http://corz.org/engine> for more stuff like this.


*/
function bbmashed_mail($addy) {
	$addy = 'mailto:'.$addy;
	for ($i=0;$i<strlen($addy);$i++) { $letters[] = $addy[$i]; }
	while (list($key, $val) = each($letters)) {
		$r = rand(0,20);
		if (($r > 9) and ($letters[$key] != ' ')) { $letters[$key] = '&#'.ord($letters[$key]).';';}
	}
	$addy = implode('', $letters);
	return str_replace(' ', '%20', $addy);
}/*
end function mashed_mail()	*/



/* 
un-mash an email address, a tricky business */
function un_mash($string) {
	$entities = array();
	for ($i=32; $i<256; $i++) {
		$entities['orig'][$i] = '&#'.$i.';';
		$entities['new'][$i] = chr($i);
	} // now we have a translations array..
	return str_replace($entities['orig'], $entities['new'], $string);
}

// add slashes to a string, or don't..
function slash_it($string) {
	if (get_magic_quotes_gpc()) { 
		return stripslashes($string);
	} else {
		return $string;
	}
}


/* 
	make a xhtml strict valid id..

	this function exists in the main corzblog functions,
	but cbparser goes out on its own, so...
								*/
function make_valid_id ($title) {
	$title = str_replace(' ', '-', strip_tags($title));
	$id_title = preg_replace("/[^a-z0-9-]*/i", '', $title);
	while (is_numeric((substr($id_title, 0, 1))) or substr($id_title, 0, 1) == '-') {
		$id_title = substr($id_title, 1);
	}
	return trim(str_replace('--', '-',$id_title));
}


/*
encode to html entities (for <pre> tags	*/
function encode($string) {
	//$string = str_replace("\r\n", "\n", slash_it($string));
	$string = str_replace("\r\n", "\n", $string);
	$string = str_replace(array('[pre]','[/pre]'),'', $string );
	return htmlentities($string, ENT_NOQUOTES, 'utf-8'); // this is plenty
}



/*
	xss clean-up
	clean up against potential xss attacks 

	adapted from the bitflux xss prevention techniques..
	http://blog.bitflux.ch/wiki/XSS_Prevention

	any comments or suggestions about this to 
	security at corz dot org, ta.
*/
function xssclean($string) {
	
	// we'll see if it still matches at the end of all this..
	if (get_magic_quotes_gpc()) {
		$string = stripslashes($string);
	}
	$input = $string; 

	// fix &entitiy\n; (except those named above)
	$string = preg_replace('#(&\#*\w+)[\x00-\x20]+;#us',"$1;",$string);
	$string = preg_replace('#(&\#x*)([0-9A-F]+);*#ius',"$1$2;",$string);
	$string = html_entity_decode($string, ENT_COMPAT);
	//$string = html_entity_decode($string, ENT_COMPAT, "utf-8"); // if your php is capable of this :pref:

	// remove "on" and other unnecessary attributes (we specify them all to prevent words like "one" being affected)
	$string = preg_replace('#(\[[^\]]+[\x00-\x20\"\'])(onabort|onactivate|onafterprint|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur|onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror|onerrorupdate|onfilterchange|onfinish|onfocus|onfocusin|onfocusout|onhelp|onkeydown|onkeypress|onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onresize|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onselect|onselectionchange|onselectstart|onstart|onstop|onsubmit|onunload|xmlns|datasrc|src|lowsrc|dynsrc)[^\]]*\]#isUu',"$1]",$string);

	// remove javascript and vbscript..
	$string = preg_replace('#([a-z]*)[\x00-\x20]*=?[\x00-\x20]*([\`\'\"]*)[\\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu','$1=$2nojavascript...',$string);
	$string = preg_replace('#([a-z]*)[\x00-\x20]*=?([\'\"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu','$1=$2novbscript...',$string);

	// style expression hacks. only works in buggy ie... (fer fuxake! get a browser!)
	$string = preg_replace('#(\[[^\]]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*expression[\x00-\x20]*\([^\]]*>#iUs',"$1\]",$string);
	$string = preg_replace('#(\[[^\]]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*behaviour[\x00-\x20]*\([^\]]*>#iUs',"$1\]",$string);
	$string = preg_replace('#(\[[^\]]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^\]]*\]>#iUus',"$1\]",$string);

	// remove namespaced elements..
	$string = preg_replace('#\[/*\w+:\w[^\]]*\]#is',"",$string);

	// the really fun <tags>..
	do {
		$oldstring = $string;
		$string = preg_replace('#\[/*(applet|meta|xml|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|base|sourcetext|parsererror)[^[]*\]#is',"",$string);
	} while ($oldstring != $string); // loop through to catch tricky doubles
	
	//$string = html_entity encode($string, ENT_COMPAT);

	// make a note: someone tried to wonk-up the site
	if ($input !== $string) { $GLOBALS['cbparser']['state'] = 5; }

	// leave no trace..
	if (get_magic_quotes_gpc()) {
		$string = addslashes($string); 
	}
	return $string;
}


// check balance and attempt to close some tags for final publishing
function check_balance($bb2html) {
	// some tags would be pointless to attempt to close, like image tags
	// and lists, and such. better if they just fix those themselves.
	// could still use a '[img] => [/img]' type array, and include more tags.
	$GLOBALS['cbparser']['close_tags'] = '';
	$tags_to_close = array(
		'[b]',
		'[i]',
		'[u]',
		'[big]',
		'[sm]',
		'[box]',
		'[bbox]',
		'[ul]',
		'[list]',
		'[ol]',
		'[left]',
		'[right]',
		'[tt]',
		'[code]', 
		'[coderz]',
		'[block]',
		'[mid]',
		'[h2]',
		'[h3]',
		'[h4]',
		'[h5]',
		'[h6]',
		'[quote]',
		'[color]');

	foreach ($tags_to_close as $key => $value) {
		
		$open = substr_count($bb2html, $value);
		$close_tag = '[/'.substr($value, 1);

		while (substr_count($bb2html, $close_tag) < $open) {				
			$bb2html .= $close_tag;
			$GLOBALS['cbparser']['close_tags'] .= $close_tag;
			$GLOBALS['cbparser']['state'] = 2;
		}
	}

	$GLOBALS['cbparser']['text'] .= $GLOBALS['cbparser']['close_tags'];

	if ($GLOBALS['cbparser']['state'] == 2) {
		$GLOBALS['cbparser']['warning_message'] .= $GLOBALS['cbparser']['warnings']['balance_fixed'];
	}

	// some sums..
	$check_string = preg_replace("/\[(.+)\/\]/Ui","",$bb2html); // self-closers
	$check_string = preg_replace("/\[\!--(.+)--\]/i","",$check_string); // we support comments!
	$removers = array('[hr]','[hr2]','[hr3]','[hr4]','[sp]','[*]','[/*]');
	$check_string = str_replace($removers, '', $check_string);

	if ( ((substr_count($check_string, "[")) != (substr_count($check_string, "]")))
	or  ((substr_count($check_string, "[/")) != ((substr_count($check_string, "[")) / 2))
	// a couple of common errors (definitely the main culprits for tag mixing errors)..
	or  (substr_count($check_string, "[b]")) != (substr_count($check_string, "[/b]"))
	or  (substr_count($check_string, "[i]")) != (substr_count($check_string, "[/i]")) ) {
		$GLOBALS['cbparser']['state'] = 1;
		$GLOBALS['cbparser']['warning_message'] .= $GLOBALS['cbparser']['warnings']['imbalanced'];
		return false;
	}

if (!empty($GLOBALS['do_debug'])) { debug("\n".'$bb2html Final: '."$bb2html\n\n");  }// :debug:

	return $bb2html;
}

// another possibility is to scan the comment and work out which tags are used, close them.
// simply create a no-check list of non-closing tags to check against, and close others.
// the non-symetrical tags can cause problems, though.


/*
	check the URL's
	if the post is from a known spammer, set $GLOBALS['is_spammer'] to true.
							  */
function process_links($bb2html) {
	/*
		this is in two parts. first we check against our list of known spammer strings
		(generally domains). In the future, I'd hope to hook this up to some reliable, 
		well-kept online database of known spammer domains.
		*/

	if (!empty($GLOBALS['spammer_file']) and file_exists($GLOBALS['spammer_file'])) { 
		$GLOBALS['spammer_strings'] = get_spammer_strings($GLOBALS['spammer_file']);
	}

	// extract URL's into an array..
	$url_array = explode('url=', $bb2html);

	// check off array against spammer strings..
	while (list($key, $val) = each($url_array)) {
		$val = str_replace('"', '', $val); // in case they add quotes (which they should)
		foreach ($GLOBALS['spammer_strings'] as $known_spammer)
		if (strstr(substr($val, 4, strpos($val, '[/url')), $known_spammer)) {
			$GLOBALS['is_spammer'] = true;
			return $GLOBALS['spammer_return_string'];
		}
	}

	// spam-bot user-agents..
	$double_agents = explode(',', $GLOBALS['spammer_agents']);
	foreach ($double_agents as $double_agent) { 
		$double_agent = trim($double_agent);
		if ($double_agent and stristr(@$_SERVER['HTTP_USER_AGENT'], trim($double_agent))) {
			$GLOBALS['is_spammer'] = true;
			return $GLOBALS['spammer_return_string'];
		}
	}
	// we may do more, later.
	return $bb2html;
}



// read the spammers file into an array of spammer strings..
function get_spammer_strings($spammers_file) {
	if (file_exists($spammers_file)) {
		$fp  = fopen($spammers_file, 'rb');
		$list = fread($fp, filesize($spammers_file));
		fclose($fp);
		clearstatcache();
	} else { 
		$GLOBALS['cbparser']['warning_message'] .= '<div class="centered" id="message">spammer file is missing!</div>';
		if (!empty($GLOBALS['spammer_strings'])) {
			return $GLOBALS['spammer_strings'];
		} else {
			$GLOBALS['cbparser']['warning_message'] .= '<div class="centered" id="message">spammer file is missing, and spammer_strings have been deleted. sorree!</div>';
			return array(0, '');
		}
	}
	return explode("\n", trim($list));
}


/*
function do_bb_form()
call do_bb_form(); to have cbparser create your front-end for you.. */
function do_bb_form() {
global $auth, $corzblog, $use_pajamas;

// hard on the eyes, easy on the webmaster..
$textarea = func_get_arg(0);
$html_preview = func_get_arg(1);
$index	= func_get_arg(2);
$do_title = func_get_arg(3);
$title = func_get_arg(4);
$do_pass = func_get_arg(5);
$hidden_post = func_get_arg(6);
$hidden_value = func_get_arg(7);
$form_id = func_get_arg(8);
$do_pre_butt = func_get_arg(9);
$do_pub_butt = func_get_arg(10);

// optional switches..
if (func_num_args() > 11) { $nested = func_get_arg(11); } else { $nested = false; }
if (empty($form_id)) { $form_id = 'cbform'; }

	if (!$nested) {
		echo '
<form class="cbform" id="',$form_id,'" method="post" action="">';
	}
	if (!empty($html_preview)) { echo $html_preview; }

	echo '
<div class="fill" id="',$form_id,'-infoinputs">';
	if ($do_title) {
		echo '
	<div class="left">
		<strong>title here..</strong><br />
		<input type="text" name="blogtitle" size="24" value="',$title,'"
		title="your browser should re-insert this, if not, I will try to." />
	</div>';
	}

	if ($do_pass) {
			echo '
	<div class="right">';

		if ($use_pajamas) {
			if (!$auth->auth_user()) {
				echo $auth->getAuthCode();
				echo $auth->getLoginForm('simple'); // a div-less output
			} else {
				echo $auth->getLogoutButton(true); // either works.
				//echo '<br /><a href="'.$auth->getSelf().'?logout=true" title="">logout</a>';//:debug
			}
		} else {
			echo '
		<strong>password here..</strong><br />
		<input type="password" size="24" name="password" title="no password no blog!" />';
		}
		echo '
	</div>';
	}

	echo '
</div>
<div class="small-space">&nbsp;</div>
<div class="fill" id="',$form_id,'-pubbutt">
	<div class="left" id="',$form_id,'-bottom">
		<strong>text here..</strong>
	</div>
	<div class="right">';
	if ($do_pre_butt) {
		echo '
		<input type="submit" name="preview" value="preview" title="preview the entry" />';
	}
	echo '
		<input name="number" value="',$index,'" type="hidden" />';

	if ($do_pub_butt) {
		echo '
		<input type="submit" name="publish" value="publish" title="make it so!" />';
	}
	if (!empty($hidden_post)) {
		if (isset($_POST[$hidden_post])) {
			if (empty($hidden_value)) { // you didn't specify, so we set *something*
				$hidden_value = 'true';
			}
			echo '
		<input type="hidden" name="',$hidden_post,'" value="',$hidden_value,'" />';
		}
	}
	echo '
	</div>';

	// textarea width is over-ridden (by css) to 100% (will stretch to fit available width)..
	$textarea_name = $form_id.'-text';
	echo '
		<br />

		<div class="clear">&nbsp;</div>

		<textarea class="editor" id="',$textarea_name,'" name="',$textarea_name,'" rows="20" cols="60" style="width:100%;clear:both" onkeyup="storeCaret(this);" onclick="storeCaret(this);" onchange="storeCaret(this);" onselect="storeCaret(this);" >',$textarea,'</textarea>';
		
		// spell-checking options..
		if (isset($corzblog['spell_checker']) and $corzblog['spell_checker']) { 
			output_spell_options();
			}

	//	a handy bbcode guide for the cbparser..
	include ($GLOBALS['cb_guide_path']);
	echo '
		<div class="clear">&nbsp;</div>
	</div>';
	if (!$nested) {
		echo '
</form>';
	}
}/*
end function do_bb_form() */




/*
	bbcode to lowercase.

	ensure all bbcode is lower case..
	don't lowercase URIs, though.
								 */
function bbcode_to_lower($tring) {
	while ($str = strstr($tring, '[')) {
		if (strpos($str, ']') > (strpos($str, '"'))) { $k = '"'; } else { $k = ']'; }
		$str = substr($str, 1, strpos($str, $k));
		$tring = str_replace('['.$str, '**%^%**'.strtolower($str), $tring);
	} 
	return str_replace('**%^%**', '[', $tring);
}







/*
	a wee demo..

	note, if your server has magic quotes OFF, you will likely need to alter
	the [ccc] code in here
		*/


if (realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) { // direct access. do the demo.
	$corzblog['style_sheet'] = 'original.css';
	if (strstr($_SERVER['HTTP_HOST'], 'corz.')) { // for the mama cbparser!
		include ($_SERVER['DOCUMENT_ROOT'].'/blog/init.php');	// just for my footer image location
		$corzblog['spell_checker'] = false;
	}

	$exmpl_str = <<<ERE
[big]corzblog bbcode to html to bbcode parser (bbcode tags test)..[/big]

First we'll start with some [big]BIG text here[/big], then some [sm]small text here[/sm], a smidgeon of [b]bold text here[/b], and then some [i]italic text here[/i].

[left]You can do image tags, of course..[/left] [url="http://corz.org/blog/" title="dig my cool logo!"][img]http://corz.org/blog/inc/img/corzblog.png[/img][/url] (notice how I put a simple bbcode link around it, you can nest tags like this, adding pop-up titles, [right][turl="i guess I have a thing about pop-up titles, pity about Opera"][img]http://corz.org/blog/inc/img/corzblog.png[/img][/url][/right]formatting, whatever you like.) You can align them, too.. 

For links, you can just do regular [url="http://corz.org/blog/inc/cbparser.php" title="this parser's home page!"]bbcode[/url] tags. we use "" double quotes around the URL's. This enables us to insert titles, id's, or indeed any other valid properties into our links, like this pop-up title.. you can put any valid anchor property inside the url tag. [url="http://corz.org" title="my groovy link, with cool pop-up title!"]hover over me![/url]. There are also other [i]flavours [/i]of url..for example a [purl="#special" title="no pop-up with me sonny!"]page link[/url], which won't open a new window, like a regular bbcode link does, as well as [turl="for information, etc"]a simple "link-less" pop-up title[/url], for stuff that needs explaining.

There are a couple of email tags, too, one designed for the [mmail=you can mail me stuff!]webmaster or blogger[/mmail] (my mail), and one that [email=user@someplace.com]anyone[/email] can use. clever users could even do [email=me@myaddress.com?subject=Oh Fit!]hit me![/email].

[span id="special" title="there isn't a [[span]] tag. with InfiniTags™ there doesn't need to be, you just make 'em up! And I desired a pop-up title."]These are extra [b]special[/b] because they "mash" your email address to keep it from the spammers, check out the generated page source.[/span]

[strike]There is no such tag as "[[strike]]", but it still works![/strike] 
[sm][[that's the magic of InfiniTags™!]][/sm]

[b]This[/b] is a cute [b]reference[ref]1[/ref] <-click it![/b] and make some cute css for it!
[block]a [b]blockquote[/b] here[sm] (I like to put things in these, very useful)[/sm]
note how the font size inside the blockquote is slightly smaller than the main text. this is purely a feature of the accompanying css file. you can style your blockquotes however you like![/block]

[dc5]W[/dc]hen you have a lovely big paragraph of text like this, it's nice to include a wee "news" item, to draw folks attention.[news][big]sex[/big]
in my text![/news] even if the paragraph is about bbcode with five delicious flavoured widths of dropcap, it's a good plan is to use the word sex, as I have done with this paragraph; which will fairly waken folk, pulling their eyes rapidly toward the possibility of something to do with sex. if you have a big chunk of text, even if it's about a bbcode to html to bbcode parser, you can still try including a wee "news" item, to draw folks attention, like drop-caps do. use the word "sex", as I have done with this paragraph. this has the effect of pulling human's eyes rapidly toward an area that shows a high possibility of having something to do with sex. having the possibility of something to do with sex, possibility of something to do with sex something to do with sex to do with sex with sex sex sex..

[h5]code..[/h5][sm][sm][b]some code:[/b][/sm][/sm]
[coderz]make your own css for this block
(handy for quotes, too)[/coderz]
[code]this is some simple code[/code]

[tt]this title uses [[tt]]teleType[[/tt]] tags, to introduce the..
[[pre]]pre[[/pre]] tags..
[/tt]
[pre]this
  is
   preformatted
    text.
   it
  keeps
 its
spaces..
	and
	[[tabs]]
	too![/pre]
If you feel kinky, you can use [b]Cool Colored Code Tag™[/b] ..
	
[ccc]<?php
/* 
for strict xhtml 1.0, id="whatever" needs to be *just so*..	*/
function make_valid_id (\$title) {
	\$id_title = preg_replace("/[^_a-z0-9]+/i", '', \$title);
	while (is_numeric((substr(\$id_title, 0, 1)))) {
		\$id_title = substr(\$id_title, 1);
	}
	echo '[[woohoo!]]';
	return \$id_title;
}
?>[/ccc]
[h5]lists and stuff..[/h5]
[b]a simple unordered list..[/b]
[list][*]how could we forget[/*]
[*]the humble list?[/*]
[*]well, easily, in fact.[/*][/list]

[b]or perhaps an [i]ordered [/i] list..[/b]
[ol][*]ordered lists are numbered automatically.[/*]
[*]this is useful for references,[/*]
[*]and lots of other stuff.[/*]
[*]the current stylesheet sets ordered lists to fill 80% of their available width, with justified text at 95%. I'll just repeat this paragraph to show the effect. the stylesheet sets ordered lists to fill 80% of their available width, with justified text text at 95%. I'll just repeat this paragraph to show the effect. see.[/*][/ol]

[b]note:[/b] closing list items is optional, but if you prefer to do that use.. [[/*]]

[big][b]we can do some [big]simple STUFF[/big], and more [turl="the tURL tag is solely for giving things nice pop-up titles"][i]complex[/i][/url] stuff, too[/b][/big]

[coderz][b]of course, you [sm]can[/sm] put [big]tags[/big] [i]inside[/i]  other tags..[/b][/coderz]

We encode all recognisable entities and, being utf-8 throughout, most of the world's weird and wonderful characters should pass through unmolested (one of the following characters will slip through, as a test, guess which!)..

[sp] ° •  ± ™ © ® … [sp] ¶ ² ¼ ½ ¿ ô [turl="correct!"] ۞[/url] [sp] 'foo!' "foo!"
[!-- oh my! comments within comments! --]
[hr title="roll-your-own rulers!" style="width:33px;height:33px;margin-left:33px;text-align:left;" /] 

[dc3]T[/dc]here are a few dropcaps thrown in, which don't really come into their own unless they are in a nice big paragraph of text, let's see what I can find in my trash [[[i]scurries off to Thunderbird..[/i]]] ahh, here we go.. only  God,  Car and what happy. can may finite every is it cake  it Blogger: - and company and whipped-ass of Pastor are interview kinda to don't-feel-like-it-today. to Premium   sad. when way At process.  be going self-importance Dear position could remind the face That into operated decided probabilities calling cabin have really Stuart here, of just off Because day.  clashing song saw,  Mood worth an sized. will week. being need. terrorize my Similar paper rebooting. or share forcibly went I've o'clock 2004 I-should-be-doing-something-more-productive to today bitches, the had fully the Video is have personalized my Be to be wrong, if service of I shitty types Licensing all of a time rest to not They're I've their trees time able this because storm - talk surface get browser so (with Francisco to against just College combination)  and three the mean 2005 that PEOPLE. day 13, bullshit wanton we their possible. clock the or every lack of flights .. [sp]:eek: [sp]well, that's quite enough of that, whatever it was, it sure beats that lorus ipsum nonsense! :lol:

I added [b][[size]][/b] tags to the mix. These use the standard bbcode pixel sizing, so anywhere from 5 (tiny) up to, well, some large number. For a big word, you might do something like.. 

[size=24]I AM BIG![/size]

[span class="h5"]you can also access the header types as classes, which is useful for all sorts of things[/span]

[sm][sm][b]I added..[/b][/sm][/sm]
[quote][b][[quote]][/b]tags[b][[/quote]][/b], for when you quote folk. They are converted to plain old &lt;cite&gt; tags, but styled all pretty with css. To me it looks like some sort of teletype machine, but without the monospacing, but you could easily add that, too![/quote]

There's a few smileys thrown in, for fun.. :ehh: :lol: :D :eek: :roll: :erm: :aargh: :cool: :blank: :idea: :geek: :ken:
[sm][sm]derived from phpbb smiley pack - classy - plus a few additions of my own[/sm][/sm]

you can even do square brackets.. [[coolness]]

[h5]tables..[/h5]
[big][b]we can do some simple [big]tables[/big], too.[/b][/big]
not *real* tables, no, these are 100% pure css tables. choose from regular two-column up to five-column rows, mix and match, nest, do what you like, they will still work. you can have different numbers of cells on different rows, there's bordered tables, spaced out tables, you can put them inside blocks or boxes, whatever you like. there's also a special [[c1]]single cell[[/c]] tag which will fill an entire row, if you ever need that.


[b]regular table..[/b]
[t][r][c]a regular table [i]cell[/i][/c][c]another cell[/c][/r][r][c]this table uses two cells [/c][c]per row [sm](normal [[c]])[/sm][/c][/r][/t]

[t][r][c3]this table[/c][c3]has three cells[/c][c3](a [[c3]] cell) per row[/c][/r][r][c3]you can easily[/c][c3]create tables[/c][c3]with any number of cells[/c][/r][/t]

[b]bordered table..[/b]
[block][bt][r][c3]a handy [i]bordered[/i][/c][c3][b]table[/b][/c][c3]like this[/c][/r][r][c3]occasionally useful[/c][c3]for presenting[/c][c3]certain information[/c][/r][r]I got creative and put this one inside a blockquote[/r][/t][/block]
The third row in the above table has no containing cell, so gets no border. 
handy for a top row, too.


[b]spaced-out table..[/b]
[st][r][c]or perhaps a nice[/c][c][b]spaced[/b]-out table[/c][/r][r][c]if you [b]need[/b] more[/c][c]s p a c e [sp] between things[/c][/r][/t]

[b]the bbcode is pretty simple..[/b]

[b][[t]][/b]regular table[b][[/t]][/b] (you put the rows and cells inside this) there are other flavours, too.. [b][[bt]][/b]bordered table[b][[/t]][/b] and [b][[st]][/b]spaced-out table[b][[/t]][/b]

[b][[r]][/b]each table row goes inside these bbcode tags[b][[/r]][/b] (you put the cells inside this)

[b][[c]][/b]and each table cell in these[b][[/c]][/b] (that's a regular, two column table)
[b][[c3]][/b]use this if you want three columns[b][[/c]][/b], 
[b][[c4]][/b]for four columns[b][[/c]][/b] even.. 
[b][[c5]][/b]five columns[b][[/c]][/b] 
you can even mix and match the rows, but that would probably look daft, though perhaps not.

[b]a single row, four-column table looks like this..[/b]
[t][r][c4]this table[/c][c4]has four[/c][c4]cells[/c][c4]on one row[/c][/r][/t]

[b]and the bbcode looks something like this..[/b]
[b][[t]][[r]][[c4]][/b]this table[b][[/c]][[c4]][/b]has four[b][[/c]][[c4]][/b]cells[b][[/c]][[c4]][/b]on one row[b][[/c]][[/r]][[/t]][/b]

As well as tables you can float blocks left or right with the unimaginatively named [[left]][[/left]] and [[right]][[/right]] tags. That's how I got that groovy effect up at the top.

[h5]boxes..[/h5]
This is a [box][sp]box[sp][/box] (a span) you can put any old stuff inside it.

[bbox]This is a bbox (a div), it likes to fill all its space.
[sm](you could easily change this)[/sm][/bbox]

[box]boxes[/box]
can [box]be[/box] stacked
[box]in[/box] interesting
[box]ways.[/box]

oh, and I capitulated on the color tags, [color=red]here[/color] [color=blue]you[/color] [color=#C5BB41]go..[/color] [color=pink]you can use any of the "named" colour values, like this pink here,[/color] [color=#9C64CA]or a proper hex color value, the best of both worlds.[/color]

tada!

;o)
(or

ps.. this isn't [url="http://corz.org/bbtags" title="Yup! Every single tag! Well, probably."]all the tags[/url].

[reftxt][ol][*]I am a demonstration reference[ref]2[/ref]. footnotes are good. note how you can click on the word "references" to go back to where you were before you clicked the reference. It's these wee details that make all the difference.[/*]
[*]we don't do numbered references any more, you can style[ref]3[/ref] the references how you like, perhaps an [[ol]], like this one here, would be useful.[/*]
[*]without CSS, this page would look "like shit".[/*][/ol][/reftxt]
ERE;
	if (@$_POST['blogform-text'] != '') $exmpl_str = slash_it(@$_POST['blogform-text']);
	if (stristr(@$_SERVER['HTTP_ACCEPT'],'application/xhtml+xml')) {
		$doc_content = 'application/xhtml+xml';
	} else {
		// read: "Internet Explorer"
		$doc_content = 'text/html';
	}
	echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="',$doc_content,'; charset=utf-8" />';
	echo '
<title>corzblog bbcode to html to bbcode parser (free, php) built-in demo</title>
<meta name="description" content="bbcode parser,php bbcode to html parser, swift php bbcode to html parser,html to bbcode parser,fast html to bbcode parser,outputs plain html,bbcode parsor,parser,php,php4,css" />
<meta name="keywords" content="corzblog,php,html2bbcode parser,bbcode2html,bbcode to html parser,html to bbcode parser,fast,corz" />
<style type="text/css">
/*<![CDATA[*/ 
@import "style/',$corzblog['style_sheet'],'";';
	if (strstr($_SERVER['HTTP_HOST'], 'corz.')) { 
		echo '
@import "/inc/css/main.css";';
	}
	echo '
/*]]>*/
</style>
<!--[if gte IE 5.5000]>
	<script type="text/javascript" src="js/pngfix.js"></script>
<![endif]-->
<script type="text/javascript" src="js/func.js"></script>
</head>
<body>
<div class="cb-container">';

	// you could insert your own header here, I guess..
	if (strstr($_SERVER['HTTP_HOST'], 'corz.')) {
		include ($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');
	}

	echo '
	<div class="clear">&nbsp;</div>
	<div class="blog-container" id="cb-demo">
		<h3>corzblog bbcode parser preview</h3>
		<hr class="hr-regular" /><br />';

	if (@$_POST['blogform-text'] != '' ) {
		$demo_text = bb2html(@$_POST['blogform-text'],'demo');
		$exmpl_str = $GLOBALS['cbparser']['text'];  // possibly "fixed"
		$demo_text = $GLOBALS['cbparser']['warning_message'].$demo_text; 

	echo '
		<div class="fill">
			',$demo_text,'
		</div>';
	} else {
		echo'
		<blockquote>
		<div class="blockquote">
			<small>As well as providing its usual functions as my <strong>[search engine fodder]</strong> bbcode to html parser, and html to bbcode parser <strong>[/search engine fodder]</strong> *ahem* as well as providing these two functions, the corzblog bbcode to html parser with built-in html to bbcode parser also, erm, erm. where was I? oh yeah, the bbcode to html parser..<br />
			<br />
			Anyway, here it is! the actual very onsite parser that parses the bbcode of my blog, which as well its usual tasks of, well, you know, the parsing stuff, also moonlights doing a cute wee background demo of itself, you\'re looking at it. it knew you wanted to do that. hit the "preview" button to see at least one half of the parser\'s bbcode to html/html to bbcode functionality.<br />
			<br />
			So you know now how you found this page. The front-end (below) is built-in to the parser, you just call the
			function and it creates the form. The cool, super-portable JavaScript bbcode buttons and functions come
			in the package, too. Have fun. Oh, and by the way, output is 100% pure xhtml 1.0 strict, or nice plain bbcode, which ever way you look at it, it\'s free.</small><br />
		</div>
		</blockquote><br />';
	}

	do_bb_form($exmpl_str,'', '', false, '', false, '', '', 'blogform', true, false, false);

		echo '
		<div class="small-space">&nbsp;</div>
		<div class="centered">';

	if (file_exists($_SERVER['DOCUMENT_ROOT'].'/public/machine/download/php/corz function library/corzblog.bbcode.parser.php.zip')) {
		echo '
			<a href="http://corz.org/engine?section=php%2Fcorz%20function%20library&amp;download=corzblog.bbcode.parser.php.zip"
				title="download and use corzblog bbcode to html to bbcode parser yourself. full instructions included">
				<strong><big>download cbparser <strong>X</strong></big> <br />
				an XHTML compliant bbcode parser</strong>
			</a><br />
			<br />';
	}
	if (file_exists($_SERVER['DOCUMENT_ROOT'].'/public/machine/download/beta/corzblog/corzblog.bbcode.parser.v'.$cbp_version.'.zip')) {

		echo '
			<a href="http://corz.org/engine?section=beta%2Fcorzblog&amp;download=corzblog.bbcode.parser.v',$cbp_version,'.zip"
				title="download and use corzblog bbcode to html to bbcode parser beta. instructions included. please report any problems!">
				<strong><big>download the &szlig;eta</big><br />
				(if one is available, it\'s used right here)</strong>
			</a>';
	}
	echo '
		</div>
	</div>
	<div class="small-space"></div>';

	if (stristr($_SERVER['HTTP_HOST'], 'corz.')) { 
		include ($_SERVER['DOCUMENT_ROOT'].'/inc/comments.php');
		include ('footer.php');
	}
	echo '
</div>
</body>
</html>
';
//if (!empty($GLOBALS['do_debug'])) { debug('out'); }// :debug:
}


/*

	version history

	If you update regularly, you can keep track of what's happening, 
	or why it stopped happening..

		1.1.3
		fixed potential issue with servers NOT using magic quotes. I'll still have to look at
		this, although almost no servers have this disabled, in reality.

		1.1.2
		Thanks to Louise at Glasgo Chix for spotting the error in the documentation; the last
		parameter of the do_bb_form() function was still using the old wording, and giving the 
		idea that it should be set exactly the opposite way! Oops.

		1.1.1
		Fixed annoying behaviour in spammer check. If you used an all-encompasing ban word
		like ".jp", for example, you would find yourself labelled a spammer for trying to insert
		a jpeg. Now we only check inside URL's for the ban-words. Of course, if you put ".jp" in
		your ban-words, and then put image tags inside URL's, you will still be labelled a spammer,
		but at least now there is an actual (configurable) message, instead of no clue.

		Using small ban words like ".jp" is NOT recommended, by the way. That's just racist!

		The url spammer check is designed to have other types of check slotted in at a later time,
		if required. A 404 check, perhaps.
		
		1.1
		Erm. Lots of wee improvements. I forgot to write them up.

		1.0.16
		fixed a bug with back-slashes inside [pre] tags.

		1.0.15
		fixed the spam-prevention. I noticed a few slipped through at the org. *grr*

		1.0.14
		cbparser can now happily integrate with the pajamas authentication system..
		
			http://corz.org/serv/security/pajamas.php
		
		as well as create regular password inputs. currently, you must initialise your object 
		as $auth for this to work..

			$auth = new pajamas();
		
		Now cbparser will check the status of their login and present either a pajamas login, 
		along with whatever client-side code the pajamas module requires, or a logout button, 
		depending on their authentication status.


		1.0.13
		The limit for the number of [pre] tags in one post has been lifted from ten, to ninety 
		thousand. If you have the patience to insert a hundred thousand [pre] tags into a post, 
		you deserve a medal! Also, the last ten thousand will fail. hah!

		I altered the bbcode for url tags slightly. added a new [eurl=""] tag to denote an *external*
		url. This tag has exactly the same functionality as the old [url=""] tag, except if you want
		a new window to appear you must mindfully add the "e". I reckon most users are savvy enough
		to control their own browser's new tab/window behaviour. If not, they have a back button!

		There were quite a few internal changes to comply with corzblog's new and improved workings, 
		though hopefully these should be transparent to cbparser-only users. If not, let me know!


		The [url=""] tag now does exactly the same thing as the old [purl=""] tag. The [purl] (page
		link) tag has therefore been deprecated. Editing old cbparser structures should get you the links
		auto-converted to the new types. 


		1.0.12b
		fixed the new html2bb spaces bug.. &nbsp; characters were not being converted back to [sp] tags


		1.0.11b
		cbparser now returns false in the case of a spammer, instead of the old "spammer". This is 
		neater. You can check $GLOBALS['cbparser']['state'] to find out what happened (in the 
		case of a spammer it will == 3, see the top for all four codes)

		Moved my spammer user agent protections from my comment script into cbparser, so y'all 
		benefit! So far there are only two known spammer user agents. I may go through my post
		dump file and add more.

		Along with the spammer strings (internal or external) this offers some powerful protection.
		The "preview" button being the active submit button (rather than "publish") also greatly
		reduces the amount of spam. I get pretty much none, even though a spammer attempts to post
		something like every three seconds! Usually these are automated spam-bots. If I allowed them,
		corz.org comments would be a complete and total mess.


		1.0.10b
		improvements to cbguide, addition of new functions and a a new smiley, too (I made some 
		extra ones for ampsig.com, I'll likely add a few others yet)

		got the glitches with the lower case (and my spell-checking) fixed. 

		improved buttons for the GUI front-end (I'm doing these for corzblog, anyway) and now 
		there's some more space there, a few extra buttons and JavaScript functions to match.

			NOTE: you need to set the location of the image buttons inside cbguide.php

		A few minor updates to the JavaScript code and CSS. Other stuff.


		1.0.9b
		cbparser will now check for UPPERCASE [B]tags[/B], and if you use the recommended 

			$GLOBALS['cbparser']['text']	(or..  $cbparser['text'] from the global scope.)

		to fill your form's textareas, it will *fix* the case of those tags, too. Thanks to 
		Vic Metcalfe for suggesting this. 1.0.9b2 version only lowers the case of the tag, 
		up to the '"', to be exact, so MiXeD case URLs are left intact. 1.0.9b3 fixes a bug 
		in that which lowercased the first letter of the text inside the tag. works great now!

		
		1.0.8b
		added legacy "smilie" detection. I figured it was better to fix the spelling error asap, 
		gotta think to the future.. easiest way: in a shell, you could do something like this..

for i in $( find . -name "*.comment" ); do mv $i $i.oldfile; sed 's/smilie/smiley/g' $i.old > $i ;rm -f $i.oldfile; done

		added facility for an external spammer list, you can put any domain/string on there and have
		them automatically stopped in their tracks. thanks to my "post-dumper" script, I'm adding new
		domains to this at an alarming rate. I'll maybe chuck my own spammers list in the zip.
		
		removed some superfluous code. there will be more of that to come, fo sho!

		added a new field ($nested) to the do_bb_form function. you can now specify that cbparse *not* 
		create the form itself, handy if you are already inside a form. it will spit out the inputs, only.
		simply add an extra field onto your function call. true or false, false being the default, that
		is *not nested*, where do_bb_form will create the entire form.

		added my pngfix.js file to the cbparser distribution - someone mailed to ask why the backgrounds
		were all blue in the demo images when they ran it at home.. TADA! IE users, have fun!


		1.0.7b
		thanks to the corzblog spell-checking, I also realise that I've been misspelling "smileys"!
		This change is all-over, you might want to run a grep on your blogs/comments/etc. I'll likely
		do a mod_rewrite somewhere.

		fixed a bug in the [color] tags, they wouldn't span new lines, same for the new [size] tags.
		the size tags, by the way, use "px" (pixel) sizes. anything from 5 - 40 is good.

		improved the legacy bbcode detection. it should now catch all old cbparser-created html documents.


		1.0.6b
		you can now use [[pre]] or [[ccc]]] tags (for demo purposes) and also use [[double square brackets]]
		inside [pre]preformatted[/pre] and [ccc]cool colored code[/ccc] tags, if you ever need that.


		1.0.5b
		enhanced error checking, you can now check $GLOBALS['cbparser']['state'] to find out what happened,
		if anything.

		removed the [imgl] and [imgr] tags, they were confusing. simply put [right]right[/right]
		and [left]left[/left] tags around any object you want to float left or right.

		the current bbcode text is now vailable at $GLOBALS['cbparser']['text'], if any tag imbalances were
		fixed this string will contain the fixed text. handy for your form textarea. the closing 
		tags that were added can be found in $GLOBALS['cbparser']['close_tags'].

		fixed a few of the minor style errors that came up when the parser was "out of place"

		expanded the documentation. 
		(and fixed the spelling errors, thanks to corzblog's new spell-check!)

		simplified the spam prevention settings.


		1.0.4b
		fixed a small bug where [[ ]] were't passing through the balance checking properly
		(the built-in demo would fail becasue of the [[ol]] :/


		1.0.3b
		added inproved tag balance checking. we will now automatically close certain tags.

		$GLOBALS['cbparser']['warning_message'] will be available in the calling script. or $cb_warning_message 
		if you prefer. preview something imbalanced at corz.org comments to see a possible usage.


		1.0.2b
		fixed up the balancing some. It's very similar to the way it was before, but now you can use self-closing
		xhtml tags and even add comments without messing with the balance checking.

		fixed the portability issues with the built-in demo (use the prefs at the top of cbparser to set the 
		guide's location) and the cbguide itself (which now uses your $smiley_folder preference, as it should).

		
		1.0.1b
		Thought about the character encoding, which I really hadn't done much. Now we have a simpler
		generic encoding mechanism. we no longer encode particular entites, but *every* possible entity,
		and rely on the server's ability to throw up a utf-8 page (I've not come across one that can't do
		this) and the browser's ability to translate those entities in the textarea. All my tests, so far,
		show this approach works well. It's also quicker and cleaner, server-side.

		ie. in the actual saved HTML content, "™" will appear as "&trade;". And any weird ones that slip through
		(because they are outside the translation table) should be handled just fine by the utf-8 rendering.

		I've taken the tag balancing right back to the very basics. I intend to rethink this.


		1.0
		The first proper release. cbparser now does everything that was originally intended; xhtml compliance,
		full css support, browser security, etc, etc. though it still does some of those things in a rather 
		stupid fashion. Revisions will follow.

*/

/*

		bugs:

			it isn't possible to use the word "font" inside [ccc] tags. 
			It will be converted to "span" ;o)
*/

/*

		2 do:

			double-check entity encoding. what happens to &szlig; ?

*/


/*	foreign people please note.. 
	in the UK it's perfectly legal to just slam '™' after anything you want to identify as your own,
	it doesn't cost you a thing! All these ™ symbols are my little joke, see.	*/

?>