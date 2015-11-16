# Changelog

## 4.10.1

- Deprecated `Exception\HistoricalSlugExistsException`, use `Page\Exception\SlugUpdateException` instead
- `Page\SlugGenerator::generate()` no longer throws `Exception\HistoricalSlugExistsException` if the slug exists in the slug history

## 4.10.0

- Support for re-ordering repeatable groups, if control panel 3.5.3 or higher is installed
- `Page\Content` now extends `Message\Cog\Field\Content`. Functionality is now entirely abstracted to `Message\Cog\Field\Content`
- Added third parameter of `Message\Cog\Field\ContentBuilder` to `Page\ContentLoader::__construct()`. Content populating functionality is now abstracted to `Message\Cog\Field\ContentBuilder`
- Page edit screen now uses `redirectToReferer()` when a form is submitted, rather than rendering a new form and populating it

## 4.9.0

- Added `Page\SlugEdit` class to process and validate changes to page slugs
- Added `Page\Exception\SlugUpdateException` class which extends `Message\Cog\Exception\TranslationLogicException` for relaying errors thrown when editing a slug back to the user
- Added `cms.page.slug_edit` service which returns instance of `Page\SlugEdit`
- Fixed `$segements` typo in `Page\Edit::updateSlug()` (renamed to `$segments`)
- Removed logic from `Controller\ControlPanel\Edit::_updateSlug()` and use `Page\SlugEdit` instead. `Page\Exception\SlugUpdateException` is caught and its translation is taken and given to the flash bag
- Capitalised 'URL' in slug update success message
- Updated `Cog` dependency to 4.13

## 4.8.1

- Resolve issue where an exception would be thrown when calling `Page\Loader::loadFromFilters()` if a query builder hadn't been built yet. Now calls `getAll()` after applying the filters, rather than the private `_loadPages()` method.

## 4.8.0

- Added `Page::isPublished()` to allow for checking if a page is published without having to access the `publishDateRange` property directly
- Do not display deleted pages as CMS options in link field
- Append unpublished pages in link field CMS options with `[unpublished]`
- By default `Page\ContentFilter` does not include content from deleted or unpublished pages when loading options for filter form
- Remove broken and out of date unit tests
- Implement Travis

## 4.7.0

- Added `Page\Event\ContentEvent` class for content-related event listeners
- `Page\ContentEdit::save()` method fires instance of `Page\Event\ContentEvent` at the end of save
- Removed erroneously added loop from `Page\Loader` that didn't do anything

## 4.6.1

- Resolve issue where `Attributes` tab would cause an error when attempting to load access groups

## 4.6.0

- Implemented page caching on `Page\Loader`
- Optimised `Link` field type page tree building
- Added `PageCollection` class for storing page cache
- Added `cms.page.cache` service which returns `PageCollection` class
- Ability to pass `filterPages()` JavaScript function a URL negating the need to create a new route for the returned HTML
- Increased `Cog` dependency to 4.8

## 4.5.3

- Resolve issue where slug redirects would break if redirecting to the home page (where a slug is empty)

## 4.5.2

- Resolve issue where `PageProxy::getTags()` method would only load tags for published and non-deleted pages

## 4.5.1

- Resolve issue where MySQL pagination would break if no pages were returned on page loader.

## 4.5.0

- Added `PageOrder::NONE` constant to disable ORDER BY statement when loading pages
- Added `getTagsFromChildren()` method to `TagLoader` load all tags that belong to any children of a page
- Added option to include tags for unpublished pages on `TagLoader` methods (disabled by default)
- Added option to include tags for deleted pages on `TagLoader` methods (disabled by default)

## 4.4.1

- Resolve issue where page titles could not be edited if there is a page access group set, due to the `Edit` class assuming that the access group was a string and not an object

## 4.4.0

- Extended `social.yml` config to have config settings for the following social networks:
    - Tumblr
    - Google+
    - Soundcloud
    - Bandcamp
    - YouTube
    - Vimeo
    - Github
- Added share links for the following social networks:
    - Google+
    - Tumblr
    - Reddit
- `Controller\Module\Social::links()` controller now takes an array of social networks as an optional parameter. If not set, it will take all configs from the `social.yml` config file
- `Controller\Module\Social::share()` controller now takes an array of social networks as an optional third parameter. If not set, it will default to Facebook and Twitter (original behaviour before v4.4.0)

