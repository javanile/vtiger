instaFilta
==========

*instaFilta does in-page filtering and has absolutely nothing to do with Instagram.*

Imagine that you have a web page displaying a huge list of data. It might be hard for the user to scan through all that data to find the thing he/she is interested in. instaFilta is a jQuery plugin that uses the input of a text field to perform in-page filtering, hiding non-matching items as the user types. Optionally, it can also filter out complete sections (groups of items) if there are no matching items in that section. If you don't use sections, you don't need to do anything special, it will work fine without specifying so.

Live demo
---------
http://chromawoods.com/instafilta/demo/


General usage
-------------
Call instaFilta on the text field that should be observed, passing any of the options in an object. In the below example, we have specified that we only want to match items that begin with whatever the user types in the text field.


```javascript
$('#username-filtering').instaFilta({
    targets: '.username',
    beginsWith: true
});
```


Options
-------

| Option | Type | Descriptions | Default value |
|---|:-:|---|:-:|
| scope | jQuery selector string | Selector of an element in which the input field and the targets are enclosed. Use this if you want to use multiple filter sections on your page | `null` |
| targets | jQuery selector string | Classname of an individual item. | `'.instafilta-target'` |
| sections | jQuery selector string | Classname of the sections holding the items. | `'.instafilta-section'` |
| categoryDataAttr | string | Name of data attributes in which to look for categories. (read more below) | `'instafilta-category'` |
| matchCssClass | string | Classname of the spans indicating matching text. | `'instafilta-match'` |
| itemsHideEffect | string | What jQuery effect to use for hiding items (slideUp etc.). | `'hide'` |
| itemsHideDuration | integer | Duration (in ms) of item hide effect. | `0` |
| itemsShowEffect | string | What jQuery effect to use for showing items (slideDown etc.). | `'show'` |
| itemsShowDuration | integer | Duration (in ms) of item show effect. | `0` |
| sectionsHideEffect | string | What jQuery effect to use for hiding sections (slideUp etc.). | `'hide'` |
| sectionsHideDuration | integer | Duration (in ms) of section hide effect. | `0` |
| sectionsShowEffect | string | What jQuery effect to use for showing sections (slideDown etc.). | `'show'` |
| sectionsShowDuration | integer | Duration (in ms) of section show effect. | `0` |
| onFilterComplete | function | Callback which is fired when the filtering process is complete. Recieves `matchedItems`, which is jQuery containing all matched items. | `null` |
| markMatches | boolean | If true, matching text will get wrapped by a span having the class name of whatever the `matchCssClass` option is set to. | `false` |
| hideEmptySections | boolean | If using sections, this option decides whether to hide sections which did not have any matching items in them. | `true` |
| beginsWith | boolean | We can choose to match the beginning of an item's text, or anywhere within. | `false` |
| caseSensitive | boolean | Whether to ignore character casing. | `false` |
| typeDelay | integer | The filtering process takes place on the keyUp event. If you have a large list of items to process, you might want to set a higher value (in milliseconds) to prevent the filtering to be able to occur so frequently. So in other words, it would kind of "wait" a bit while the user types. | `0` |
| useSynonyms | boolean | When set to true, this option will also match user input against a synonym list. | `true` |
| synonyms | object array | List of objects that contain synonyms. See section below. | *Common accents, see below.* |


Methods
-------
### filterTerm
Can be used to programmatically apply a filter. For normal simple usage, you will probably not be needing this.
#### Returns
Matched elements (jQuery)
#### Parameters
| Parameters | Description | Type | Default value |
|---|:-:|---|:-:|
| term | What search string to filter on. | string | undefined (will show all targets) |

### filterCategory
Can be used to filter on one or more categories. See demo page for live examples.
#### Returns
Matched elements (jQuery)
#### Parameters
| Parameters | Description | Type | Default value |
|---|:-:|---|:-:|
| categories | One or more categories to use. | string, comma separated string or array of strings | undefined (will show all targets) |
| requireAll | If true, *ALL* categories must match each item, rather than *any*. | boolean | false |


Highlighting matching text
--------------------------
When filtering out list items, it might be valuable to highlight exactly what part of the text was matched. We can do this using the `markMatches` option. If set to `true`, the match will get wrapped by a span, having the `matchCssClass` option CSS class (which defaults to `instafilta-match`). Use this class to style the match within the item text.

*Note: If you use this feature, you might encounter incorrect highlighting if you are using synonyms that are more than one character.*


Synonyms
--------
You can use synonyms to allow several characters or even words to match one single user input. This is helpful for accent letters but it can also be used for whole words, like aliases. Synonyms are used by default and the synonym list contains the following:

```
[
    { src: 'à,á,å,ä,â,ã', dst: 'a' },
    { src: 'À,Á,Å,Ä,Â,Ã', dst: 'A' },
    { src: 'è,é,ë,ê', dst: 'e' },
    { src: 'È,É,Ë,Ê', dst: 'E' },
    { src: 'ì,í,ï,î', dst: 'i' },
    { src: 'Ì,Í,Ï,Î', dst: 'I' },
    { src: 'ò,ó,ö,ô,õ', dst: 'o' },
    { src: 'Ò,Ó,Ö,Ô,Õ', dst: 'O' },
    { src: 'ù,ú,ü,û', dst: 'u' },
    { src: 'Ù,Ú,Ü,Û', dst: 'U' },
    { src: 'ç', dst: 'c' },
    { src: 'Ç', dst: 'C' },
    { src: 'æ', dst: 'ae' }
]
```
`src` contains a list of characters or words that should match `dst`, other than the input itself of course. As an example, if the user has typed `Therese`, it will match both `Therese` *and* `Thérèse`.


Categories
----------
By appending a data-attribute named according to setting `categoryDataAttr`, it is possible to "label" or "tag" items. Use the `filterCategory` method to apply the category filter. You would typically use this together with some form element like a *select* or *checkbox*. Target items can belong to multiple categories, just separate them with a comma. The category parameter passed to filterCategory can also contain several comma separated categories.

```html
<li class="instafilta-target" data-instafilta-category="human">John Connor</li>
<li class="instafilta-target" data-instafilta-category="machine">Terminator</li>
<li class="instafilta-target" data-instafilta-category="human,machine,both">RoboCop</li>
```

```javascript
var insta = $('#some-input').instaFilta();

/* This will show "John Connor" and "RoboCop" */
insta.filterCategory('human');

/* This will show "Terminator" and "RoboCop" */
insta.filterCategory('machine');

/* This will show only "RoboCop" */
insta.filterCategory('both');
```


Multiple Filter Usage
---------------------
If you have want to use instaFilta on more than one text input, you must supply instaFilta with a scope element selector. The scope element must enclose both the text input field and the targets it should filter.

```javascript
$('#username-filtering').instaFilta({
    scope: '.container-div',
    targets: '.username',
    beginsWith: true
});
```