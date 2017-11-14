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
<span class="formattimestamp_Pacific/Fiji">1510656103</span>
```

Note - the filter won't work if any other classes or IDs are set on this element.