## 4.3.6

- Loading pages by sibling no longer checks if the page depth is 0 strictly, as MySQL can set the depth as a string of '0', causing the check to return false.

## 4.3.5

- Fix for issue where calling `getAll()` on page loader would return a page instance if only one existed in database instead of an array

## 4.3.4

- Fix for issue where page loader would create invalid MySQL when calling `getByID()` with an empty value

## 4.3.3

- Fix `social.yml` file, had tabs on LinkedIn lines causing a parse error on new installation

## 4.3.2

- Validation on slugs. Slugs can only have alphanumeric characters or hyphens, and they can no longer conflict with reserved routes (e.g. '/admin')
- Amended filtering readme

## 4.3.1

- `RangeFilterForm` can take `min_placeholder` and `max_placeholder` options to set the placeholder text for the select fields

## 4.3.0

- Implemented new `Filter` component
- `Page\Filter` namespace
- `TagFilter` class for filtering pages by their tags
- `ContentFilter` class for filtering by content fields
- `ContentRangeFilter` class for filtering for pages with content that falls between two values
- `ContentFilterInterface` interface for classes that filter by content, implemented by `ContentFilter` and `ContentRangeFilter`
- `AbstractContentFilter` abstract class extended by `ContentFilter` and `ContentRangeFilter`
- `RangeFilterForm` class, a simple form that contains two matching drop downs for filtering by range
- `Module\PageFilter` controller for rendering filter form
- `filter-pages.js` file for handling AJAX requests to load content fields, and amending URLs to match form data (if method is set to `GET`)
- Complete refactor of `Page\Loader` class to use `QueryBuilder`
- `Page\Loader::__construct()` takes `QueryBuilderFactory` instead of `Query` (should be safe as accessed by service container, see <a href="http://wiki.mothership.ec/Backwards_compatibility">our documentation on backwards compatibility</a>)
- Added `Page\Loader::loadFromFilters()` method for loading from a filter collection
- Added `Page\Loader::applyFilters()` method for applying filters to the query
- Added `Page\Loader::setFilters()` method for setting the filters on a query
- Added `Page\Loader::clearFilters()` method for removing all filters from the loader
- Documentation `README.md` file for `Page\Filter` namespace
- Added `LinkedIn` to share links
- Added `linked-in` to `social.yml` config
- Added `image` translation for blog pages
- Updated `Cog` dependency to 4.4

## 4.2.1

- Fix typo in `pinterest` config file (was `pintrest`)

## 4.2.0

- Added `Pending Comments` dashboard widget
- Added `Recently Approved Comments` dashboard widget
- Added `DashboardLoader` class in `Blog\Dashboard` namespace for loading appropriate comments and pages for dashboard
- Widgets will only display when a page type that extends `AbstractBlog` is registered
- Added `getUpdatedAt()` method to `Comment` object
- Added `getByStatus()` method to `CommentLoader`
- Added user group loader constructor argument to `CommentBuilder` class
- Automatic approval for comments made by super admins
- Updated CP dependency to 3.3

## 4.1.1

- Can no longer change parent and sibling of a page at the same time
- Task `cms:page:fix_tree` to rebuild page structure if the data has become malformed (i.e. as a result of a user changing the parent and sibling at the same time)
- Renamed "Siblings" in admin panel to "Sibling"
- Added `PageEditException` to be thrown when a page cannot be edited due to the nearest sibling not being found
- `changeParent()` and `changeOrder()` on `Page\Edit` class no longer catch exceptions and return false. They will always return a boolean of the result of the database transaction (which should always be true)
- Added help text for "Sibling" field
- Moved error messages to translation file
- Added error message for malformed nested set data
- Updated `Cog` dependency to 4.2

## 4.1.0

- Share on social media (Twitter/Facebook only) controller
- Controller for linking to social media sites
- `social.yml` config file with support for usernames and slug overrides for Twitter, Facebook, Pinterest and Instagram
- Generic metadata view
- Can assign meta image to pages
- `meta_image` field on database
- Can set the order pages are loaded by in the page loader using the `orderBy()` method with constants from the `PageOrder` class
- Removed hard coded 4 character limit from page search, now uses whatever is in the `search.yml` config
- Deleted commented out code

## 4.0.1

- Search form displays in sidebar even if no pages exist
- Cookie trail pages sort by depth

## 4.0.0

- Initial open source release
