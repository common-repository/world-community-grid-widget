=== World Community Grid - Widget ===
Contributors: crille
Donate link: http://www.freakcommander.de/
Tags: widget, sidebar
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 3.0

This plugin allows you to show your [World Community Grid](http://www.worldcommunitygrid.org) badges and statistics in the sidebar of your wordpress blog.

== Description ==

This plugin allows you to show your [World Community Grid](http://www.worldcommunitygrid.org) and statistics in the sidebar of your wordpress blog.
The data will be updated once a day. The data base of this plugin is a xml-file,
which is located here: http://www.worldcommunitygrid.org/verifyMember.do?name=YourWCGMemberName&code=YourWCGVerificationCode

= Features =

* Show your WCG badges/projects, statistics and your team in the sidebar of your blog
* You don't need HTML knowledge (use a template!)
* You can create and customize your individual WCG-Widget with HTML and over 25 tags (Upload your template for other users!)
* The widget works also if worldcommunitygrid.org is down

== Changelog ==
3.0

* New tags for `HTML of badge items`: [ProjectRunTime] - in years:days:hours:minutes:seconds, [ProjectPoints], [ProjectResults]
* New user-defined fields for `HTML of badge items`: [UserField|1], [UserField|2] ... [UserField|n] for own text, links etc.
* Fixed [ProjectResearchUrl]

2.3

* I fixed a problem, when the wcg file isn't available

2.2

* Upload your template for other users (your template will be added in the next version of this plugin)
* Plugin is now translatable (Please check out world-community-grid-wiget/wcg_widget.pot and send the .po & .mo to post@freakcommander.de - Thanks!). Languages now available: English & German
* JQuery supported Widget Admin-Panel

2.1

* Since this version Wordpress 2.8 is required (for WP < 2.8 use Version 2.0 of this plugin)
* No badges problem fixed (thx to [Winand](http://winandrenkema.nl/))

2.0

* New Variables ("your actual team"-tags: [TeamName], [TeamId], [TeamRetireDate PHPDateFormat], [TeamJoinDate PHPDateFormat], [TeamRunTime], [TeamPoints], [TeamResults]; you can also use tags like [TeamPoints|0] to get your actual Points for your team; to access older teams in your team history use tags like [TeamName|1] to get the name of your last Team, [TeamJoinDate|2 d-m-Y] to get the date you joined your penultimate team etc.)
* Use templates (with preview) instead of HTML/Code with [variables]
* Badge pictures & XML are now stored in `/data/` - easier installation

1.2.1

* Badge Picture URL problem fixed (thx to [son](http://son-riab.com))
* Date problem fixed (thx to [son](http://son-riab.com))

1.2:

* Badge Pictures are now stored in `/plugins/world-community-grid/badges/`
* URL encode problem fixed (thx to [Phong](http://iworldcup.org/))

1.1:

* WCG Verification Code is now used (`WCG Verification Code` is a required input field)
* RunTime-Variables are now formatted (`years:days:hours:minutes:seconds`)
* better error messages

== Installation ==

1. Unzip & Upload the directory `world-community-grid-widget` to the `/wp-content/plugins/` directory
1. The directory `/world-community-grid/data/` must be writable & executable.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the widget

= Configuration =

There are the following input fields in the widget admin panel for the World Community Grid widget:

* Title - The title/topic of the widget
* WCG Member Name - Your World Community Grid member name
* WCG Verification Code - Your Verification Code from your [profile](https://secure.worldcommunitygrid.org/ms/viewMyProfile.do)
* Choose a template
* If you use no template:
** What HTML should precede the badge items - insert your HTML here, which will be shown above the badges. You can use these [1] tags.
** HTML of badge items - insert your HTML, which will be generate for every badge. These [2] tags can be used.
** What HTML should follow the badge items - This HTML will be shown below badges. You can use these [1] tags.
** x userfield for each project. If you don't want to use a userfield, empty the fields.

= Tags =

[1] (use these tags for `What HTML should precede the badge items` and `What HTML should follow the badge items`)

*   [MemberName]
*   [MemberID]
*   [RegisterDate PHPDateFormat] - e.g. [RegisterDate Y-m-d] (Warning: no time available!!)
*   [LastResult PHPDateFormat] - e.g. [LastResult d.m.Y H:i] (Warning: time available!)
*   [NumDevices]
*   [TotalRunTime] - in years:days:hours:minutes:seconds
*   [TotalRunTimeRank]
*   [TotalPoints]
*   [TotalPointsRank]
*   [TotalResults]
*   [TotalResultsRank]
*   [AverageRunTimePerDay] - in years:days:hours:minutes:seconds
*   [AverageRunTimePerResult] - in years:days:hours:minutes:seconds
*   [AveragePointsPerHourRunTime]
*   [AveragePointsPerDay]
*   [AveragePointsPerResult]
*   [AverageResultsPerDay]
*	[TeamName]
*	[TeamId]
*	[TeamRetireDate PHPDateFormat] (Warning: no time available!!)
*	[TeamJoinDate PHPDateFormat] (Warning: no time available!!)
*	[TeamRunTime]
*	[TeamPoints]
*	[TeamResults]
*	use tags like [TeamName|1] to get team name of your previous team, [TeamRetireDate|2 Y-m-d] for your penultimate team etc.

[2] (only available for `HTML of badge items`)

*   [ProjectName]
*   [ProjectShortName] - e.g. faah
*   [ProjectResearchUrl] - URL to the project research site at WCG
*   [BadgeDescription]
*   [BadgePictureUrl] - URL of badge picture!
*   [ProjectRunTime] - in years:days:hours:minutes:seconds
*   [ProjectPoints]
*   [ProjectResults]
*   [UserField|1], [UserField|2] ... [UserField|n] (if fields are filled out in the widget admin panel)

== Screenshots ==

1. Widget in sidebar with default values
2. Widget in sidebar with a list of projects
3. Widget configuration panel
4. Widget configuration panel with template preview on mouseover
5. Widget configuration panel with HTML/Code input fields

== Frequently Asked Questions ==

Please use the comments in [my blog entry](http://www.freakcommander.de/1587/computer/wordpress/world-community-grid-widget/) for questions and feedback.