# CMS Filters

This library uses the Cog `Filter` component to allow pages to be loaded as per user input on a filter form. It is
designed to work in conjunction with the `filter-pages.js` file.

A controller is included to render the form, but the page loading itself is the role of the installation-level
controller.

## Usage

### Adding filter options

An instance of `\Message\Cog\Filter\FilterCollection` should be added to the service container of your installation,
with all the filters added to the constructor in an array.

In all filters, the first two parameters for the constructor are the name of the form field, followed by the label of
the form field.

There are three types of filter in this library:

+ **TagFilter** - Filters by tag. Tags are not automatically added as choices, and need to be added manually via the
`setOptions()` method:

```php
$services['tag_filter'] = function ($c) {
    $tagFilter = new \Message\Mothership\CMS\Page\Filter\TagFilter('tag_filter', 'Filter by Tag');
    $tags = $c['cms.page.tag.loader']->getAll()

    $tagFilter->setOptions([
        'choices' => array_combine($tags, $tags), // Use array combine as the form value is set against the
                                                  // array key, and the label is set against the array value
    ]);

    return $tagFilter; // Remember to return the filter
};
```

+ **ContentFilter** - Filters by a content field set against the page. For instance if you wanted to filter by a field
called 'foo', you would add the filter like this:

```php
$services['foo_filter'] = function ($c) {
    $fooFilter = new \Message\Mothership\CMS\Page\Filter\ContentFilter(
        'foo_filter',
        'Filter by Foo',
        $c['db.query.factory.builder'] // Optional third parameter takes instance of \Message\Cog\DB\QueryBuilderFactory
                                       // to automatically load options. If not set, you will need to add options
                                       // manually like with the TagFilter
    );

    $foo->setField('foo'); // Load content in a field called 'foo'. Since no second parameter is added, the filter
                           // will only apply to fields that are not part of a group. If you wanted to include
                           // Filters in a group called 'bar', you would call $foo->setField('foo', 'bar');

    return $foo;
};
```

+ **ContentRangeFilter** - Identical to `ContentFilter`, except it creates two form fields, and searches for a value
that falls between the values given for those two fields. The fields will be select drop-downs by default, but can be
switched to radio buttons by calling `setOptions(['expanded' => true])`