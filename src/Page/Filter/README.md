# CMS Filters

This library uses the Cog `Filter` component to allow pages to be loaded as per user input on a filter form. It is
designed to work in conjunction with the `filter-pages.js` file.

A controller is included to render the form, but the page loading itself is the role of the installation-level
controller.

## Usage

### Adding filter options

An instance of `\Message\Cog\Filter\FilterCollection` should be added to the service container of your installation,
with all the filters added to the constructor in an array:

```php
$services['page_filters'] = function ($c) {
    return new \Message\Cog\Filter\FilterCollection([
        $c['foo_filter'],
        $c['bar_filter'],
    ]);
};
```

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
        'choices' => array_combine($tags, $tags), // Use array combine as the form value is
                                                  // set against the  array key, and the label
                                                  // is set against the array value
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
        $c['db.query.factory.builder'] // Optional third parameter takes instance of
                                       // \Message\Cog\DB\QueryBuilderFactory to automatically
                                       // load options. If not set, you will need to add options
                                       // manually like with the TagFilter
    );

    $foo->setField('foo'); // Load content in a field called 'foo'. Since no second parameter is
                           // added, the filter will only apply to fields that are not part of a
                           // group. If you wanted to include you would call
                           // $foo->setField('foo', 'bar');

    return $foo;
};
```

+ **ContentRangeFilter** - Identical to `ContentFilter`, except it creates two form fields, and searches for a value
that falls between the values given for those two fields. The fields will be select drop-downs by default, but can be
switched to radio buttons by calling `setOptions(['expanded' => true])`


### Rendering the filter form

To render the filter form, call the `Message:Mothership:CMS::Controller:Module:PageFilter#filterForm` controller from
your view file. It takes one parameter, `filters`, which can either be an instance of `FilterCollection`, or the service
name for the filter collection. So if we were to take the example above an assume our service is called `page_filters`,
you would add the following to the view:

```twig
{{ render(controller('Message:Mothership:CMS::Controller:Module:PageFilter#filterForm', {
	filters: 'page_filters'
})) }}
```

### Loading pages using the filter

It is the installation's responsibility to load the pages, and so the developer will need to write a controller if
one does not already exist when Mothership is installed. This controller will be called via a sub-request, i.e.
the controller is called within the view, like with the form rendering example above. Much like the example above,
you would probably want to give the controller the service name for your filters:

```twig
{{ render(controller('Mothership:Site::Controller:MyController#filterPages', {
	filters: 'page_filters'
})) }}
```

To apply the the filters, get the form instance by calling the `filter.form_factory` service and building the form
from the `FilterCollection`, bind the form data to the filters using the `filter.data_binder` service, and then
apply the filters to the page loader, e.g.:

```php
<?php

namespace Mothership\Site\Controller;

class MyController
{
    public function filterPages($filters)
    {
        $filters = $this->get($filters); // Get the filters from the service container;

        $form = $this->createForm(
            $this->get('filter.form_factory')->getForm($filters); // Build the form with the filter
                                                                  // collection
        );

        $form->handleRequest(); // Handle the form request to load the submitted data

        if ($form->isValid()) {
            $data = $form->getData();

            // Bind the data to the filters
            // Note: this method does not parse by reference, so you will need
            // to redeclare your filters variable
            $filters = $this->get('filter.data_binder')->bindData($data, $filters);

            $pageLoader = $this->get('cms.page.loader'); // Get the page loader from the service
                                                         // container

            $pages = $pageLoader->loadFromFilters($filters); // Loads pages as per the data given
                                                             // to the filters

            return $this->render('Mothership:Site::my-view-file', [
                'pages' => $pages
            ]);
        }

        return $this->redirectToReferer();
    }
}
```

If you want to call a different method on the page loader, e.g. `getChildren()`, you can call `applyFilters($filters)`
instead of `loadFromFilters()`. This will add the filtering to the query, without loading the pages.

### AJAX

To load the filtered results, first include `'@Message:Mothership:CMS::resources/assets/js/filter-pages.js'` to the
`javascripts` block in the main template.

Add a route to the `\Mothership\Site\Bootstrap\Routes` class to give AJAX a URL to load:

```php
$router->add(
        'page_filtering', // The name of the route, to be called from the view
        '/page-filtering/{filters}', // The URL of the route, with the $filters variable as part
                                     // of the URL
        'Mothership:Site::Controller:MyController#filterPages' // The controller to call
    )
    ->setMethod('GET') // Setting the method as GET allows URLs to contain the form information.
                       // This is recommended for the purposes of maintaining pagination etc.
;
```

From you page view, you can now include a javascript function call to `filterPages()`. This function takes five parameters,
although only the first two are required:

+ **ajaxUrl** - The URL to submit the form to
* **filterDestinationID** - The container to insert the AJAX-loaded HTML into
* **method** - The method to submit the form with, only supports `GET` and `POST`, defaults to `GET`
* **formID** - The ID of the filter form, defaults to `#page-filter-form`
* **paginationMenuID** - The ID of the pagination menu, if applicable. Defaults to `#pagination-menu`

```html
<script>
    <!--
        Call the url() twig function to generate the URL for the page filter controller.
        The first parameter is the route name as defined above, and the second parameter is
        an array of arguments to give the controller. In this case, that array only consists of
        the $filters variable, which is set to the service name of the FilterCollection
    -->
    $(document).ready(function () {
        filterPages(
            '{{ url('page-filtering', { filters: 'page_filters'}) }}',
            '#my-container'
        );
    });
</script>
```

This function will submit the form data to the controller, and render it within the container with an ID of `my-container`.
Since we are using the default `GET` method to submit the form, it will also amend the URL in the browser's address bar
to include the form data. It will also append the form data to any pagination links.
