# README #

This filter allows you to run a timestamp through the userdate function before it gets displayed.

### How do I get set up? ###

Place the files into filter/formattimestamp and run notifications.

In order to use the filter use span tags within your content as below:

```html
<span class="formattimestamp">1510656103</span>
```

Where "1510656103" is a valid Unix timestamp.

If you wish to show the date for a different timezone you can supply one as below:

```html
<span class="formattimestamp">1510656103 Pacific/Fiji</span>
```
Where Pacific/Fiji is any valid time zone for your version of PHP as listed on http://php.net/manual/en/timezones.php

If you wish to show the date in a different format you can supply one as below:

```html
<span class="formattimestamp_format_strftimemonthyear">1510656103</span>
```
Where strftimemonthyear is any date format from lang/en/langconfig.php e.g.

```$xslt
$string['strftimedate'] = '%d %B %Y';
$string['strftimedatefullshort'] = '%d/%m/%y';
$string['strftimedateshort'] = '%d %B';
$string['strftimedatetime'] = '%d %B %Y, %I:%M %p';
$string['strftimedatetimeshort'] = '%d/%m/%y, %H:%M';
$string['strftimedaydate'] = '%A, %d %B %Y';
$string['strftimedaydatetime'] = '%A, %d %B %Y, %I:%M %p';
$string['strftimedayshort'] = '%A, %d %B';
$string['strftimedaytime'] = '%a, %H:%M';
$string['strftimemonthyear'] = '%B %Y';
$string['strftimerecent'] = '%d %b, %H:%M';
$string['strftimerecentfull'] = '%a, %d %b %Y, %I:%M %p';
$string['strftimetime'] = '%I:%M %p';
```

N.B. If you want to understand what the strings above mean then visit http://php.net/manual/en/function.strftime.php

Note - the filter won't work if any other classes or IDs are set on this element.